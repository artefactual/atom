<?

class QubitCsvTransform extends QubitFlatfileImport {

  public
    $transformLogic,
    $levelsOfDescription = array(
      'fonds',
      'collection',
      'sousfonds',
      'sous-fonds',
      'series',
      'subseries',
      'file',
      'item'
    );


  public function __construct($options = array())
  {
    if (
      !isset($options['skipOptionsAndEnvironmentCheck'])
      || $options['skipOptionsAndEnvironmentCheck'] == false
    )
    {
      $this->checkTaskOptionsAndEnvironment($options['options']); 
    }

    // unset options not allowed in parent class
    unset($options['skipOptionsAndEnvironmentCheck']);
    if (isset($options['options']))
    {
      $cliOptions = $options['options'];
      unset($options['options']);
    }

    // call parent class constructor
    parent::__construct($options);

    if (isset($options['transformLogic']))
    {
      $this->transformLogic = $options['transformLogic'];
    }

    if (isset($cliOptions)) {
      $this->status['finalOutputFile'] = $cliOptions['output-file'];
      $this->status['ignoreBadLod'] = $cliOptions['ignore-bad-lod'];
    }

    $this->status['headersWritten']  = false;
  }

  protected function checkTaskOptionsAndEnvironment($options)
  {
    if (!$options['output-file'])
    {
      throw new sfException('You must specifiy the output-file option.');
    }

    if (!getenv("MYSQL_PASSWORD"))
    {
      //throw new sfException('You must set the MYSQL_PASSWORD environmental variable. This script will use the "root" user and a database called "import".');
    }
  }

  function writeHeadersOnFirstPass()
  {
    if (!$this->status['headersWritten'])
    {
      fputcsv($this->status['outFh'], $this->columnNames);
      $this->status['headersWritten'] = true;
    }
  }

  function initializeMySQLtemp()
  {
    $link = mysql_connect('localhost', 'root', getEnv("MYSQL_PASSWORD"));

    if (!$link) throw new sfException('MySQL connection failed. Make sure the MYSQL_PASSWORD environmental variable is set.');

    $db = mysql_select_db('import', $link);

    if (!$db) throw new sfException(
      'MySQL DB selection failed. Make sure a database called "import" exists.'
    );

    $sql = "CREATE TABLE IF NOT EXISTS import_descriptions (
      id INT NOT NULL AUTO_INCREMENT,
      sortorder INT,
      data LONGTEXT,
      PRIMARY KEY (id)
    )";

    $result = mysql_query($sql);

    if (!$result) throw new sfException('MySQL create table failed.');

    $result = mysql_query("DELETE FROM import_descriptions");
  }

  function addRowToMySQL($sortorder)
  {
    $sql = "INSERT INTO import_descriptions
        (sortorder, data)
        VALUES ('". mysql_real_escape_string($sortorder) ."',
        '". mysql_real_escape_string(serialize($this->status['row'])) ."')";

    $result = mysql_query($sql);

    if (!$result)
    {
      throw new sfException('Failed to create MySQL DB row.');
    }
  }

  function numberedFilePathVariation($filename, $number)
  {
    $parts     = pathinfo($filename);
    $base      = $parts['filename'];
    $path      = $parts['dirname'];
    return $path .'/'. $base .'_'. $number .'.'. $parts['extension'];
  }

  function writeMySQLRowsToCsvFilePath($filepath)
  {
    $chunk = 0;
    $startFile = $this->numberedFilePathVariation($filepath, $chunk);
    $fhOut = fopen($startFile, 'w');

    if (!$fhOut) throw new sfException('Error writing to '. $startFile .'.');

    print "Writing to ". $startFile ."...\n";

    fputcsv($fhOut, $this->columnNames); // write headers

    // cycle through DB, sorted by sort, and write CSV file
    $sql = "SELECT data FROM import_descriptions ORDER BY sortorder";

    $result = mysql_query($sql);

    $currentRow = 1;

    while($row = mysql_fetch_assoc($result))
    {
      // if starting a new chunk, write CSV headers
      if (($currentRow % 1000) == 0)
      {
        $chunk++;
        $chunkFilePath = $this->numberedFilePathVariation($filepath, $chunk);
        $fhOut = fopen($chunkFilePath, 'w');

        print "Writing to ". $chunkFilePath ."...\n";

        fputcsv($fhOut, $this->columnNames); // write headers
      }

      $data = unserialize($row['data']);

      // write to CSV out
      fputcsv($fhOut, $data);

      $currentRow++;
    }
  }

  function levelOfDescriptionToSortorder($level)
  {
    return array_search(strtolower($level), $this->levelsOfDescription);
  }
}
