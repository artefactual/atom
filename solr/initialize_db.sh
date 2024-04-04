#!/bin/bash

set -ue

# SET VARIABLES
SOLR_HOST=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' solr1)
SOLR_PORT=8983
COLLECTION_NAME="atom"

# INITIALIZE COLLECTION

# delete collection
curl -X POST -H 'Content-Type: application/json' \
"http://$SOLR_HOST:$SOLR_PORT/solr/admin/collections?action=DELETE&name=$COLLECTION_NAME"                                                                                                                                  

# create collection
curl -X POST -H 'Content-Type: application/json' \
"http://$SOLR_HOST:$SOLR_PORT/solr/admin/collections?action=CREATE&name=$COLLECTION_NAME&numShards=2&replicationFactor=1&wt=json"

# create schema
curl -X POST -H 'Content-Type: application/json' -H 'Accept: application/json' \
-d '{"add-field": {"name": "all","stored": "false","type": "text_general","indexed": "true","multiValued": "true"}}' \
"http://$SOLR_HOST:$SOLR_PORT/solr/$COLLECTION_NAME/schema/"

# add config to collection
curl -X POST -H 'Content-Type: application/json' -H 'Accept: application/json' \
-d '{"update-requesthandler": {"name": "/select", "class": "solr.SearchHandler", "defaults": {"df": "all", "rows": 10, "echoParams": "explicit"}}}' \
"http://$SOLR_HOST:$SOLR_PORT/api/collections/$COLLECTION_NAME/config/"

# INDEX DATABASE

# define SQL query
sql="select title t, archival_history ah, id, scope_and_content sc, extent_and_medium ext, acquisition aq from information_object_i18n LIMIT 1000"

# set IFS to '|' to split fields by '|'
IFS='|'

# execute SQL query and process results
while IFS= read -r line; do
    # Remove leading and trailing whitespaces from each field
    read -r x t ah id sc ext aq <<< "$(echo "$line" | awk '{$1=$1}1')"
    # Construct JSON document
    json="{\"id\":\"$id\",\"title\":\"$t\",\"scope\":\"$sc\",\"extent\":\"$ext\",\"acquisition\":\"$aq\",\"archivalHistory\":\"$ah\"}"
    echo -e "\n"
    # Send document to Solr
    curl -X POST -H 'Content-Type: application/json' \
    --data-binary "{\"add\": {\"doc\": $json}}" "http://$SOLR_HOST:$SOLR_PORT/solr/$COLLECTION_NAME/update?commit=true"
done < <(/usr/bin/docker-compose exec percona mysql -u atom -patom_12345 -h localhost atom -e "$sql")

unset IFS

# ADD FIELDS TO COPY

# fields to add to copy
fields=('title', 'archivalHistory', 'scope', 'extent', 'acquisition')

# add fields to copy
for element in "${fields[@]}"; do
    curl -X POST -H 'Content-Type: application/json' -H 'Accept: application/json' \
    --data-binary "{\"add-copy-field\": {\"source\": $element, \"dest\": [\"all\"]}}" \
    "http://$SOLR_HOST:$SOLR_PORT/solr/$COLLECTION_NAME/schema/"
done

# RE-INDEX

# delete index
curl -X POST -H 'Content-Type: application/json' -H 'Accept: application/json' \
--data-binary '{\"delete\": {\"query\": \"*:*\"}}' \
"http://$SOLR_HOST:$SOLR_PORT/solr/$COLLECTION_NAME/update/"

# re-index
# define SQL query
sql="select title t, archival_history ah, id, scope_and_content sc, extent_and_medium ext, acquisition aq from information_object_i18n LIMIT 1000"

# set IFS to '|' to split fields by '|'
IFS='|'

# execute SQL query and process results
while IFS= read -r line; do
    # Remove leading and trailing whitespaces from each field
    read -r x t ah id sc ext aq <<< "$(echo "$line" | awk '{$1=$1}1')"
    # Construct JSON document
    json="{\"id\":\"$id\",\"title\":\"$t\",\"scope\":\"$sc\",\"extent\":\"$ext\",\"acquisition\":\"$aq\",\"archivalHistory\":\"$ah\"}"
    echo -e "\n"
    # Send document to Solr
    curl -X POST -H 'Content-Type: application/json' \
    --data-binary "{\"add\": {\"doc\": $json}}" "http://$SOLR_HOST:$SOLR_PORT/solr/$COLLECTION_NAME/update?commit=true"
done < <(/usr/bin/docker-compose exec percona mysql -u atom -patom_12345 -h localhost atom -e "$sql")

unset IFS