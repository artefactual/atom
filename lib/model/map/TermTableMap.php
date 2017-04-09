<?php


/**
 * This class defines the structure of the 'term' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    lib.model.map
 */
class TermTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.TermTableMap';

	/**
	 * Initialize the table attributes, columns and validators
	 * Relations are not initialized by this method since they are lazy loaded
	 *
	 * @return     void
	 * @throws     PropelException
	 */
	public function initialize()
	{
	  // attributes
		$this->setName('term');
		$this->setPhpName('term');
		$this->setClassname('QubitTerm');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addForeignKey('TAXONOMY_ID', 'taxonomyId', 'INTEGER', 'taxonomy', 'ID', true, null, null);
		$this->addColumn('CODE', 'code', 'VARCHAR', false, 1024, null);
		$this->addForeignKey('PARENT_ID', 'parentId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('LFT', 'lft', 'INTEGER', true, null, null);
		$this->addColumn('RGT', 'rgt', 'INTEGER', true, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('taxonomy', 'taxonomy', RelationMap::MANY_TO_ONE, array('taxonomy_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('termRelatedByparentId', 'term', RelationMap::MANY_TO_ONE, array('parent_id' => 'id', ), null, null);
    $this->addRelation('accessionRelatedByacquisitionTypeId', 'accession', RelationMap::ONE_TO_MANY, array('id' => 'acquisition_type_id', ), 'SET NULL', null);
    $this->addRelation('accessionRelatedByprocessingPriorityId', 'accession', RelationMap::ONE_TO_MANY, array('id' => 'processing_priority_id', ), 'SET NULL', null);
    $this->addRelation('accessionRelatedByprocessingStatusId', 'accession', RelationMap::ONE_TO_MANY, array('id' => 'processing_status_id', ), 'SET NULL', null);
    $this->addRelation('accessionRelatedByresourceTypeId', 'accession', RelationMap::ONE_TO_MANY, array('id' => 'resource_type_id', ), 'SET NULL', null);
    $this->addRelation('deaccession', 'deaccession', RelationMap::ONE_TO_MANY, array('id' => 'scope_id', ), 'SET NULL', null);
    $this->addRelation('actorRelatedByentityTypeId', 'actor', RelationMap::ONE_TO_MANY, array('id' => 'entity_type_id', ), 'SET NULL', null);
    $this->addRelation('actorRelatedBydescriptionStatusId', 'actor', RelationMap::ONE_TO_MANY, array('id' => 'description_status_id', ), 'SET NULL', null);
    $this->addRelation('actorRelatedBydescriptionDetailId', 'actor', RelationMap::ONE_TO_MANY, array('id' => 'description_detail_id', ), 'SET NULL', null);
    $this->addRelation('aip', 'aip', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), 'SET NULL', null);
    $this->addRelation('job', 'job', RelationMap::ONE_TO_MANY, array('id' => 'status_id', ), 'SET NULL', null);
    $this->addRelation('digitalObjectRelatedByusageId', 'digitalObject', RelationMap::ONE_TO_MANY, array('id' => 'usage_id', ), 'SET NULL', null);
    $this->addRelation('digitalObjectRelatedBymediaTypeId', 'digitalObject', RelationMap::ONE_TO_MANY, array('id' => 'media_type_id', ), 'SET NULL', null);
    $this->addRelation('event', 'event', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), 'CASCADE', null);
    $this->addRelation('functionRelatedBytypeId', 'function', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), null, null);
    $this->addRelation('functionRelatedBydescriptionStatusId', 'function', RelationMap::ONE_TO_MANY, array('id' => 'description_status_id', ), null, null);
    $this->addRelation('functionRelatedBydescriptionDetailId', 'function', RelationMap::ONE_TO_MANY, array('id' => 'description_detail_id', ), null, null);
    $this->addRelation('informationObjectRelatedBylevelOfDescriptionId', 'informationObject', RelationMap::ONE_TO_MANY, array('id' => 'level_of_description_id', ), 'SET NULL', null);
    $this->addRelation('informationObjectRelatedBycollectionTypeId', 'informationObject', RelationMap::ONE_TO_MANY, array('id' => 'collection_type_id', ), null, null);
    $this->addRelation('informationObjectRelatedBydescriptionStatusId', 'informationObject', RelationMap::ONE_TO_MANY, array('id' => 'description_status_id', ), 'SET NULL', null);
    $this->addRelation('informationObjectRelatedBydescriptionDetailId', 'informationObject', RelationMap::ONE_TO_MANY, array('id' => 'description_detail_id', ), 'SET NULL', null);
    $this->addRelation('informationObjectRelatedBydisplayStandardId', 'informationObject', RelationMap::ONE_TO_MANY, array('id' => 'display_standard_id', ), 'SET NULL', null);
    $this->addRelation('note', 'note', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), 'SET NULL', null);
    $this->addRelation('objectTermRelation', 'objectTermRelation', RelationMap::ONE_TO_MANY, array('id' => 'term_id', ), 'CASCADE', null);
    $this->addRelation('otherName', 'otherName', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), 'SET NULL', null);
    $this->addRelation('physicalObject', 'physicalObject', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), 'SET NULL', null);
    $this->addRelation('relation', 'relation', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), null, null);
    $this->addRelation('repositoryRelatedBydescStatusId', 'repository', RelationMap::ONE_TO_MANY, array('id' => 'desc_status_id', ), 'SET NULL', null);
    $this->addRelation('repositoryRelatedBydescDetailId', 'repository', RelationMap::ONE_TO_MANY, array('id' => 'desc_detail_id', ), 'SET NULL', null);
    $this->addRelation('rightsRelatedBybasisId', 'rights', RelationMap::ONE_TO_MANY, array('id' => 'basis_id', ), 'SET NULL', null);
    $this->addRelation('rightsRelatedBycopyrightStatusId', 'rights', RelationMap::ONE_TO_MANY, array('id' => 'copyright_status_id', ), 'SET NULL', null);
    $this->addRelation('rightsRelatedBystatuteCitationId', 'rights', RelationMap::ONE_TO_MANY, array('id' => 'statute_citation_id', ), 'SET NULL', null);
    $this->addRelation('grantedRight', 'grantedRight', RelationMap::ONE_TO_MANY, array('id' => 'act_id', ), 'SET NULL', null);
    $this->addRelation('statusRelatedBytypeId', 'status', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), 'CASCADE', null);
    $this->addRelation('statusRelatedBystatusId', 'status', RelationMap::ONE_TO_MANY, array('id' => 'status_id', ), 'CASCADE', null);
    $this->addRelation('termRelatedByparentId', 'term', RelationMap::ONE_TO_MANY, array('id' => 'parent_id', ), null, null);
    $this->addRelation('termI18n', 'termI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // TermTableMap
