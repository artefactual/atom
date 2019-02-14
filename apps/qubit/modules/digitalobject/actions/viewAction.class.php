<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Digital Object view action
 *
 * @package    AccesstoMemory
 * @subpackage digital object
 * @author     Andy Koch <koch.andy@gmail.com>
 */
class DigitalObjectViewAction extends sfAction
{
  public function execute($request)
  {
    $pathinfo = pathinfo($request->getPathInfo());
    $pathinfo['dirname'] = str_replace("/{$request->module}/{$request->action}", '', $pathinfo['dirname']).'/';

    $this->resource = QubitDigitalObject::getByPathFile($pathinfo['dirname'], $pathinfo['basename']);

    // We are going to need this later
    $this->digitalObjectId = $this->resource->id;

    // Resource Found?
    if (null === $this->resource)
    {
      $this->forward404();
    }

    list($obj, $action) = $this->getObjAndAction();

    // Do appropriate ACL check(s). Master copy of text objects are always allowed for reading
    // QubitActor does not have a ACL check for readmaster.
    if ((!QubitAcl::check($obj, $action) || !QubitGrantedRight::checkPremis($obj->id, $action))
      && !($action == 'readMaster' && $this->resource->mediaTypeId == QubitTerm::TEXT_ID)
      && $obj instanceOf QubitInformationObject)
    {
      $this->forward404();
    }

    if ($this->needsPopup($action))
    {
      $this->resource = $this->resource->object;

      $this->accessToken = bin2hex(random_bytes(32)); # URL friendly
      $this->context->user->setAttribute("token-$this->digitalObjectId", $this->accessToken, 'symfony/user/sfUser/copyrightStatementTmpAccess');

      $this->response->addMeta('robots', 'noindex,nofollow');
      $this->setTemplate('viewCopyrightStatement');

      $this->copyrightStatement = sfConfig::get('app_digitalobject_copyright_statement');

      return sfView::SUCCESS;
    }

    $this->setResponseHeaders();

    return sfView::HEADER_ONLY;
  }

  protected function needsPopup($action)
  {
    // Only if the user is reading the master digital object
    if ($action !== 'readMaster')
    {
      return false;
    }

    // Only if the copyright statement is enabled
    if ('1' !== sfConfig::get('app_digitalobject_copyright_statement_enabled', false))
    {
      return false;
    }

    // Check if there is any right statement associated with the object where
    // the basis = copyright and the restriction = conditional (regardless of
    // the Rights Act). We don't need to show the popup otherwise.
    $sql = 'SELECT EXISTS(
      SELECT 1
        FROM '.QubitObject::TABLE_NAME.' o
        JOIN '.QubitRelation::TABLE_NAME.' rel ON (rel.subject_id = o.id)
        JOIN '.QubitGrantedRight::TABLE_NAME.' gr ON (rel.object_id = gr.rights_id)
        JOIN '.QubitRights::TABLE_NAME.' r ON (gr.rights_id = r.id)
      WHERE
        o.id = ? AND
        rel.type_id = ? AND
        gr.restriction = ? AND
        r.basis_id = ?
      LIMIT 1) AS has';
    $r = QubitPdo::fetchOne($sql, array(
      $this->resource->object->id,
      QubitTerm::RIGHT_ID,
      QubitGrantedRight::CONDITIONAL_RIGHT,
      QubitTerm::RIGHT_BASIS_COPYRIGHT_ID));

    if (false === $r || !isset($r->has))
    {
      throw new sfException('Unexpected error');
    }
    if ('1' !== $r->has)
    {
      return false;
    }

    return false === $this->isAccessTokenValid();
  }

  protected function setResponseHeaders()
  {
    $this->response->setContentType($this->resource->mimeType);

    // Using X-Accel-Redirect (Nginx) unless ATOM_XSENDFILE is set
    if (false === filter_var($_SERVER['ATOM_XSENDFILE'], FILTER_VALIDATE_BOOLEAN))
    {
      $urlPath = preg_replace('\/?[^\/]+\.php$', '', $_SERVER['SCRIPT_NAME']);
      $this->response->setHttpHeader('X-Accel-Redirect', $urlPath . '/private' . $this->resource->getFullPath());
    }
    else
    {
      $this->response->setHttpHeader('X-Sendfile', sprintf('%s/%s',
        sfConfig::get('sf_root_dir'),
        $this->resource->getFullPath()));
    }
  }

  private function getObjAndAction()
  {
    switch ($this->resource->usageId)
    {
      case QubitTerm::MASTER_ID:
        $action = 'readMaster';
        $obj = $this->resource->object;
        break;

      case QubitTerm::REFERENCE_ID:
        $action = 'readReference';
        $obj = $this->resource->parent->object;
        break;

      case QubitTerm::THUMBNAIL_ID:
        $action = 'readThumbnail';
        $obj = $this->resource->parent->object;
        break;

      default:
        throw new sfException("Invalid usageId given in digitalobject/view: {$this->resource->usageId}");
    }

    return array($obj, $action);
  }

  private function isAccessTokenValid()
  {
    $providedToken = $this->request->token;
    $internalToken = $this->context->user->getAttribute("token-$this->digitalObjectId", null, 'symfony/user/sfUser/copyrightStatementTmpAccess');

    if (empty($providedToken) || empty($internalToken))
    {
      return false;
    }

    if ($providedToken !== $internalToken)
    {
      return false;
    }

    return true;
  }
}
