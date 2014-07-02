<?php


/**
 * This class defines the structure of the 'fixity_recovery' table.
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
class FixityRecoveryTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.FixityRecoveryTableMap';

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
		$this->setName('fixity_recovery');
		$this->setPhpName('fixityRecovery');
		$this->setClassname('QubitFixityRecovery');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addColumn('SUCCESS', 'success', 'BOOLEAN', false, null, null);
		$this->addColumn('MESSAGE', 'message', 'VARCHAR', false, 255, null);
		$this->addForeignKey('AIP_ID', 'aipId', 'INTEGER', 'aip', 'ID', false, null, null);
		$this->addColumn('TIME_STARTED', 'timeStarted', 'TIMESTAMP', false, null, null);
		$this->addColumn('TIME_COMPLETED', 'timeCompleted', 'TIMESTAMP', false, null, null);
		$this->addForeignKey('USER_ID', 'userId', 'INTEGER', 'user', 'ID', false, null, null);
		$this->addColumn('STORAGE_SERVICE_EVENT_ID', 'storageServiceEventId', 'INTEGER', true, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('aip', 'aip', RelationMap::MANY_TO_ONE, array('aip_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('user', 'user', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'SET NULL', null);
	} // buildRelations()

} // FixityRecoveryTableMap
