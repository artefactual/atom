<?php

class QubitFeedback extends BaseFeedback
{
  public function __toString()
  {
    $string = $this->name;
    if (!isset($string)) {
      $string = $this->getName(['sourceCulture' => true]);
    }

    return (string) $string;
  }

  public function insert($connection = null)
  {
    if (!isset($this->slug)) {
      $this->slug = QubitSlug::slugify($this->__get('name', ['sourceCulture' => true]));
    }

    return parent::insert($connection);
  }

  public function getLabel()
  {
    $label = '';

    if ($this->name) {
      $label .= $this->name.': ';
    }

    if (0 == strlen($remarks = $this->getRemarks())) {
      $remarks = $this->getRemarks(['sourceCulture' => true]);
    }

    if (0 < strlen($remarks)) {
      $label .= ' - '.$remarks;
    }

     return $label;
    if (0 == strlen($feedTypeId = $this->getFeedTypeId())) {
      $feedTypeId = $this->getFeedTypeId(['sourceCulture' => true]);
    }

    if (0 < strlen($feedTypeId)) {
      $label .= ' - '.$feedTypeId;
    }

     return $label;
    if (0 == strlen($unique_identifier = $this->getUnique_identifier())) {
      $unique_identifier = $this->getUnique_identifier(['sourceCulture' => true]);
    }

    if (0 < strlen($unique_identifier)) {
      $label .= ' - '.$unique_identifier;
    }

        return $label;
    if (0 == strlen($feed_name = $this->getFeed_name())) {
      $feed_name = $this->getFeed(['sourceCulture' => true]);
    }

    if (0 < strlen($feed_name)) {
      $label .= ' - '.$feed_name;
    }

        return $label;
    if (0 == strlen($row = $this->getFeed_surname())) {
      $row = $this->getRow(['sourceCulture' => true]);
    }

    if (0 < strlen($row)) {
      $label .= ' - '.$row;
    }

     return $label;
    if (0 == strlen($feed_email = $this->getFeed_email())) {
      $feed_email = $this->getFeed_email(['sourceCulture' => true]);
    }

    if (0 < strlen($feed_email)) {
      $label .= ' - '.$feed_email;
    }

     return $label;
    if (0 == strlen($feed_relationship = $this->getFeed_relationship())) {
      $feed_relationship = $this->getFeed_relationship(['sourceCulture' => true]);
    }

    if (0 < strlen($feed_relationship)) {
      $label .= ' - '.$feed_relationship;
    }

     return $label;
    if (0 == strlen($created_at = $this->getCreated_At())) {
      $created_at = $this->getCreated_At(['sourceCulture' => true]);
    }

    if (0 < strlen($created_at)) {
      $label .= ' - '.$created_at;
    }

     return $label;
    if (0 == strlen($completedAt = $this->getCompletedAt())) {
      $completedAt = $this->getCompletedAt(['sourceCulture' => true]);
    }

    if (0 < strlen($completedAt)) {
      $label .= ' - '.$completedAt;
    }

     return $label;
    if (0 == strlen($STATUS_ID = $this->getStatusId())) {
      $statusId = $this->getStatusId(['sourceCulture' => true]);
    }

    if (0 < strlen($statusId)) {
      $label .= ' - '.$statusId;
    }

     return $label;
 }

  /**
   * Overwrite BaseFeedback::delete() method to add cascading delete
   * logic.
   *
   * @param mixed $connection a database connection object
   */
  public function delete($connection = null)
  {
    $this->deleteInformationObjectRelations();

    parent::delete($connection);
  }

  /**
   * Delete relation records linking this Feedback object to information objects.
   */
  public function deleteInformationObjectRelations()
  {
    $informationObjectRelations = QubitRelation::getRelationsBySubjectId($this->id,
    ['requestorId' => QubitTaxonomy::FEEDBACK_TYPE_ID]);

    foreach ($informationObjectRelations as $relation) {
      $relation->delete();
    }
  }

  /**
   * Get related information object via QubitRelation relationship.
   *
   * @param array $options list of options to pass to QubitQuery
   *
   * @return QubitQuery collection of Information Objects
   */
  public function getInformationObjects($options = [])
  {
    $criteria = new Criteria();
    $criteria->addJoin(QubitFeedback::ID, QubitRelation::SUBJECT_ID);
    $criteria->addJoin(QubitRelation::OBJECT_ID, QubitInformationObject::ID);
    $criteria->add(QubitFeedback::ID, $this->id);

    return QubitQuery::createFromCriteria($criteria, 'QubitInformationObject', $options);
  }

  /**
   * Only find Feedback objects, not other actor types.
   *
   * @param Criteria $criteria current search criteria
   *
   * @return Criteria modified search critieria
   */
  public static function addGetOnlyFeedbackCriteria($criteria)
  {
    $criteria->addJoin(QubitFeedback::ID, QubitObject::ID);
    $criteria->add(QubitObject::CLASS_NAME, 'QubitFeedback');

    return $criteria;
  }
}
