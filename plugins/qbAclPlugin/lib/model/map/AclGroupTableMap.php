<?php


/**
 * This class defines the structure of the 'acl_group' table.
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
class AclGroupTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'plugins.qbAclPlugin.lib.model.map.AclGroupTableMap';

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
		$this->setName('acl_group');
		$this->setPhpName('aclGroup');
		$this->setClassname('QubitAclGroup');
		$this->setPackage('plugins.qbAclPlugin.lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addForeignKey('PARENT_ID', 'parentId', 'INTEGER', 'acl_group', 'ID', false, null, null);
		$this->addColumn('LFT', 'lft', 'INTEGER', true, null, null);
		$this->addColumn('RGT', 'rgt', 'INTEGER', true, null, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('UPDATED_AT', 'updatedAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('aclGroupRelatedByparentId', 'aclGroup', RelationMap::MANY_TO_ONE, array('parent_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('aclGroupRelatedByparentId', 'aclGroup', RelationMap::ONE_TO_MANY, array('id' => 'parent_id', ), 'CASCADE', null);
    $this->addRelation('aclGroupI18n', 'aclGroupI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('aclPermission', 'aclPermission', RelationMap::ONE_TO_MANY, array('id' => 'group_id', ), 'CASCADE', null);
    $this->addRelation('aclUserGroup', 'aclUserGroup', RelationMap::ONE_TO_MANY, array('id' => 'group_id', ), 'CASCADE', null);
	} // buildRelations()

} // AclGroupTableMap
