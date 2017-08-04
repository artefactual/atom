<?php

abstract class BaseTerm extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'term',

    ID = 'term.ID',
    TAXONOMY_ID = 'term.TAXONOMY_ID',
    CODE = 'term.CODE',
    PARENT_ID = 'term.PARENT_ID',
    LFT = 'term.LFT',
    RGT = 'term.RGT',
    SOURCE_CULTURE = 'term.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitTerm::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitTerm::ID);
    $criteria->addSelectColumn(QubitTerm::TAXONOMY_ID);
    $criteria->addSelectColumn(QubitTerm::CODE);
    $criteria->addSelectColumn(QubitTerm::PARENT_ID);
    $criteria->addSelectColumn(QubitTerm::LFT);
    $criteria->addSelectColumn(QubitTerm::RGT);
    $criteria->addSelectColumn(QubitTerm::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitTerm::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitTerm', $options);
  }

  public static function getAll(array $options = array())
  {
    return self::get(new Criteria, $options);
  }

  public static function getOne(Criteria $criteria, array $options = array())
  {
    $criteria->setLimit(1);

    return self::get($criteria, $options)->__get(0, array('defaultValue' => null));
  }

  public static function getById($id, array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitTerm::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public static function addOrderByPreorder(Criteria $criteria, $order = Criteria::ASC)
  {
    if ($order == Criteria::DESC)
    {
      return $criteria->addDescendingOrderByColumn(QubitTerm::LFT);
    }

    return $criteria->addAscendingOrderByColumn(QubitTerm::LFT);
  }

  public static function addRootsCriteria(Criteria $criteria)
  {
    $criteria->add(QubitTerm::PARENT_ID);

    return $criteria;
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitTerm::DATABASE_NAME)->getTable(QubitTerm::TABLE_NAME);
  }

  public function __isset($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    try
    {
      return call_user_func_array(array($this, 'QubitObject::__isset'), $args);
    }
    catch (sfException $e)
    {
    }

    if ('accessionsRelatedByacquisitionTypeId' == $name)
    {
      return true;
    }

    if ('accessionsRelatedByprocessingPriorityId' == $name)
    {
      return true;
    }

    if ('accessionsRelatedByprocessingStatusId' == $name)
    {
      return true;
    }

    if ('accessionsRelatedByresourceTypeId' == $name)
    {
      return true;
    }

    if ('deaccessions' == $name)
    {
      return true;
    }

    if ('actorsRelatedByentityTypeId' == $name)
    {
      return true;
    }

    if ('actorsRelatedBydescriptionStatusId' == $name)
    {
      return true;
    }

    if ('actorsRelatedBydescriptionDetailId' == $name)
    {
      return true;
    }

    if ('aips' == $name)
    {
      return true;
    }

    if ('jobs' == $name)
    {
      return true;
    }

    if ('digitalObjectsRelatedByusageId' == $name)
    {
      return true;
    }

    if ('digitalObjectsRelatedBymediaTypeId' == $name)
    {
      return true;
    }

    if ('events' == $name)
    {
      return true;
    }

    if ('functionsRelatedBytypeId' == $name)
    {
      return true;
    }

    if ('functionsRelatedBydescriptionStatusId' == $name)
    {
      return true;
    }

    if ('functionsRelatedBydescriptionDetailId' == $name)
    {
      return true;
    }

    if ('informationObjectsRelatedBylevelOfDescriptionId' == $name)
    {
      return true;
    }

    if ('informationObjectsRelatedBycollectionTypeId' == $name)
    {
      return true;
    }

    if ('informationObjectsRelatedBydescriptionStatusId' == $name)
    {
      return true;
    }

    if ('informationObjectsRelatedBydescriptionDetailId' == $name)
    {
      return true;
    }

    if ('informationObjectsRelatedBydisplayStandardId' == $name)
    {
      return true;
    }

    if ('notes' == $name)
    {
      return true;
    }

    if ('objectTermRelations' == $name)
    {
      return true;
    }

    if ('otherNames' == $name)
    {
      return true;
    }

    if ('physicalObjects' == $name)
    {
      return true;
    }

    if ('relations' == $name)
    {
      return true;
    }

    if ('repositorysRelatedBydescStatusId' == $name)
    {
      return true;
    }

    if ('repositorysRelatedBydescDetailId' == $name)
    {
      return true;
    }

    if ('rightssRelatedBybasisId' == $name)
    {
      return true;
    }

    if ('rightssRelatedBycopyrightStatusId' == $name)
    {
      return true;
    }

    if ('rightssRelatedBystatuteCitationId' == $name)
    {
      return true;
    }

    if ('grantedRights' == $name)
    {
      return true;
    }

    if ('statussRelatedBytypeId' == $name)
    {
      return true;
    }

    if ('statussRelatedBystatusId' == $name)
    {
      return true;
    }

    if ('termsRelatedByparentId' == $name)
    {
      return true;
    }

    if ('termI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrenttermI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrenttermI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
    }

    if ('ancestors' == $name)
    {
      return true;
    }

    if ('descendants' == $name)
    {
      return true;
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    try
    {
      return call_user_func_array(array($this, 'QubitObject::__get'), $args);
    }
    catch (sfException $e)
    {
    }

    if ('accessionsRelatedByacquisitionTypeId' == $name)
    {
      if (!isset($this->refFkValues['accessionsRelatedByacquisitionTypeId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['accessionsRelatedByacquisitionTypeId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['accessionsRelatedByacquisitionTypeId'] = self::getaccessionsRelatedByacquisitionTypeIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['accessionsRelatedByacquisitionTypeId'];
    }

    if ('accessionsRelatedByprocessingPriorityId' == $name)
    {
      if (!isset($this->refFkValues['accessionsRelatedByprocessingPriorityId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['accessionsRelatedByprocessingPriorityId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['accessionsRelatedByprocessingPriorityId'] = self::getaccessionsRelatedByprocessingPriorityIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['accessionsRelatedByprocessingPriorityId'];
    }

    if ('accessionsRelatedByprocessingStatusId' == $name)
    {
      if (!isset($this->refFkValues['accessionsRelatedByprocessingStatusId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['accessionsRelatedByprocessingStatusId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['accessionsRelatedByprocessingStatusId'] = self::getaccessionsRelatedByprocessingStatusIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['accessionsRelatedByprocessingStatusId'];
    }

    if ('accessionsRelatedByresourceTypeId' == $name)
    {
      if (!isset($this->refFkValues['accessionsRelatedByresourceTypeId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['accessionsRelatedByresourceTypeId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['accessionsRelatedByresourceTypeId'] = self::getaccessionsRelatedByresourceTypeIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['accessionsRelatedByresourceTypeId'];
    }

    if ('deaccessions' == $name)
    {
      if (!isset($this->refFkValues['deaccessions']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['deaccessions'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['deaccessions'] = self::getdeaccessionsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['deaccessions'];
    }

    if ('actorsRelatedByentityTypeId' == $name)
    {
      if (!isset($this->refFkValues['actorsRelatedByentityTypeId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['actorsRelatedByentityTypeId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['actorsRelatedByentityTypeId'] = self::getactorsRelatedByentityTypeIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['actorsRelatedByentityTypeId'];
    }

    if ('actorsRelatedBydescriptionStatusId' == $name)
    {
      if (!isset($this->refFkValues['actorsRelatedBydescriptionStatusId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['actorsRelatedBydescriptionStatusId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['actorsRelatedBydescriptionStatusId'] = self::getactorsRelatedBydescriptionStatusIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['actorsRelatedBydescriptionStatusId'];
    }

    if ('actorsRelatedBydescriptionDetailId' == $name)
    {
      if (!isset($this->refFkValues['actorsRelatedBydescriptionDetailId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['actorsRelatedBydescriptionDetailId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['actorsRelatedBydescriptionDetailId'] = self::getactorsRelatedBydescriptionDetailIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['actorsRelatedBydescriptionDetailId'];
    }

    if ('aips' == $name)
    {
      if (!isset($this->refFkValues['aips']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['aips'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['aips'] = self::getaipsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['aips'];
    }

    if ('jobs' == $name)
    {
      if (!isset($this->refFkValues['jobs']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['jobs'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['jobs'] = self::getjobsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['jobs'];
    }

    if ('digitalObjectsRelatedByusageId' == $name)
    {
      if (!isset($this->refFkValues['digitalObjectsRelatedByusageId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['digitalObjectsRelatedByusageId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['digitalObjectsRelatedByusageId'] = self::getdigitalObjectsRelatedByusageIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['digitalObjectsRelatedByusageId'];
    }

    if ('digitalObjectsRelatedBymediaTypeId' == $name)
    {
      if (!isset($this->refFkValues['digitalObjectsRelatedBymediaTypeId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['digitalObjectsRelatedBymediaTypeId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['digitalObjectsRelatedBymediaTypeId'] = self::getdigitalObjectsRelatedBymediaTypeIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['digitalObjectsRelatedBymediaTypeId'];
    }

    if ('events' == $name)
    {
      if (!isset($this->refFkValues['events']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['events'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['events'] = self::geteventsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['events'];
    }

    if ('functionsRelatedBytypeId' == $name)
    {
      if (!isset($this->refFkValues['functionsRelatedBytypeId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['functionsRelatedBytypeId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['functionsRelatedBytypeId'] = self::getfunctionsRelatedBytypeIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['functionsRelatedBytypeId'];
    }

    if ('functionsRelatedBydescriptionStatusId' == $name)
    {
      if (!isset($this->refFkValues['functionsRelatedBydescriptionStatusId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['functionsRelatedBydescriptionStatusId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['functionsRelatedBydescriptionStatusId'] = self::getfunctionsRelatedBydescriptionStatusIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['functionsRelatedBydescriptionStatusId'];
    }

    if ('functionsRelatedBydescriptionDetailId' == $name)
    {
      if (!isset($this->refFkValues['functionsRelatedBydescriptionDetailId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['functionsRelatedBydescriptionDetailId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['functionsRelatedBydescriptionDetailId'] = self::getfunctionsRelatedBydescriptionDetailIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['functionsRelatedBydescriptionDetailId'];
    }

    if ('informationObjectsRelatedBylevelOfDescriptionId' == $name)
    {
      if (!isset($this->refFkValues['informationObjectsRelatedBylevelOfDescriptionId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['informationObjectsRelatedBylevelOfDescriptionId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['informationObjectsRelatedBylevelOfDescriptionId'] = self::getinformationObjectsRelatedBylevelOfDescriptionIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['informationObjectsRelatedBylevelOfDescriptionId'];
    }

    if ('informationObjectsRelatedBycollectionTypeId' == $name)
    {
      if (!isset($this->refFkValues['informationObjectsRelatedBycollectionTypeId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['informationObjectsRelatedBycollectionTypeId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['informationObjectsRelatedBycollectionTypeId'] = self::getinformationObjectsRelatedBycollectionTypeIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['informationObjectsRelatedBycollectionTypeId'];
    }

    if ('informationObjectsRelatedBydescriptionStatusId' == $name)
    {
      if (!isset($this->refFkValues['informationObjectsRelatedBydescriptionStatusId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['informationObjectsRelatedBydescriptionStatusId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['informationObjectsRelatedBydescriptionStatusId'] = self::getinformationObjectsRelatedBydescriptionStatusIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['informationObjectsRelatedBydescriptionStatusId'];
    }

    if ('informationObjectsRelatedBydescriptionDetailId' == $name)
    {
      if (!isset($this->refFkValues['informationObjectsRelatedBydescriptionDetailId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['informationObjectsRelatedBydescriptionDetailId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['informationObjectsRelatedBydescriptionDetailId'] = self::getinformationObjectsRelatedBydescriptionDetailIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['informationObjectsRelatedBydescriptionDetailId'];
    }

    if ('informationObjectsRelatedBydisplayStandardId' == $name)
    {
      if (!isset($this->refFkValues['informationObjectsRelatedBydisplayStandardId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['informationObjectsRelatedBydisplayStandardId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['informationObjectsRelatedBydisplayStandardId'] = self::getinformationObjectsRelatedBydisplayStandardIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['informationObjectsRelatedBydisplayStandardId'];
    }

    if ('notes' == $name)
    {
      if (!isset($this->refFkValues['notes']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['notes'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['notes'] = self::getnotesById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['notes'];
    }

    if ('objectTermRelations' == $name)
    {
      if (!isset($this->refFkValues['objectTermRelations']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['objectTermRelations'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['objectTermRelations'] = self::getobjectTermRelationsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['objectTermRelations'];
    }

    if ('otherNames' == $name)
    {
      if (!isset($this->refFkValues['otherNames']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['otherNames'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['otherNames'] = self::getotherNamesById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['otherNames'];
    }

    if ('physicalObjects' == $name)
    {
      if (!isset($this->refFkValues['physicalObjects']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['physicalObjects'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['physicalObjects'] = self::getphysicalObjectsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['physicalObjects'];
    }

    if ('relations' == $name)
    {
      if (!isset($this->refFkValues['relations']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['relations'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['relations'] = self::getrelationsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['relations'];
    }

    if ('repositorysRelatedBydescStatusId' == $name)
    {
      if (!isset($this->refFkValues['repositorysRelatedBydescStatusId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['repositorysRelatedBydescStatusId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['repositorysRelatedBydescStatusId'] = self::getrepositorysRelatedBydescStatusIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['repositorysRelatedBydescStatusId'];
    }

    if ('repositorysRelatedBydescDetailId' == $name)
    {
      if (!isset($this->refFkValues['repositorysRelatedBydescDetailId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['repositorysRelatedBydescDetailId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['repositorysRelatedBydescDetailId'] = self::getrepositorysRelatedBydescDetailIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['repositorysRelatedBydescDetailId'];
    }

    if ('rightssRelatedBybasisId' == $name)
    {
      if (!isset($this->refFkValues['rightssRelatedBybasisId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['rightssRelatedBybasisId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['rightssRelatedBybasisId'] = self::getrightssRelatedBybasisIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['rightssRelatedBybasisId'];
    }

    if ('rightssRelatedBycopyrightStatusId' == $name)
    {
      if (!isset($this->refFkValues['rightssRelatedBycopyrightStatusId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['rightssRelatedBycopyrightStatusId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['rightssRelatedBycopyrightStatusId'] = self::getrightssRelatedBycopyrightStatusIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['rightssRelatedBycopyrightStatusId'];
    }

    if ('rightssRelatedBystatuteCitationId' == $name)
    {
      if (!isset($this->refFkValues['rightssRelatedBystatuteCitationId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['rightssRelatedBystatuteCitationId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['rightssRelatedBystatuteCitationId'] = self::getrightssRelatedBystatuteCitationIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['rightssRelatedBystatuteCitationId'];
    }

    if ('grantedRights' == $name)
    {
      if (!isset($this->refFkValues['grantedRights']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['grantedRights'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['grantedRights'] = self::getgrantedRightsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['grantedRights'];
    }

    if ('statussRelatedBytypeId' == $name)
    {
      if (!isset($this->refFkValues['statussRelatedBytypeId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['statussRelatedBytypeId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['statussRelatedBytypeId'] = self::getstatussRelatedBytypeIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['statussRelatedBytypeId'];
    }

    if ('statussRelatedBystatusId' == $name)
    {
      if (!isset($this->refFkValues['statussRelatedBystatusId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['statussRelatedBystatusId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['statussRelatedBystatusId'] = self::getstatussRelatedBystatusIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['statussRelatedBystatusId'];
    }

    if ('termsRelatedByparentId' == $name)
    {
      if (!isset($this->refFkValues['termsRelatedByparentId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['termsRelatedByparentId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['termsRelatedByparentId'] = self::gettermsRelatedByparentIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['termsRelatedByparentId'];
    }

    if ('termI18ns' == $name)
    {
      if (!isset($this->refFkValues['termI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['termI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['termI18ns'] = self::gettermI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['termI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrenttermI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrenttermI18n(array('sourceCulture' => true) + $options), '__get'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
    }

    if ('ancestors' == $name)
    {
      if (!isset($this->values['ancestors']))
      {
        if ($this->new)
        {
          $this->values['ancestors'] = QubitQuery::create(array('self' => $this) + $options);
        }
        else
        {
          $criteria = new Criteria;
          $this->addAncestorsCriteria($criteria);
          $this->addOrderByPreorder($criteria);
          $this->values['ancestors'] = self::get($criteria, array('self' => $this) + $options);
        }
      }

      return $this->values['ancestors'];
    }

    if ('descendants' == $name)
    {
      if (!isset($this->values['descendants']))
      {
        if ($this->new)
        {
          $this->values['descendants'] = QubitQuery::create(array('self' => $this) + $options);
        }
        else
        {
          $criteria = new Criteria;
          $this->addDescendantsCriteria($criteria);
          $this->addOrderByPreorder($criteria);
          $this->values['descendants'] = self::get($criteria, array('self' => $this) + $options);
        }
      }

      return $this->values['descendants'];
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public function __set($name, $value)
  {
    $args = func_get_args();

    $options = array();
    if (2 < count($args))
    {
      $options = $args[2];
    }

    call_user_func_array(array($this, 'QubitObject::__set'), $args);

    call_user_func_array(array($this->getCurrenttermI18n($options), '__set'), $args);

    return $this;
  }

  public function __unset($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    call_user_func_array(array($this, 'QubitObject::__unset'), $args);

    call_user_func_array(array($this->getCurrenttermI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->termI18ns as $termI18n)
    {
      $termI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->termI18ns as $termI18n)
    {
      $termI18n->id = $this->id;

      $termI18n->save($connection);
    }

    return $this;
  }

  protected function param($column)
  {
    $value = $this->values[$column->getPhpName()];

    // Convert to DateTime or SQL zero special case
    if (isset($value) && $column->isTemporal() && !$value instanceof DateTime)
    {
      // Year only: one or more digits.  Convert to SQL zero special case
      if (preg_match('/^\d+$/', $value))
      {
        $value .= '-0-0';
      }

      // Year and month only: one or more digits, plus separator, plus
      // one or more digits.  Convert to SQL zero special case
      else if (preg_match('/^\d+[-\/]\d+$/', $value))
      {
        $value .= '-0';
      }

      // Convert to DateTime if not SQL zero special case: year plus
      // separator plus zero to twelve (possibly zero padded) plus
      // separator plus one or more zeros
      if (!preg_match('/^\d+[-\/]0*(?:1[0-2]|\d)[-\/]0+$/', $value))
      {
        try
        {
          $value = new DateTime($value);
        }
        catch (Exception $e)
        {
          return null;
        }
      }
    }

    return $value;
  }

  protected function insert($connection = null)
  {
    $this->updateNestedSet($connection);

    parent::insert($connection);

    return $this;
  }

  protected function update($connection = null)
  {
    // Update nested set keys only if parent id has changed
    if (isset($this->values['parentId']))
    {
      // Get the "original" parentId before any updates
      $offset = 0;
      $originalParentId = null;
      foreach ($this->tables as $table)
      {
        foreach ($table->getColumns() as $column)
        {
          if ('parentId' == $column->getPhpName())
          {
            $originalParentId = $this->row[$offset];
            break;
          }
          $offset++;
        }
      }

      // If updated value of parentId is different then original value,
      // update the nested set
      if ($originalParentId != $this->values['parentId'])
      {
        $this->updateNestedSet($connection);
      }
    }

    parent::update($connection);

    return $this;
  }

  public function delete($connection = null)
  {
    if ($this->deleted)
    {
      throw new PropelException('This object has already been deleted.');
    }

    $this->clear();
    if (!property_exists($this, 'disableNestedSetUpdating') || $this->disableNestedSetUpdating !== true)
    {
      $this->deleteFromNestedSet($connection);
    }

    parent::delete($connection);

    return $this;
  }

  public static function addJointaxonomyCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitTerm::TAXONOMY_ID, QubitTaxonomy::ID);

    return $criteria;
  }

  public static function addJoinparentCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitTerm::PARENT_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addaccessionsRelatedByacquisitionTypeIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAccession::ACQUISITION_TYPE_ID, $id);

    return $criteria;
  }

  public static function getaccessionsRelatedByacquisitionTypeIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaccessionsRelatedByacquisitionTypeIdCriteriaById($criteria, $id);

    return QubitAccession::get($criteria, $options);
  }

  public function addaccessionsRelatedByacquisitionTypeIdCriteria(Criteria $criteria)
  {
    return self::addaccessionsRelatedByacquisitionTypeIdCriteriaById($criteria, $this->id);
  }

  public static function addaccessionsRelatedByprocessingPriorityIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAccession::PROCESSING_PRIORITY_ID, $id);

    return $criteria;
  }

  public static function getaccessionsRelatedByprocessingPriorityIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaccessionsRelatedByprocessingPriorityIdCriteriaById($criteria, $id);

    return QubitAccession::get($criteria, $options);
  }

  public function addaccessionsRelatedByprocessingPriorityIdCriteria(Criteria $criteria)
  {
    return self::addaccessionsRelatedByprocessingPriorityIdCriteriaById($criteria, $this->id);
  }

  public static function addaccessionsRelatedByprocessingStatusIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAccession::PROCESSING_STATUS_ID, $id);

    return $criteria;
  }

  public static function getaccessionsRelatedByprocessingStatusIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaccessionsRelatedByprocessingStatusIdCriteriaById($criteria, $id);

    return QubitAccession::get($criteria, $options);
  }

  public function addaccessionsRelatedByprocessingStatusIdCriteria(Criteria $criteria)
  {
    return self::addaccessionsRelatedByprocessingStatusIdCriteriaById($criteria, $this->id);
  }

  public static function addaccessionsRelatedByresourceTypeIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAccession::RESOURCE_TYPE_ID, $id);

    return $criteria;
  }

  public static function getaccessionsRelatedByresourceTypeIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaccessionsRelatedByresourceTypeIdCriteriaById($criteria, $id);

    return QubitAccession::get($criteria, $options);
  }

  public function addaccessionsRelatedByresourceTypeIdCriteria(Criteria $criteria)
  {
    return self::addaccessionsRelatedByresourceTypeIdCriteriaById($criteria, $this->id);
  }

  public static function adddeaccessionsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitDeaccession::SCOPE_ID, $id);

    return $criteria;
  }

  public static function getdeaccessionsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::adddeaccessionsCriteriaById($criteria, $id);

    return QubitDeaccession::get($criteria, $options);
  }

  public function adddeaccessionsCriteria(Criteria $criteria)
  {
    return self::adddeaccessionsCriteriaById($criteria, $this->id);
  }

  public static function addactorsRelatedByentityTypeIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitActor::ENTITY_TYPE_ID, $id);

    return $criteria;
  }

  public static function getactorsRelatedByentityTypeIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addactorsRelatedByentityTypeIdCriteriaById($criteria, $id);

    return QubitActor::get($criteria, $options);
  }

  public function addactorsRelatedByentityTypeIdCriteria(Criteria $criteria)
  {
    return self::addactorsRelatedByentityTypeIdCriteriaById($criteria, $this->id);
  }

  public static function addactorsRelatedBydescriptionStatusIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitActor::DESCRIPTION_STATUS_ID, $id);

    return $criteria;
  }

  public static function getactorsRelatedBydescriptionStatusIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addactorsRelatedBydescriptionStatusIdCriteriaById($criteria, $id);

    return QubitActor::get($criteria, $options);
  }

  public function addactorsRelatedBydescriptionStatusIdCriteria(Criteria $criteria)
  {
    return self::addactorsRelatedBydescriptionStatusIdCriteriaById($criteria, $this->id);
  }

  public static function addactorsRelatedBydescriptionDetailIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitActor::DESCRIPTION_DETAIL_ID, $id);

    return $criteria;
  }

  public static function getactorsRelatedBydescriptionDetailIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addactorsRelatedBydescriptionDetailIdCriteriaById($criteria, $id);

    return QubitActor::get($criteria, $options);
  }

  public function addactorsRelatedBydescriptionDetailIdCriteria(Criteria $criteria)
  {
    return self::addactorsRelatedBydescriptionDetailIdCriteriaById($criteria, $this->id);
  }

  public static function addaipsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAip::TYPE_ID, $id);

    return $criteria;
  }

  public static function getaipsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaipsCriteriaById($criteria, $id);

    return QubitAip::get($criteria, $options);
  }

  public function addaipsCriteria(Criteria $criteria)
  {
    return self::addaipsCriteriaById($criteria, $this->id);
  }

  public static function addjobsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitJob::STATUS_ID, $id);

    return $criteria;
  }

  public static function getjobsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addjobsCriteriaById($criteria, $id);

    return QubitJob::get($criteria, $options);
  }

  public function addjobsCriteria(Criteria $criteria)
  {
    return self::addjobsCriteriaById($criteria, $this->id);
  }

  public static function adddigitalObjectsRelatedByusageIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitDigitalObject::USAGE_ID, $id);

    return $criteria;
  }

  public static function getdigitalObjectsRelatedByusageIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::adddigitalObjectsRelatedByusageIdCriteriaById($criteria, $id);

    return QubitDigitalObject::get($criteria, $options);
  }

  public function adddigitalObjectsRelatedByusageIdCriteria(Criteria $criteria)
  {
    return self::adddigitalObjectsRelatedByusageIdCriteriaById($criteria, $this->id);
  }

  public static function adddigitalObjectsRelatedBymediaTypeIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitDigitalObject::MEDIA_TYPE_ID, $id);

    return $criteria;
  }

  public static function getdigitalObjectsRelatedBymediaTypeIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::adddigitalObjectsRelatedBymediaTypeIdCriteriaById($criteria, $id);

    return QubitDigitalObject::get($criteria, $options);
  }

  public function adddigitalObjectsRelatedBymediaTypeIdCriteria(Criteria $criteria)
  {
    return self::adddigitalObjectsRelatedBymediaTypeIdCriteriaById($criteria, $this->id);
  }

  public static function addeventsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitEvent::TYPE_ID, $id);

    return $criteria;
  }

  public static function geteventsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addeventsCriteriaById($criteria, $id);

    return QubitEvent::get($criteria, $options);
  }

  public function addeventsCriteria(Criteria $criteria)
  {
    return self::addeventsCriteriaById($criteria, $this->id);
  }

  public static function addfunctionsRelatedBytypeIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitFunction::TYPE_ID, $id);

    return $criteria;
  }

  public static function getfunctionsRelatedBytypeIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addfunctionsRelatedBytypeIdCriteriaById($criteria, $id);

    return QubitFunction::get($criteria, $options);
  }

  public function addfunctionsRelatedBytypeIdCriteria(Criteria $criteria)
  {
    return self::addfunctionsRelatedBytypeIdCriteriaById($criteria, $this->id);
  }

  public static function addfunctionsRelatedBydescriptionStatusIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitFunction::DESCRIPTION_STATUS_ID, $id);

    return $criteria;
  }

  public static function getfunctionsRelatedBydescriptionStatusIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addfunctionsRelatedBydescriptionStatusIdCriteriaById($criteria, $id);

    return QubitFunction::get($criteria, $options);
  }

  public function addfunctionsRelatedBydescriptionStatusIdCriteria(Criteria $criteria)
  {
    return self::addfunctionsRelatedBydescriptionStatusIdCriteriaById($criteria, $this->id);
  }

  public static function addfunctionsRelatedBydescriptionDetailIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitFunction::DESCRIPTION_DETAIL_ID, $id);

    return $criteria;
  }

  public static function getfunctionsRelatedBydescriptionDetailIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addfunctionsRelatedBydescriptionDetailIdCriteriaById($criteria, $id);

    return QubitFunction::get($criteria, $options);
  }

  public function addfunctionsRelatedBydescriptionDetailIdCriteria(Criteria $criteria)
  {
    return self::addfunctionsRelatedBydescriptionDetailIdCriteriaById($criteria, $this->id);
  }

  public static function addinformationObjectsRelatedBylevelOfDescriptionIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, $id);

    return $criteria;
  }

  public static function getinformationObjectsRelatedBylevelOfDescriptionIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addinformationObjectsRelatedBylevelOfDescriptionIdCriteriaById($criteria, $id);

    return QubitInformationObject::get($criteria, $options);
  }

  public function addinformationObjectsRelatedBylevelOfDescriptionIdCriteria(Criteria $criteria)
  {
    return self::addinformationObjectsRelatedBylevelOfDescriptionIdCriteriaById($criteria, $this->id);
  }

  public static function addinformationObjectsRelatedBycollectionTypeIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitInformationObject::COLLECTION_TYPE_ID, $id);

    return $criteria;
  }

  public static function getinformationObjectsRelatedBycollectionTypeIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addinformationObjectsRelatedBycollectionTypeIdCriteriaById($criteria, $id);

    return QubitInformationObject::get($criteria, $options);
  }

  public function addinformationObjectsRelatedBycollectionTypeIdCriteria(Criteria $criteria)
  {
    return self::addinformationObjectsRelatedBycollectionTypeIdCriteriaById($criteria, $this->id);
  }

  public static function addinformationObjectsRelatedBydescriptionStatusIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitInformationObject::DESCRIPTION_STATUS_ID, $id);

    return $criteria;
  }

  public static function getinformationObjectsRelatedBydescriptionStatusIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addinformationObjectsRelatedBydescriptionStatusIdCriteriaById($criteria, $id);

    return QubitInformationObject::get($criteria, $options);
  }

  public function addinformationObjectsRelatedBydescriptionStatusIdCriteria(Criteria $criteria)
  {
    return self::addinformationObjectsRelatedBydescriptionStatusIdCriteriaById($criteria, $this->id);
  }

  public static function addinformationObjectsRelatedBydescriptionDetailIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitInformationObject::DESCRIPTION_DETAIL_ID, $id);

    return $criteria;
  }

  public static function getinformationObjectsRelatedBydescriptionDetailIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addinformationObjectsRelatedBydescriptionDetailIdCriteriaById($criteria, $id);

    return QubitInformationObject::get($criteria, $options);
  }

  public function addinformationObjectsRelatedBydescriptionDetailIdCriteria(Criteria $criteria)
  {
    return self::addinformationObjectsRelatedBydescriptionDetailIdCriteriaById($criteria, $this->id);
  }

  public static function addinformationObjectsRelatedBydisplayStandardIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitInformationObject::DISPLAY_STANDARD_ID, $id);

    return $criteria;
  }

  public static function getinformationObjectsRelatedBydisplayStandardIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addinformationObjectsRelatedBydisplayStandardIdCriteriaById($criteria, $id);

    return QubitInformationObject::get($criteria, $options);
  }

  public function addinformationObjectsRelatedBydisplayStandardIdCriteria(Criteria $criteria)
  {
    return self::addinformationObjectsRelatedBydisplayStandardIdCriteriaById($criteria, $this->id);
  }

  public static function addnotesCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitNote::TYPE_ID, $id);

    return $criteria;
  }

  public static function getnotesById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addnotesCriteriaById($criteria, $id);

    return QubitNote::get($criteria, $options);
  }

  public function addnotesCriteria(Criteria $criteria)
  {
    return self::addnotesCriteriaById($criteria, $this->id);
  }

  public static function addobjectTermRelationsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitObjectTermRelation::TERM_ID, $id);

    return $criteria;
  }

  public static function getobjectTermRelationsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addobjectTermRelationsCriteriaById($criteria, $id);

    return QubitObjectTermRelation::get($criteria, $options);
  }

  public function addobjectTermRelationsCriteria(Criteria $criteria)
  {
    return self::addobjectTermRelationsCriteriaById($criteria, $this->id);
  }

  public static function addotherNamesCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitOtherName::TYPE_ID, $id);

    return $criteria;
  }

  public static function getotherNamesById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addotherNamesCriteriaById($criteria, $id);

    return QubitOtherName::get($criteria, $options);
  }

  public function addotherNamesCriteria(Criteria $criteria)
  {
    return self::addotherNamesCriteriaById($criteria, $this->id);
  }

  public static function addphysicalObjectsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitPhysicalObject::TYPE_ID, $id);

    return $criteria;
  }

  public static function getphysicalObjectsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addphysicalObjectsCriteriaById($criteria, $id);

    return QubitPhysicalObject::get($criteria, $options);
  }

  public function addphysicalObjectsCriteria(Criteria $criteria)
  {
    return self::addphysicalObjectsCriteriaById($criteria, $this->id);
  }

  public static function addrelationsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRelation::TYPE_ID, $id);

    return $criteria;
  }

  public static function getrelationsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrelationsCriteriaById($criteria, $id);

    return QubitRelation::get($criteria, $options);
  }

  public function addrelationsCriteria(Criteria $criteria)
  {
    return self::addrelationsCriteriaById($criteria, $this->id);
  }

  public static function addrepositorysRelatedBydescStatusIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRepository::DESC_STATUS_ID, $id);

    return $criteria;
  }

  public static function getrepositorysRelatedBydescStatusIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrepositorysRelatedBydescStatusIdCriteriaById($criteria, $id);

    return QubitRepository::get($criteria, $options);
  }

  public function addrepositorysRelatedBydescStatusIdCriteria(Criteria $criteria)
  {
    return self::addrepositorysRelatedBydescStatusIdCriteriaById($criteria, $this->id);
  }

  public static function addrepositorysRelatedBydescDetailIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRepository::DESC_DETAIL_ID, $id);

    return $criteria;
  }

  public static function getrepositorysRelatedBydescDetailIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrepositorysRelatedBydescDetailIdCriteriaById($criteria, $id);

    return QubitRepository::get($criteria, $options);
  }

  public function addrepositorysRelatedBydescDetailIdCriteria(Criteria $criteria)
  {
    return self::addrepositorysRelatedBydescDetailIdCriteriaById($criteria, $this->id);
  }

  public static function addrightssRelatedBybasisIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRights::BASIS_ID, $id);

    return $criteria;
  }

  public static function getrightssRelatedBybasisIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrightssRelatedBybasisIdCriteriaById($criteria, $id);

    return QubitRights::get($criteria, $options);
  }

  public function addrightssRelatedBybasisIdCriteria(Criteria $criteria)
  {
    return self::addrightssRelatedBybasisIdCriteriaById($criteria, $this->id);
  }

  public static function addrightssRelatedBycopyrightStatusIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRights::COPYRIGHT_STATUS_ID, $id);

    return $criteria;
  }

  public static function getrightssRelatedBycopyrightStatusIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrightssRelatedBycopyrightStatusIdCriteriaById($criteria, $id);

    return QubitRights::get($criteria, $options);
  }

  public function addrightssRelatedBycopyrightStatusIdCriteria(Criteria $criteria)
  {
    return self::addrightssRelatedBycopyrightStatusIdCriteriaById($criteria, $this->id);
  }

  public static function addrightssRelatedBystatuteCitationIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRights::STATUTE_CITATION_ID, $id);

    return $criteria;
  }

  public static function getrightssRelatedBystatuteCitationIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrightssRelatedBystatuteCitationIdCriteriaById($criteria, $id);

    return QubitRights::get($criteria, $options);
  }

  public function addrightssRelatedBystatuteCitationIdCriteria(Criteria $criteria)
  {
    return self::addrightssRelatedBystatuteCitationIdCriteriaById($criteria, $this->id);
  }

  public static function addgrantedRightsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitGrantedRight::ACT_ID, $id);

    return $criteria;
  }

  public static function getgrantedRightsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addgrantedRightsCriteriaById($criteria, $id);

    return QubitGrantedRight::get($criteria, $options);
  }

  public function addgrantedRightsCriteria(Criteria $criteria)
  {
    return self::addgrantedRightsCriteriaById($criteria, $this->id);
  }

  public static function addstatussRelatedBytypeIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitStatus::TYPE_ID, $id);

    return $criteria;
  }

  public static function getstatussRelatedBytypeIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addstatussRelatedBytypeIdCriteriaById($criteria, $id);

    return QubitStatus::get($criteria, $options);
  }

  public function addstatussRelatedBytypeIdCriteria(Criteria $criteria)
  {
    return self::addstatussRelatedBytypeIdCriteriaById($criteria, $this->id);
  }

  public static function addstatussRelatedBystatusIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitStatus::STATUS_ID, $id);

    return $criteria;
  }

  public static function getstatussRelatedBystatusIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addstatussRelatedBystatusIdCriteriaById($criteria, $id);

    return QubitStatus::get($criteria, $options);
  }

  public function addstatussRelatedBystatusIdCriteria(Criteria $criteria)
  {
    return self::addstatussRelatedBystatusIdCriteriaById($criteria, $this->id);
  }

  public static function addtermsRelatedByparentIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitTerm::PARENT_ID, $id);

    return $criteria;
  }

  public static function gettermsRelatedByparentIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addtermsRelatedByparentIdCriteriaById($criteria, $id);

    return QubitTerm::get($criteria, $options);
  }

  public function addtermsRelatedByparentIdCriteria(Criteria $criteria)
  {
    return self::addtermsRelatedByparentIdCriteriaById($criteria, $this->id);
  }

  public static function addtermI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitTermI18n::ID, $id);

    return $criteria;
  }

  public static function gettermI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addtermI18nsCriteriaById($criteria, $id);

    return QubitTermI18n::get($criteria, $options);
  }

  public function addtermI18nsCriteria(Criteria $criteria)
  {
    return self::addtermI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrenttermI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $termI18ns = $this->termI18ns->indexBy('culture');
    if (!isset($termI18ns[$options['culture']]))
    {
      $termI18ns[$options['culture']] = new QubitTermI18n;
    }

    return $termI18ns[$options['culture']];
  }

  public function hasChildren()
  {
    return ($this->rgt - $this->lft) > 1;
  }

  public function addAncestorsCriteria(Criteria $criteria)
  {
    return $criteria->add(QubitTerm::LFT, $this->lft, Criteria::LESS_THAN)->add(QubitTerm::RGT, $this->rgt, Criteria::GREATER_THAN);
  }

  public function addDescendantsCriteria(Criteria $criteria)
  {
    return $criteria->add(QubitTerm::LFT, $this->lft, Criteria::GREATER_THAN)->add(QubitTerm::RGT, $this->rgt, Criteria::LESS_THAN);
  }

  protected function updateNestedSet($connection = null)
  {
// HACK Try to prevent modifying left and right values anywhere except in this
// method.  Perhaps it would be more logical to use protected visibility for
// these values?
unset($this->values['lft']);
unset($this->values['rgt']);
    if (!isset($connection))
    {
      $connection = Propel::getConnection();
    }

    if (!isset($this->lft) || !isset($this->rgt))
    {
      $delta = 2;
    }
    else
    {
      $delta = $this->rgt - $this->lft + 1;
    }

    if (null === $parent = $this->__get('parent', array('connection' => $connection)))
    {
      $statement = $connection->prepare('
        SELECT MAX('.QubitTerm::RGT.')
        FROM '.QubitTerm::TABLE_NAME);
      $statement->execute();
      $row = $statement->fetch();
      $max = $row[0];

      if (!isset($this->lft) || !isset($this->rgt))
      {
        $this->lft = $max + 1;
        $this->rgt = $max + 2;

        return $this;
      }

      $shift = $max + 1 - $this->lft;
    }
    else
    {
      $parent->clear();

      if (isset($this->lft) && isset($this->rgt) && $this->lft <= $parent->lft && $this->rgt >= $parent->rgt)
      {
        throw new PropelException('An object cannot be a descendant of itself.');
      }

      $statement = $connection->prepare('
        UPDATE '.QubitTerm::TABLE_NAME.'
        SET '.QubitTerm::LFT.' = '.QubitTerm::LFT.' + ?
        WHERE '.QubitTerm::LFT.' >= ?');
      $statement->execute(array($delta, $parent->rgt));

      $statement = $connection->prepare('
        UPDATE '.QubitTerm::TABLE_NAME.'
        SET '.QubitTerm::RGT.' = '.QubitTerm::RGT.' + ?
        WHERE '.QubitTerm::RGT.' >= ?');
      $statement->execute(array($delta, $parent->rgt));

      if (!isset($this->lft) || !isset($this->rgt))
      {
        $this->lft = $parent->rgt;
        $this->rgt = $parent->rgt + 1;
        $parent->rgt += 2;

        return $this;
      }

      if ($this->lft > $parent->rgt)
      {
        $this->lft += $delta;
        $this->rgt += $delta;
      }

      $shift = $parent->rgt - $this->lft;
    }

    $statement = $connection->prepare('
      UPDATE '.QubitTerm::TABLE_NAME.'
      SET '.QubitTerm::LFT.' = '.QubitTerm::LFT.' + ?, '.QubitTerm::RGT.' = '.QubitTerm::RGT.' + ?
      WHERE '.QubitTerm::LFT.' >= ?
      AND '.QubitTerm::RGT.' <= ?');
    $statement->execute(array($shift, $shift, $this->lft, $this->rgt));

    $this->deleteFromNestedSet($connection);

    if ($shift > 0)
    {
      $this->lft -= $delta;
      $this->rgt -= $delta;
    }

    $this->lft += $shift;
    $this->rgt += $shift;

    return $this;
  }

  protected function deleteFromNestedSet($connection = null)
  {
    if (!isset($connection))
    {
      $connection = Propel::getConnection();
    }

    $delta = $this->rgt - $this->lft + 1;

    $statement = $connection->prepare('
      UPDATE '.QubitTerm::TABLE_NAME.'
      SET '.QubitTerm::LFT.' = '.QubitTerm::LFT.' - ?
      WHERE '.QubitTerm::LFT.' >= ?');
    $statement->execute(array($delta, $this->rgt));

    $statement = $connection->prepare('
      UPDATE '.QubitTerm::TABLE_NAME.'
      SET '.QubitTerm::RGT.' = '.QubitTerm::RGT.' - ?
      WHERE '.QubitTerm::RGT.' >= ?');
    $statement->execute(array($delta, $this->rgt));

    return $this;
  }

  public function isInTree()
  {
    return $this->lft > 0 && $this->rgt > $this->lft;
  }

  public function isRoot()
  {
      return $this->isInTree() && $this->lft == 1;
  }

  public function isDescendantOf($parent)
  {
    return $this->isInTree() && $this->lft > $parent->lft && $this->rgt < $parent->rgt;
  }

  public function moveToFirstChildOf($parent, PropelPDO $con = null)
  {
    if ($parent->isDescendantOf($this))
    {
      throw new PropelException('Cannot move a node as child of one of its subtree nodes.');
    }

    $this->moveSubtreeTo($parent->lft + 1, $con);

    return $this;
  }

  public function moveToLastChildOf($parent, PropelPDO $con = null)
  {
    if ($parent->isDescendantOf($this))
    {
      throw new PropelException('Cannot move a node as child of one of its subtree nodes.');
    }

    $this->moveSubtreeTo($parent->rgt, $con);

    return $this;
  }

  public function moveToPrevSiblingOf($sibling, PropelPDO $con = null)
  {
    if (!$this->isInTree())
    {
      throw new PropelException('This object must be already in the tree to be moved. Use the insertAsPrevSiblingOf() instead.');
    }

    if ($sibling->isRoot())
    {
      throw new PropelException('Cannot move to previous sibling of a root node.');
    }

    if ($sibling->isDescendantOf($this))
    {
      throw new PropelException('Cannot move a node as sibling of one of its subtree nodes.');
    }

    $this->moveSubtreeTo($sibling->lft, $con);

    return $this;
  }

  public function moveToNextSiblingOf($sibling, PropelPDO $con = null)
  {
    if (!$this->isInTree())
    {
      throw new PropelException('This object must be already in the tree to be moved. Use the insertAsPrevSiblingOf() instead.');
    }

    if ($sibling->isRoot())
    {
      throw new PropelException('Cannot move to previous sibling of a root node.');
    }

    if ($sibling->isDescendantOf($this))
    {
      throw new PropelException('Cannot move a node as sibling of one of its subtree nodes.');
    }

    $this->moveSubtreeTo($sibling->rgt + 1, $con);

    return $this;
  }

  protected function moveSubtreeTo($destLeft, PropelPDO $con = null)
  {
    $left  = $this->lft;
    $right = $this->rgt;

    $treeSize = $right - $left +1;

    if ($con === null)
    {
      $con = Propel::getConnection();
    }

    $con->beginTransaction();

    try
    {
      // make room next to the target for the subtree
      self::shiftRLValues($treeSize, $destLeft, null, $con);

      if ($left >= $destLeft) // src was shifted too?
      {
        $left += $treeSize;
        $right += $treeSize;
      }

      // move the subtree to the target
      self::shiftRLValues($destLeft - $left, $left, $right, $con);

      // remove the empty room at the previous location of the subtree
      self::shiftRLValues(-$treeSize, $right + 1, null, $con);

      // update all loaded nodes
      // self::updateLoadedNodes(null, $con);

      $con->commit();
    }
    catch (PropelException $e)
    {
      $con->rollback();

      throw $e;
    }
  }

  /**
   * Adds $delta to all L and R values that are >= $first and <= $last.
   * '$delta' can also be negative.
   *
   * @param int $delta Value to be shifted by, can be negative
   * @param int $first First node to be shifted
   * @param int $last Last node to be shifted (optional)
   * @param PropelPDO $con Connection to use.
   */
  protected function shiftRLValues($delta, $first, $last = null, PropelPDO $con = null)
  {
    if ($con === null)
    {
      $con = Propel::getConnection();
    }

    // Shift left column values
    $whereCriteria = new Criteria;
    $criterion = $whereCriteria->getNewCriterion(QubitTerm::LFT, $first, Criteria::GREATER_EQUAL);
    if (null !== $last)
    {
      $criterion->addAnd($whereCriteria->getNewCriterion(QubitTerm::LFT, $last, Criteria::LESS_EQUAL));
    }
    $whereCriteria->add($criterion);

    $valuesCriteria = new Criteria;
    $valuesCriteria->add(QubitTerm::LFT, array('raw' => QubitTerm::LFT . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

    BasePeer::doUpdate($whereCriteria, $valuesCriteria, $con);

    // Shift right column values
    $whereCriteria = new Criteria;
    $criterion = $whereCriteria->getNewCriterion(QubitTerm::RGT, $first, Criteria::GREATER_EQUAL);
    if (null !== $last)
    {
      $criterion->addAnd($whereCriteria->getNewCriterion(QubitTerm::RGT, $last, Criteria::LESS_EQUAL));
    }
    $whereCriteria->add($criterion);

    $valuesCriteria = new Criteria;
    $valuesCriteria->add(QubitTerm::RGT, array('raw' => QubitTerm::RGT . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

    BasePeer::doUpdate($whereCriteria, $valuesCriteria, $con);
  }
}
