<?php

class QubitLftSyncer
{
    private $parentId;
    private $limit;

    public function __construct($parentId, $limit = 10000)
    {
        $this->parentId = $parentId;
        $this->limit = $limit;
    }

    public function sync()
    {
        // Get checksum representing lft values of DB and Elasticsearch
        $dbChecksum = $this->getChildLftChecksumForDB();
        $esChecksum = $this->getChildLftChecksumForElasticsearch();

        // If checksums don't match then repair
        if ($dbChecksum != $esChecksum) {
            $this->repairEsChildrenLftValues();

            // Check to see if repair worked
            $esChecksum = $this->getChildLftChecksumForElasticsearch();

            // Make a few checks on the syncing, allowing for Elasticsearch to be
            // asynchronously updated
            if ($esChecksum != $dbChecksum) {
                $waitCount = 0;
                $maxWaitAttempts = 3;

                do {
                    sleep(1);
                    ++$waitCount;
                    $esChecksum = $this->getChildLftChecksumForElasticsearch();
                } while ($waitCount < $maxWaitAttempts && $esChecksum != $dbChecksum);
            }

            return $esChecksum == $dbChecksum; // Return boolean reflecting repair success
        }
    }

    private function getChildLftChecksumForDB()
    {
        $sql = sprintf('SELECT lft
            FROM information_object
            WHERE parent_id=:parentId
            ORDER BY lft ASC
            LIMIT %d', $this->limit);

        $params = [':parentId' => $this->parentId];
        $lft = QubitPdo::fetchAll($sql, $params, ['fetchMode' => PDO::FETCH_COLUMN]);

        return md5(serialize($lft));
    }

    private function getChildLftChecksumForElasticsearch()
    {
        // Initialize Elasticsearch query
        $query = new arElasticSearchPluginQuery($this->limit);

        // Add criteria and set sort
        $term = new \Elastica\Query\Term(['parentId' => $this->parentId]);
        $query->queryBool->addMust($term);
        $query->query->addSort(['lft' => 'asc']);

        // Get results
        $result = QubitSearch::getInstance()
            ->index
            ->getIndex('QubitInformationObject')
            ->search($query->getQuery(false, false));

        // Amalgamate lft values in array
        $lft = [];
        foreach ($result->getResults() as $hit) {
            $doc = $hit->getDocument();
            $lft[] = $doc->lft;
        }

        return md5(serialize($lft));
    }

    private function repairEsChildrenLftValues()
    {
        $sql = sprintf('SELECT id, lft
            FROM information_object
            WHERE parent_id=:parentId
            LIMIT %d', $this->limit);

        $params = [':parentId' => $this->parentId];
        $results = QubitPdo::fetchAll($sql, $params, ['fetchMode' => PDO::FETCH_ASSOC]);

        $bulk = new Elastica\Bulk(QubitSearch::getInstance()->client);
        $bulk->setIndex(QubitSearch::getInstance()->index->getIndex('QubitInformationObject'));
        $bulk->setType(QubitSearch::getInstance()::ES_TYPE);

        foreach ($results as $row) {
            $bulk->addAction(
                new Elastica\Bulk\Action\UpdateDocument(
                    new Elastica\Document($row['id'], ['lft' => $row['lft']])
                )
            );
        }

        $bulk->send();
    }
}
