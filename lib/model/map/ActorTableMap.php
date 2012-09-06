<?php


/**
 * This class defines the structure of the 'actor' table.
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
class ActorTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.ActorTableMap';

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
		$this->setName('actor');
		$this->setPhpName('actor');
		$this->setClassname('QubitActor');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addColumn('CORPORATE_BODY_IDENTIFIERS', 'corporateBodyIdentifiers', 'VARCHAR', false, 1024, null);
		$this->addForeignKey('ENTITY_TYPE_ID', 'entityTypeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('DESCRIPTION_STATUS_ID', 'descriptionStatusId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('DESCRIPTION_DETAIL_ID', 'descriptionDetailId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('DESCRIPTION_IDENTIFIER', 'descriptionIdentifier', 'VARCHAR', false, 1024, null);
		$this->addColumn('SOURCE_STANDARD', 'sourceStandard', 'VARCHAR', false, 1024, null);
		$this->addForeignKey('PARENT_ID', 'parentId', 'INTEGER', 'actor', 'ID', false, null, null);
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
    $this->addRelation('termRelatedByentityTypeId', 'term', RelationMap::MANY_TO_ONE, array('entity_type_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBydescriptionStatusId', 'term', RelationMap::MANY_TO_ONE, array('description_status_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBydescriptionDetailId', 'term', RelationMap::MANY_TO_ONE, array('description_detail_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('actorRelatedByparentId', 'actor', RelationMap::MANY_TO_ONE, array('parent_id' => 'id', ), null, null);
    $this->addRelation('donor', 'donor', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('actorRelatedByparentId', 'actor', RelationMap::ONE_TO_MANY, array('id' => 'parent_id', ), null, null);
    $this->addRelation('actorI18n', 'actorI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('contactInformation', 'contactInformation', RelationMap::ONE_TO_MANY, array('id' => 'actor_id', ), 'CASCADE', null);
    $this->addRelation('event', 'event', RelationMap::ONE_TO_MANY, array('id' => 'actor_id', ), null, null);
    $this->addRelation('repository', 'repository', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('rights', 'rights', RelationMap::ONE_TO_MANY, array('id' => 'rights_holder_id', ), 'SET NULL', null);
    $this->addRelation('rightsHolder', 'rightsHolder', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('user', 'user', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // ActorTableMap
