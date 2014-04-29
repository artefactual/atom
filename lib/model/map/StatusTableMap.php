<?php


/**
 * This class defines the structure of the 'status' table.
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
class StatusTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.StatusTableMap';

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
		$this->setName('status');
		$this->setPhpName('status');
		$this->setClassname('QubitStatus');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addForeignKey('OBJECT_ID', 'objectId', 'INTEGER', 'object', 'ID', true, null, null);
		$this->addForeignKey('TYPE_ID', 'typeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('STATUS_ID', 'statusId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('object_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('termRelatedBytypeId', 'term', RelationMap::MANY_TO_ONE, array('type_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('termRelatedBystatusId', 'term', RelationMap::MANY_TO_ONE, array('status_id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // StatusTableMap
