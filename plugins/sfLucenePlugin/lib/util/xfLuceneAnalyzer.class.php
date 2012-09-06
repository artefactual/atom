<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load Zend_Search_Lucene
xfLuceneZendManager::load();

/**
 * An convenience analyzer to provide a shortcut to setting up the analyzers.
 *
 * @package sfLucene
 * @subpackage Analyzer
 * @author Carl Vondrick
 */
final class xfLuceneAnalyzer extends Zend_Search_Lucene_Analysis_Analyzer
{
  /**
   * Flag for text
   */
  const TEXT = 0;

  /**
   * Flag for utf8
   */
  const UTF8 = 1;

  /**
   * Flag for numbers
   */
  const NUMBERS = 2;

  /**
   * Flag for case insensitive.
   */
  const CASE_INSENSITIVE = 4;

  /**
   * The configured analyzer
   *
   * @var Zend_Search_Lucene_Analysis_Analyzer_Common
   */
  private $analyzer;

  /**
   * The analyzer clss
   *
   * @var string
   */
  private $analyzerClass;
  
  /**
   * The registered filters
   *
   * @param array
   */
  private $filters = array();

  /**
   * Flag to indicate if the analyzer has been modified.
   *
   * True if modified, false otherwise
   *
   * @var bool
   */
  private $modified = false;
  
  /**
   * The initialized mode.
   *
   * @var int
   */
  private $mode = 0;

  /**
   * Constructor
   *
   * @param int $mode The mode
   */
  public function __construct($mode = 0)
  {
    $this->initialize($mode);

    $this->setCaseInsensitive();
  }

  /**
   * Initializes the analyzer.  This will also remove any old customizations.
   *
   * @param int $mode The mode
   */
  public function initialize($mode)
  {
    $class = 'Zend_Search_Lucene_Analysis_Analyzer_Common_';

    if ($mode & self::UTF8)
    {
      $class .= 'Utf8';
    }
    else
    {
      $class .= 'Text';
    }

    if ($mode & self::NUMBERS)
    {
      $class .= 'Num';
    }

    $this->analyzerClass = $class;
    $this->mode = $mode;
    $this->modified = true;
  }

  /**
   * Makes the analyzer case insensitive
   */
  public function setCaseInsensitive()
  {
    $this->setCaseSensitive();

    $filter = 'Zend_Search_Lucene_Analysis_TokenFilter_Lowercase';
    if ($this->mode & self::UTF8)
    {
      $filter .= 'Utf8';
    }

    $this->addFilter(new $filter);
  }

  /**
   * Makes the analyzer case sensitive
   */
  public function setCaseSensitive()
  {
    $this->removeFilter('Zend_Search_Lucene_Analysis_TokenFilter_LowerCase');
    $this->removeFilter('Zend_Search_Lucene_Analysis_TokenFilter_LowerCaseUtf8');
  }

  /**
   * Configures the stop words
   *
   * If any of these words appear, they will be thrown out.
   *
   * @param array $words
   */
  public function addStopWords(array $words)
  {
    $this->addFilter(new Zend_Search_Lucene_Analysis_TokenFilter_StopWords($words));
  }

  /**
   * Configures the stop words from file. 
   *
   * One word per line.  If it starts with a #, it's ignored.
   *
   * @param string $file
   */
  public function addStopWordsFromFile($file)
  {
    $filter = new Zend_Search_Lucene_Analysis_TokenFilter_StopWords;
    $filter->loadFromFile($file);

    $this->addFilter($filter);
  }

  /**
   * Sets the short word length
   *
   * If a word is less than the required length, it will be thrown out.
   *
   * @param int $lenght
   */
  public function setShortWordLength($length)
  {
    $this->removeFilter('Zend_Search_Lucene_Analysis_TokenFilter_ShortWords');

    $this->addFilter(new Zend_Search_Lucene_Analysis_TokenFilter_ShortWords($length));
  }

  /**
   * Sets a stemmer.
   *
   * @param xfLuceneStemmer $stemmer
   */
  public function setStemmer(xfLuceneStemmer $stemmer)
  {
    $this->addFilter(new xfLuceneStemmerTokenFilter($stemmer));
  }

  /**
   * Adds a filter to the queue
   *
   * @param Zend_Search_Lucene_Analysis_TokenFilter $filter
   */
  public function addFilter(Zend_Search_Lucene_Analysis_TokenFilter $filter)
  {
    $this->modified = true;
    $this->filters[] = $filter;
  }

  /**
   * Removes a filter by class instance
   *
   * @param string $name
   */
  public function removeFilter($name)
  {
    $filters = array();
    foreach ($this->filters as $filter)
    {
      if (!($filter instanceof $name))
      {
        $filters[] = $filter;
      }
    }
    $this->filters = $filters;
    $this->modified = true;
  }

  /**
   * Binds the analyzer
   */
  public function bind()
  {
    $class = $this->analyzerClass;
    $this->analyzer = new $class;

    foreach ($this->filters as $filter)
    {
      $this->analyzer->addFilter($filter);
    }

    $this->modified = false;
  }

  /**
   * Gets the configured analyzer.
   *
   * @returns Zend_Search_Lucene_Analysis_Analyzer
   */
  public function getAnalyzer()
  {
    if ($this->analyzer == null || $this->modified)
    {
      $this->bind();
    }
    
    return $this->analyzer;
  }

  /**
   * @see Zend_Search_Lucene_Analysis_Analyzer
   */
  public function reset()
  {
    $this->getAnalyzer()->reset();
  }

  /**
   * @see Zend_Search_Lucene_Analysis_Analyzer
   */
  public function setInput($data, $encoding = '')
  {
    $this->reset();
    $this->getAnalyzer()->setInput($data, $encoding);
  }

  /**
   * @see Zend_Search_Lucene_Analysis_Analyzer
   */
  public function nextToken()
  {
    return $this->getAnalyzer()->nextToken();
  }
}
