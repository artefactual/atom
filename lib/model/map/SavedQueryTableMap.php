<?php


/**
 * This class defines the structure of the 'saved_query' table.
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
class SavedQueryTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.SavedQueryTableMap';

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
		$this->setName('saved_query');
		$this->setPhpName('savedQuery');
		$this->setClassname('QubitSavedQuery');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addForeignKey('TYPE_ID', 'typeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('SCOPE', 'scope', 'VARCHAR', false, 50, null);
		$this->addColumn('NAME', 'name', 'VARCHAR', false, 255, null);
		$this->addColumn('DESCRIPTION', 'description', 'VARCHAR', false, 1024, null);
		$this->addForeignKey('USER_ID', 'userId', 'INTEGER', 'user', 'ID', false, null, null);
		$this->addColumn('PARAMS', 'params', 'LONGVARCHAR', false, null, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('UPDATED_AT', 'updatedAt', 'TIMESTAMP', true, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('term', 'term', RelationMap::MANY_TO_ONE, array('type_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('user', 'user', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'SET NULL', null);
	} // buildRelations()

} // SavedQueryTableMap
