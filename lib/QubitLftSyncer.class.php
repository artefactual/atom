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

            if ($esChecksum != $dbChecksum) {
                // Try to wait until Elasticsearch repair completes
                $waitCount = 0;
                $maxWaitAttempts = 3;

                do {
                    sleep(1);
                    ++$waitCount;
                    $esChecksum = $this->getChildLftChecksumForElasticsearch();
                } while ($waitCount < $maxWaitAttempts && $esChecksum != $dbChecksum);
            }
        }
    }

    private function getChildLftChecksumForDB()
    {
        $sql = 'SELECT lft
            FROM information_object
            WHERE parent_id=:parentId
            ORDER BY lft ASC
            LIMIT :limit';

        $params = [':parentId' => $this->parentId, ':limit' => $this->limit];
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
            ->getType('QubitInformationObject')
            ->search($query->getQuery(false, false))
        ;

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
        $sql = 'SELECT id, lft
            FROM information_object
            WHERE parent_id=:parentId
            LIMIT :limit';

        $params = [':parentId' => $this->parentId, ':limit' => $this->limit];
        $results = QubitPdo::fetchAll($sql, $params, ['fetchMode' => PDO::FETCH_ASSOC]);

        $bulk = new Elastica\Bulk(QubitSearch::getInstance()->client);
        $bulk->setIndex(QubitSearch::getInstance()->index);
        $bulk->setType('QubitInformationObject');

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
