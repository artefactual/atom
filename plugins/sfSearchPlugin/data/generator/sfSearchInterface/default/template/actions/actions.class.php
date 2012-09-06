[?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * <?php echo $this->getGeneratedModuleName() ?> actions.
 *
 * @package ##PROJECT_NAME##
 * @subpackage <?php echo $this->getGeneratedModuleName() ?>

 * @author Carl Vondrick
 */
abstract class <?php echo $this->getGeneratedModuleName() ?>Actions extends sfActions
{
  public function executeIndex()
  {
    $this->forward('<?php echo $this->getModuleName() ?>', 'search');
  }

  public function executeSearch()
  {
    $this->form = new <?php echo $this->get('simple.form.class', 'xfSimpleForm') ?>;
    $this->form->getWidgetSchema()->setNameFormat('search[%s]');

    $data = $this->getRequestParameter('search');

    if ($data)
    {
      $this->form->bind($data);

      if ($this->form->isValid())
      {
        $this->parser   = new xfParserSilent(new xfParserLucene);
        $this->criteria = $this->parser->parse($this->form->getQuery());
        $this->results  = $this->doSearch($this->criteria);

        if (count($this->results))
        {
          $this->pager = new xfPager($this->results);
          $this->pager->setPerPage(<?php echo $this->get('simple.results.per_page', 10) ?>);
          $this->pager->setPage($this->form->getPageNumber());
          $this->pager->setUrlFormat($this->form->getUrlFormat());

          $replacements = array(
            '%total%'         => $this->pager->getNbResults(),
            '%page%'          => $this->pager->getPage(),
            '%total_pages%'   => $this->pager->getLastPage(),
            '%start_pos%'     => $this->pager->getStartPosition(),
            '%end_pos%'       => $this->pager->getEndPosition(),
          );

          $this->setTitle(str_replace(array_keys($replacements), array_values($replacements), '<?php echo $this->get('simple.results.title', 'Results %start_pos% to %end_pos%') ?>'));

          return 'Results';
        }
        else
        {
          $this->setTitle('<?php echo $this->get('simple.no_results.title', 'No Search Results') ?>');

          return 'NoResults';
        }
      }
    }

    $this->setTitle('<?php echo $this->get('simple.controls.title', 'Search') ?>');
    
    return 'Controls';
  }

  protected function doSearch(xfCriterion $c)
  {
    return xfIndexManager::get('<?php echo $this->get('index_class') ?>')->find($c);
  }

  protected function setTitle($title)
  {
    $this->getResponse()->setTitle($title);
  }
}
