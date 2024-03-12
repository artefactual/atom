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
    <form action="index.php" method="POST">
      <div class="form-row">
        <label>
          Search
          <input type="text" name="query" placeholder="Search query" />
          <input type="submit" name="search" value="Search"/>
        </label>
      </div>
      <div class="form-row">
        <input type="submit" name="index" value="Index" />
        <input type="submit" name="reindex" value="Re-index" />
        <input type="submit" name="delete" value="Delete" />
      </div>
    </form>

    <?php
      include "bootstrap.php";

      $solrClientOptions = array
      (
          'hostname' => SOLR_SERVER_HOSTNAME,
          'login'    => SOLR_SERVER_USERNAME,
          'password' => SOLR_SERVER_PASSWORD,
          'port'     => SOLR_SERVER_PORT,
          'path'     => SOLR_PATH,
      );

      if ($solrClient = new SolrClient($solrClientOptions)) {
        echo "<p class='warning'>Solr connected!</p>";
      } else {
        echo "<p class='error'>Solr not connected.</p>";
      }

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

      function indexData($client) {
        // Create connection
        $conn = mysqli_connect(SQL_SERVER_HOSTNAME, SQL_SERVER_USER, SQL_SERVER_PASSWORD, SQL_SERVER_DB, SQL_SERVER_PORT);

        // Check connection
        if ($conn->connect_error) {
          die("<p class='error'>Connection failed: " . $conn->connect_error . "</p>");
        }
        echo "<p class='warning'>MySQL Connected successfully!</p>";

        // SQL Query string
        $sql = "select title t, archival_history ah, id, scope_and_content sc, extent_and_medium ext, acquisition aq from information_object_i18n LIMIT 1000" ;
        if ($result = $conn->query($sql)) {
          while ($row = $result->fetch_assoc()) {
            echo "<strong>${row['t']}</strong>";
            echo "<p>${row['ah']}</p>";
            $doc = new SolrInputDocument();
            $doc->addField('id', $row['id']);
            $doc->addField('title', $row['t']);
            $doc->addField('scope', $row['sc']);
            $doc->addField('extent', $row['ext']);
            $doc->addField('aquisition', $row['aq']);
            $doc->addField('archivalHistory', $row['ah']);
            $updateResponse = $client->addDocument($doc);
            echo "<p class='info'>";
            print_r($updateResponse->getResponse());
            echo "</p>";
          }

          /*freeresultset*/
          $result->free();
        } else {
          echo "<p class='error'>No SQL results found in information_object_i18n</p>";
        }
        $conn->close();
      }

      function deleteData($client) {
        //This will erase the entire index
        $deleteResponse = $client->deleteByQuery("*:*");
        $client->commit();
        echo "<p class='info'>";
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
          echo "<pre>";
          print_r($searchResponse->getResponse()->response->docs);
          echo "</pre>";
        } else {
          echo "<p class='warning'>No results found</p>";
        }
      }
    ?>
  </section>
</body>
</html>
