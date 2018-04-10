<?php


/**
 * This class defines the structure of the 'audit_log' table.
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
class AuditLogTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.AuditLogTableMap';

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
		$this->setName('audit_log');
		$this->setPhpName('auditLog');
		$this->setClassname('QubitAuditLog');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addForeignKey('OBJECT_ID', 'objectId', 'INTEGER', 'object', 'ID', true, null, null);
		$this->addForeignKey('USER_ID', 'userId', 'INTEGER', 'user', 'ID', false, null, null);
		$this->addColumn('USER_NAME', 'userName', 'VARCHAR', false, 255, null);
		$this->addForeignKey('ACTION_TYPE_ID', 'actionTypeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('object_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('user', 'user', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('term', 'term', RelationMap::MANY_TO_ONE, array('action_type_id' => 'id', ), 'SET NULL', null);
	} // buildRelations()

} // AuditLogTableMap
