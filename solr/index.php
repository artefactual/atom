<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Solr AtoM demo</title>
  <link href="reset.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <section class="container">
    <?php
      include "bootstrap.php";

      $solrClientOptions = array
      (
          'hostname' => SOLR_SERVER_HOSTNAME,
          'login'    => SOLR_SERVER_USERNAME,
          'password' => SOLR_SERVER_PASSWORD,
          'port'     => SOLR_SERVER_PORT,
          'path'     => '/solr/' . SOLR_COLLECTION,
      );

      if ($solrClient = new SolrClient($solrClientOptions)) {
        echo "<p class='info info--success info--solr-status'>Solr connected!</p>";
      } else {
        echo "<p class='info info--error info--solr-status'>Solr not connected. Click init to initialize collection</p>";
      }
    ?>

    <form action="index.php" method="POST">
      <div class="form-container">
        <div class="form-row">
          <label>
            <span class='form-label'>Search</span>
            <input class='form-text' type="text" name="query" placeholder="Search query" />
            <input class='form-button' type="submit" name="search" value="Search"/>
          </label>
        </div>
        <div class="form-row">
          <input class='form-button' type="submit" name="index" value="Index" />
          <input class='form-button' type="submit" name="reindex" value="Re-index" />
          <input class='form-button' type="submit" name="delete" value="Delete" />
          <input class='form-button' type="submit" name="init" value="Initialize collection" />
          <input class='form-button' type="submit" name="addCopy" value="Add fields to copy" />
        </div>
      </div>
    </form>

    <?php
      if ($_SERVER['REQUEST_METHOD'] === "POST" and isset($_POST['index'])) {
        indexData($solrClient);
      }

      if ($_SERVER['REQUEST_METHOD'] === "POST" and isset($_POST['delete'])) {
        deleteData($solrClient);
      }

      if ($_SERVER['REQUEST_METHOD'] === "POST" and isset($_POST['reindex'])) {
        deleteData($solrClient);
        indexData($solrClient);
      }

      if ($_SERVER['REQUEST_METHOD'] === "POST" and isset($_POST['search']) and isset($_POST['query'])) {
        searchQuery($solrClient, $_POST['query']);
      }

      if ($_SERVER['REQUEST_METHOD'] === "POST" and isset($_POST['init'])) {
        initializeCollection();
      }

      if ($_SERVER['REQUEST_METHOD'] === "POST" and isset($_POST['addCopy'])) {
        $allFields = ['title', 'archivalHistory', 'scope', 'extent', 'acquisition'];
        foreach($allFields as $field) {
          addToCopyField($field);
        }
      }

      function initializeCollection() {
        $url = "http://" . SOLR_SERVER_HOSTNAME . ":" . SOLR_SERVER_PORT . "/solr/admin/collections?action=CREATE&name=" . SOLR_COLLECTION . "&numShards=2&replicationFactor=1&wt=json";
        echo "<p class='info info--log'>${url}</p>";
        $options = array(
          'http' => array(
            'method'  => 'GET',
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
            )
        );
        $context  = stream_context_create( $options );
        $result = file_get_contents($url, false, $context);
        $response = json_decode( $result );
        echo "<p class='info info--notice'>";
        print_r($response);
        echo "</p>";

        $url = 'http://' . SOLR_SERVER_HOSTNAME . ':'. SOLR_SERVER_PORT . '/solr/' . SOLR_COLLECTION . '/schema/';
        $addFieldQuery = '{"add-field": {"name": "all","stored": "false","type": "text_general","indexed": "true","multiValued": "true"}}';
        echo "<p class='info info--log'>${url}</p>";
        $options = array(
          'http' => array(
            'method'  => 'POST',
            'content' => $addFieldQuery,
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
            )
        );
        $context  = stream_context_create( $options );
        $result = file_get_contents($url, false, $context);
        $response = json_decode( $result );
        echo "<p class='info info--notice'>ADDING ALL FIELD";
        print_r($response);
        echo "</p>";

        $url = 'http://' . SOLR_SERVER_HOSTNAME . ':'. SOLR_SERVER_PORT . '/api/collections/' . SOLR_COLLECTION . '/config/';
        $updateDefaultHandler = '{"update-requesthandler": {"name": "/select", "class": "solr.SearchHandler", "defaults": {"df": "all", "rows": 10, "echoParams": "explicit"}}}';
        $options = array(
          'http' => array(
            'method'  => 'POST',
            'content' => $updateDefaultHandler,
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
            )
        );
        $context  = stream_context_create( $options );
        $result = file_get_contents($url, false, $context);
        $response = json_decode( $result );
        echo "<p class='info info--notice'>Updating default field";
        print_r($response);
        echo "</p>";
      }

      function addToCopyField($field) {
        $url = 'http://' . SOLR_SERVER_HOSTNAME . ':'. SOLR_SERVER_PORT . '/solr/' . SOLR_COLLECTION . '/schema/';
        $copySourceDest = array (
          'add-copy-field' => array (
            'source' => $field,
            'dest' => ["all"]
          )
        );
        echo "<p class='info info--log'>${url}</p>";
        echo "<p class='info info--log'>";
        echo json_encode($copySourceDest);
        echo "</p>";
        $options = array(
          'http' => array(
            'method'  => 'POST',
            'ignore_errors' => true,
            'content' => json_encode($copySourceDest),
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
            )
        );

        $context  = stream_context_create( $options );
        $result = file_get_contents($url, false, $context);
        $response = json_decode( $result );
        echo "<p class='info info--notice'>SETTING {$field} TO COPY";
        print_r($response);
        echo "</p>";
      }

      function indexData($client) {
        // Create connection
        $conn = mysqli_connect(SQL_SERVER_HOSTNAME, SQL_SERVER_USER, SQL_SERVER_PASSWORD, SQL_SERVER_DB, SQL_SERVER_PORT);

        // Check connection
        if ($conn->connect_error) {
          die("<p class='info info--error'>Connection failed: " . $conn->connect_error . "</p>");
        }
        echo "<p class='info info--warning'>MySQL Connected successfully!</p>";

        // SQL Query string
        $sql = "select title t, archival_history ah, id, scope_and_content sc, extent_and_medium ext, acquisition aq from information_object_i18n LIMIT 1000" ;
        if ($result = $conn->query($sql)) {
          while ($row = $result->fetch_assoc()) {
            echo "<p class='info info--log'>${row['t']}</p>";
            $doc = new SolrInputDocument();
            $doc->addField('id', $row['id']);
            $doc->addField('title', $row['t']);
            $doc->addField('scope', $row['sc']);
            $doc->addField('extent', $row['ext']);
            $doc->addField('acquisition', $row['aq']);
            $doc->addField('archivalHistory', $row['ah']);
            $updateResponse = $client->addDocument($doc);
            echo "<p class='info info--notice'>";
            print_r($updateResponse->getResponse());
            echo "</p>";
          }

          /*freeresultset*/
          $result->free();
        } else {
          echo "<p class='info info--error'>No SQL results found in information_object_i18n</p>";
        }
        $conn->close();
      }

      function deleteData($client) {
        //This will erase the entire index
        $deleteResponse = $client->deleteByQuery("*:*");
        $client->commit();
        echo "<p class='info info--notice'>";
        print_r($deleteResponse->getResponse());
        echo "</p>";
      }

      function searchQuery($client, $queryText) {
        $query = new SolrQuery();
        $query->setQuery($queryText);

        $query->setStart(0);
        $query->setRows(1000);

        $searchResponse = $client->query($query);

        $response = $searchResponse->getResponse()->response;
        if ($response->docs) {
          foreach($response->docs as $resp) {
            $title = $resp->title[0];
            $scope = $resp['scope'];
            $extent = $resp['extent'];
            $acquisition = $resp['acquisition'];
            $history = $resp['archivalHistory'];
            echo "<h2 class='title'>{$title}</h2>";
            if ($history) {
              echo "<p class='text'>{$history[0]}</p>";
            }
            if (!$scope && !$extent & !$acquisition) {
              continue;
            }
            echo "<section class='info-container'>";
            if ($scope) {
              echo "<h3 class='info-container__heading'>Scope</h3>";
              echo "<p class='info-container__detail'>{$scope[0]}</p>";
            }
            if ($extent) {
              echo "<h3 class='info-container__heading'>Extent</h3>";
              echo "<p class='info-container__detail'>{$extent[0]}</p>";
            }
            if ($acquisition) {
              echo "<h3 class='info-container__heading'>Acquisition</h3>";
              echo "<p class='info-container__detail'>{$acquisition[0]}</p>";
            }
            echo "</section>";
          }
        } else {
          echo "<p class='info info--warning'>No results found</p>";
        }
      }
    ?>
  </section>
</body>
</html>
