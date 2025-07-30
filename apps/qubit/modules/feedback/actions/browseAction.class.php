<?php

/**
 * Feedback List component.
 *
 * @package    qubit
 * @subpackage Feedback List Module
 * @author     Johan Pieterse <johan@plainsailingisystems.co.za>
 * @version    SVN: $Id 
 */
 
class FeedbackBrowseAction extends sfAction
{
  public function execute($request)
  {
	$title = $this->context->i18n->__('Feedback');
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

	if (!isset($this->filter)) {
		$this->filter = 'all';
	}
	
    $criteria = new Criteria;

	if ('pending' === $this->filter) {
		$criteria->add(QubitFeedbackI18n::STATUS_ID, QubitTerm::PENDING_ID);
	} elseif ('completed' === $this->filter) {
		$criteria->add(QubitFeedbackI18n::STATUS_ID, QubitTerm::COMPLETED_ID);
	}

    // Do source culture fallback
    $criteria->addJoin(QubitFeedback::ID, QubitFeedbackI18n::ID);
	BaseFeedback::addSelectColumns($criteria);

    switch ($request->sort)
    {
      case 'nameDown':
        $criteria->addDescendingOrderByColumn('name');

        break;

      case 'remarksDown':
        $criteria->addDescendingOrderByColumn('remarks');

        break;

      case 'remarksUp':
        $criteria->addAscendingOrderByColumn('remarks');

        break;

      default:
        $request->sort = 'nameUp';
        $criteria->addAscendingOrderByColumn('name');
    }

    // Page results
    $this->pager = new QubitPager('QubitFeedback');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
