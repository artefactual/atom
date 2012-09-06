<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The pager to separate results into more manageable segments.
 *
 * Page starts counting at 1.
 *
 * This pager has no dependency on any other part of sfSearch.
 *
 * @package sfSearch
 * @subpackage Result
 * @author Carl Vondrick
 */
final class xfPager
{
  /**
   * The result iterator
   *
   * @var SeekableIterator
   */
  private $results;

  /**
   * The total number of results.
   *
   * @var int
   */
  private $count = 0;

  /**
   * The current page
   *
   * @var int
   */
  private $page = 1;
  
  /**
   * The number of results per page
   *
   * @var int
   */
  private $per = 10; 

  /**
   * The URL format.
   *
   * @var string
   */
  private $urlFormat;

  /**
   * The constructor.
   *
   * @param xfResultIterator $results 
   */
  public function __construct(SeekableIterator $results)
  {
    $this->results = $results;
    $this->count = count($this->results);
  }

  /**
   * Sets the page
   *
   * @param int $page
   */
  public function setPage($page)
  {
    $this->page = (int) $page;

    $this->fixPageNumber();
  }

  /**
   * Gets the current page
   *
   * @returns int
   */
  public function getPage()
  {
    return $this->page;
  }

  /**
   * Sets per page
   *
   * @param int $per
   */
  public function setPerPage($per)
  {
    $this->per = $per;

    $this->fixPageNumber();
  }

  /**
   * Gets the per page count
   *
   * @returns int
   */
  public function getPerPage()
  {
    return $this->per;
  }

  /**
   * Recallibrates the pages and per page markings.
   */
  private function fixPageNumber()
  {
    if ($this->per < 0)
    {
      $this->per = 0;
    }

    $last = $this->getLastPage();

    if ($this->page > $last)
    {
      $this->page = $last;
    }
    elseif ($this->page < 1)
    {
      $this->page = 1;
    }
  }

  /**
   * Gets the results.
   *
   * @returns LimitIterator
   */
  public function getResults()
  {
    return new LimitIterator($this->results, ($this->page - 1) * $this->per, $this->per);
  }

  /**
   * Returns the number of results.
   *
   * @returns int
   */
  public function getNbResults()
  {
    return $this->count;
  }

  /**
   * Gets the first page.
   *
   * @returns int
   */
  public function getFirstPage()
  {
    return 1;
  }

  /**
   * Gets the last page.
   *
   * @returns int.
   */
  public function getLastPage()
  {
    if ($this->per == 0)
    {
      return 1;
    }

    return ceil($this->count / $this->per);
  }

  /**
   * Tests to see if we're at the last page already.
   *
   * @returns bool True if at last page, false otherwise
   */
  public function atLastPage()
  {
    return $this->page == $this->getLastPage();
  }

  /**
   * Tests to see if we're at the first page already.
   *
   * @returns bool True if at first page, false otherwise
   */
  public function atFirstPage()
  {
    return $this->page == 1;
  }

  /**
   * Tests to see if we should paginate.
   *
   * @returns bool True if we should paginate, false otherwise
   */
  public function haveToPaginate()
  {
    if ($this->atFirstPage() && $this->atLastPage())
    {
      return false;
    }

    return true;
  }

  /**
   * Gets the next page.
   *
   * @returns int
   */
  public function getNextPage()
  {
    if ($this->page == $this->getLastPage())
    {
      return $this->getLastPage();
    }
    else
    {
      return $this->page + 1;
    }
  }

  /**
   * Gets the previous page.
   *
   * @returns int
   */
  public function getPreviousPage()
  {
    if ($this->page == 1)
    {
      return 1;
    }
    else
    {
      return $this->page - 1;
    }
  }

  /**
   * Gets the start position.
   *
   * @returns int
   */
  public function getStartPosition()
  {
    return ($this->getPage() - 1) * $this->getPerPage() + 1;
  }

  /**
   * Gets the end position.
   *
   * @returns int
   */
  public function getEndPosition()
  {
    return min($this->getPage() * $this->getPerPage(), $this->count);
  }

  /**
   * Gets the links.
   *
   * @param int $number The number of page numbers to display
   * @returns array of page numbers
   */
  public function getLinks($number = 5)
  {
    $first = 1;
    $current = $this->page;
    $last = $this->getLastPage();
    $radius = floor($number / 2);

    $start = $current - $radius;
    $start = $start > 0 ? $start : 1;

    $end = $current + $radius;
    $end = $end > $last ? $last : $end;

    return range($start, $end);
  }

  /**
   * Sets the URL format for the pager.
   *
   * @param string $format The URL format.  %page% = page number
   */
  public function setUrlFormat($format)
  {
    $this->urlFormat = $format;
  }

  /**
   * Gets a URL with the page.
   *
   * @param int $page The page number
   * @returns string The URL for that page.
   */
  public function getPageUrl($page)
  {
    return str_replace('%page%', $page, $this->urlFormat);
  }
}
