<?php

/**
 * Favorites List component.
 *
 * @package    qubit
 * @subpackage Cart List Module
 * @author     Johan Pieterse <johan@plainsailingisystems.co.za>
 * @version    SVN: $Id 
 */
 
class FavoritesBrowseAction extends sfAction
{
  public function execute($request)
  {
	$title = "Favorites"; //$this->context->i18n->__('Favorites');
	$this->response->setTitle("{$title} - {$this->response->getTitle()}");

	if (!isset($request->limit)) {
		$request->limit = sfConfig::get('app_hits_per_page');
	}

	if (sfConfig::get('app_enable_institutional_scoping')) {
		//remove search-realm
		$this->context->user->removeAttribute('search-realm');
	}

	$this->filter = $request->filter;

    if (!$this->getUser()->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }


    $criteria = new Criteria;
	$criteria->add(QubitFavorites::USER_ID, $this->context->user->getAttribute('user_id'));

	BaseFavorites::addSelectColumns($criteria); 

    // Page results
    $this->pager = new QubitPager('QubitFavorites');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
