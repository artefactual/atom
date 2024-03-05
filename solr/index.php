<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body>

  <form action="index.php" method="POST">
    <input type="submit" name="index" value="Index" />
    <input type="submit" name="reindex" value="Re-index" />
    <input type="submit" name="delete" value="Delete" />
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
      echo "<p style='color:goldenrod;'>Solr connected!</p>";
    } else {
      echo "<p style='color:red;'>Solr not connected.</p>";
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

    function indexData($client) {
      // Create connection
      $conn = mysqli_connect(SQL_SERVER_HOSTNAME, SQL_SERVER_USER, SQL_SERVER_PASSWORD, SQL_SERVER_DB, SQL_SERVER_PORT);

      // Check connection
      if ($conn->connect_error) {
        die("<p style='color:red;'>Connection failed: " . $conn->connect_error . "</p>");
      }
      echo "<p style='color:goldenrod;'>MySQL Connected successfully!</p>";

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
          echo "<p style='color:blue;'>";
          print_r($updateResponse->getResponse());
          echo "</p>";
        }

        /*freeresultset*/
        $result->free();
      } else {
        echo "<p style='color:red;'>No SQL results found in information_object_i18n</p>";
      }
      $conn->close();
    }

    function deleteData($client) {
      //This will erase the entire index
      $deleteResponse = $client->deleteByQuery("*:*");
      $client->commit();
      echo "<p style='color:blue;'>";
      print_r($deleteResponse->getResponse());
      echo "</p>";
    }
  ?>
</body>
</html>
