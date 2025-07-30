	<?php

class QubitRequestToPublish extends BaseRequestToPublish
{
  
  public function __toString()
  {
    $string = $this->rtp_name;	
    if (!isset($string))
    {
      $string = $this->getRTP_name(array('sourceCulture' => true));  
    }

    return (string) $string;
  }

  public function insert($connection = null)
  {
    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('rtp_name', array('sourceCulture' => true)));
	  
    }

    return parent::insert($connection);
  }

  public function getLabel()
  {
    $label = '';
 
	if (0 == strlen($unique_identifier = $this->getUnique_identifier()))
    {
      $unique_identifier = $this->getUnique_identifier(array('sourceCulture' => true));
    }

    if (0 < strlen($unique_identifier))
    {
      $label .= ' - '.$unique_identifier;
    }
		return $label;	 
	 
	if (0 == strlen($rtp_name = $this->getRTP_name()))
    {
      $rtp_name = $this->getRTP(array('sourceCulture' => true));
    }

    if (0 < strlen($rtp_name))
    {
      $label .= ' - '.$rtp_name;
    }
		return $label;	 
	 
	if (0 == strlen($row = $this->getRTP_surname()))
    {
      $row = $this->getRow(array('sourceCulture' => true));
    }

    if (0 < strlen($row))
    {
      $label .= ' - '.$row;
    }
	 return $label;	 
		
	 
	if (0 == strlen($rtp_email = $this->getRTP_email()))
    {
      $rtp_email = $this->getRTP_email(array('sourceCulture' => true));
    }

    if (0 < strlen($rtp_email))
    {
      $label .= ' - '.$rtp_email;
    }
	 return $label;	 
 
	if (0 == strlen($rtp_relationship = $this->getRTP_motivation()))
    {
      $rtp_relationship = $this->getRTP_motivation(array('sourceCulture' => true));
    }

    if (0 < strlen($rtp_motivation))
    {
      $label .= ' - '.$rtp_motivation;
    }
	 return $label;	 

	if (0 == strlen($created_at = $this->getCreated_At()))
    {
      $created_at = $this->getCreated_At(array('sourceCulture' => true));
    }

    if (0 < strlen($created_at))
    {
      $label .= ' - '.$created_at;
    }
	 return $label;	 

	if (0 == strlen($completedAt = $this->getCompletedAt()))
    {
      $completedAt = $this->getCompletedAt(array('sourceCulture' => true));
    }

    if (0 < strlen($completedAt))
    {
      $label .= ' - '.$completedAt;
    }
	 return $label;	 

	if (0 == strlen($STATUS_ID = $this->getStatusId()))
    {
      $statusId = $this->getStatusId(array('sourceCulture' => true));
    }

    if (0 < strlen($statusId))
    {
      $label .= ' - '.$statusId;
    }
	 return $label;	 
 } 
 
 /**
   * Overwrite BaseRequestToPublish::delete() method to add cascading delete
   * logic
   *
   * @param mixed $connection a database connection object
   */
  public function delete($connection = null)
  {
    $this->deleteInformationObjectRelations();

    parent::delete($connection);
  }

  /**
   * Delete relation records linking this RequestToPublish object to information objects
   */
  public function deleteInformationObjectRelations()
  {
    $informationObjectRelations = QubitRelation::getRelationsBySubjectId($this->id,
    array('requestorId'=>QubitTaxonomy::REQUEST_TO_PUBLISH_ID));

    foreach ($informationObjectRelations as $relation)
    {
      $relation->delete();
    }
  }

  /**
   * Get related information object via QubitRelation relationship
   *
   * @param array $options list of options to pass to QubitQuery
   * @return QubitQuery collection of Information Objects
   */
  public function getInformationObjects($options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitRequestToPublish::ID, QubitRelation::SUBJECT_ID);
    $criteria->addJoin(QubitRelation::OBJECT_ID, QubitInformationObject::ID);
    $criteria->add(QubitRequestToPublish::ID, $this->id);

    return QubitQuery::createFromCriteria($criteria, 'QubitInformationObject', $options);
  }
  
  /**
   * Only find RequestToPublish objects, not other actor types
   *
   * @param Criteria $criteria current search criteria
   * @return Criteria modified search critieria
   */
  public static function addGetOnlyRequestToPublishCriteria($criteria)
  {
    $criteria->addJoin(QubitRequestToPublish::ID, QubitObject::ID);
    $criteria->add(QubitObject::CLASS_NAME, 'QubitRequestToPublish');

    return $criteria;
  }
  
}
