<?php


/**
 * This class defines the structure of the 'job' table.
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
class JobTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.JobTableMap';

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
		$this->setName('job');
		$this->setPhpName('job');
		$this->setClassname('QubitJob');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addColumn('NAME', 'name', 'VARCHAR', false, 255, null);
		$this->addColumn('DOWNLOAD_PATH', 'downloadPath', 'LONGVARCHAR', false, null, null);
		$this->addColumn('COMPLETED_AT', 'completedAt', 'TIMESTAMP', false, null, null);
		$this->addForeignKey('USER_ID', 'userId', 'INTEGER', 'user', 'ID', false, null, null);
		$this->addForeignKey('OBJECT_ID', 'objectId', 'INTEGER', 'object', 'ID', false, null, null);
		$this->addForeignKey('STATUS_ID', 'statusId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('OUTPUT', 'output', 'LONGVARCHAR', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('objectRelatedByid', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('user', 'user', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('objectRelatedByobjectId', 'object', RelationMap::MANY_TO_ONE, array('object_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('term', 'term', RelationMap::MANY_TO_ONE, array('status_id' => 'id', ), 'SET NULL', null);
	} // buildRelations()

} // JobTableMap
