<?php


/**
 * This class defines the structure of the 'object' table.
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
class ObjectTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.ObjectTableMap';

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
		$this->setName('object');
		$this->setPhpName('object');
		$this->setClassname('QubitObject');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addColumn('CLASS_NAME', 'className', 'VARCHAR', false, 255, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('UPDATED_AT', 'updatedAt', 'TIMESTAMP', true, null, null);
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('accession', 'accession', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('deaccession', 'deaccession', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('accessLog', 'accessLog', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
    $this->addRelation('actor', 'actor', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('aipRelatedByid', 'aip', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('aipRelatedBypartOf', 'aip', RelationMap::ONE_TO_MANY, array('id' => 'part_of', ), 'SET NULL', null);
    $this->addRelation('jobRelatedByid', 'job', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('jobRelatedByobjectId', 'job', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'SET NULL', null);
    $this->addRelation('digitalObject', 'digitalObject', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('eventRelatedByid', 'event', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('eventRelatedByobjectId', 'event', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
    $this->addRelation('function', 'function', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('informationObject', 'informationObject', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('note', 'note', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
    $this->addRelation('objectTermRelationRelatedByid', 'objectTermRelation', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('objectTermRelationRelatedByobjectId', 'objectTermRelation', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
    $this->addRelation('otherName', 'otherName', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
    $this->addRelation('physicalObject', 'physicalObject', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('premisObject', 'premisObject', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('property', 'property', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
    $this->addRelation('relationRelatedByid', 'relation', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('relationRelatedBysubjectId', 'relation', RelationMap::ONE_TO_MANY, array('id' => 'subject_id', ), null, null);
    $this->addRelation('relationRelatedByobjectId', 'relation', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), null, null);
    $this->addRelation('rights', 'rights', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('slug', 'slug', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
    $this->addRelation('staticPage', 'staticPage', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('status', 'status', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
    $this->addRelation('taxonomy', 'taxonomy', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('term', 'term', RelationMap::ONE_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('aclPermission', 'aclPermission', RelationMap::ONE_TO_MANY, array('id' => 'object_id', ), 'CASCADE', null);
	} // buildRelations()

} // ObjectTableMap
