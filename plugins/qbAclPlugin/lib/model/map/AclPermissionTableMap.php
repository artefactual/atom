<?php


/**
 * This class defines the structure of the 'acl_permission' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    plugins.qbAclPlugin.lib.model.map
 */
class AclPermissionTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'plugins.qbAclPlugin.lib.model.map.AclPermissionTableMap';

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
		$this->setName('acl_permission');
		$this->setPhpName('aclPermission');
		$this->setClassname('QubitAclPermission');
		$this->setPackage('plugins.qbAclPlugin.lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addForeignKey('USER_ID', 'userId', 'INTEGER', 'user', 'ID', false, null, null);
		$this->addForeignKey('GROUP_ID', 'groupId', 'INTEGER', 'acl_group', 'ID', false, null, null);
		$this->addForeignKey('OBJECT_ID', 'objectId', 'INTEGER', 'object', 'ID', false, null, null);
		$this->addColumn('ACTION', 'action', 'VARCHAR', false, 255, null);
		$this->addColumn('GRANT_DENY', 'grantDeny', 'INTEGER', true, null, 0);
		$this->addColumn('CONDITIONAL', 'conditional', 'LONGVARCHAR', false, null, null);
		$this->addColumn('CONSTANTS', 'constants', 'LONGVARCHAR', false, null, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('UPDATED_AT', 'updatedAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('user', 'user', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('aclGroup', 'aclGroup', RelationMap::MANY_TO_ONE, array('group_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('object_id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // AclPermissionTableMap
