<?php


/**
 * This class defines the structure of the 'user' table.
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
class UserTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.UserTableMap';

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
		$this->setName('user');
		$this->setPhpName('user');
		$this->setClassname('QubitUser');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'actor', 'ID', true, null, null);
		$this->addColumn('USERNAME', 'username', 'VARCHAR', false, 255, null);
		$this->addColumn('EMAIL', 'email', 'VARCHAR', false, 255, null);
		$this->addColumn('SHA1_PASSWORD', 'sha1Password', 'VARCHAR', false, 255, null);
		$this->addColumn('SALT', 'salt', 'VARCHAR', false, 255, null);
		$this->addColumn('ACTIVE', 'active', 'BOOLEAN', false, null, true);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('actor', 'actor', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('job', 'job', RelationMap::ONE_TO_MANY, array('id' => 'user_id', ), 'SET NULL', null);
    $this->addRelation('note', 'note', RelationMap::ONE_TO_MANY, array('id' => 'user_id', ), null, null);
    $this->addRelation('aclPermission', 'aclPermission', RelationMap::ONE_TO_MANY, array('id' => 'user_id', ), 'CASCADE', null);
    $this->addRelation('aclUserGroup', 'aclUserGroup', RelationMap::ONE_TO_MANY, array('id' => 'user_id', ), 'CASCADE', null);
	} // buildRelations()

} // UserTableMap
