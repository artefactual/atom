<?php


/**
 * This class defines the structure of the 'deaccession_i18n' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    plugins.qtAccessionPlugin.lib.model.map
 */
class DeaccessionI18nTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'plugins.qtAccessionPlugin.lib.model.map.DeaccessionI18nTableMap';

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
		$this->setName('deaccession_i18n');
		$this->setPhpName('deaccessionI18n');
		$this->setClassname('QubitDeaccessionI18n');
		$this->setPackage('plugins.qtAccessionPlugin.lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addColumn('DESCRIPTION', 'description', 'LONGVARCHAR', false, null, null);
		$this->addColumn('EXTENT', 'extent', 'LONGVARCHAR', false, null, null);
		$this->addColumn('REASON', 'reason', 'LONGVARCHAR', false, null, null);
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'deaccession', 'ID', true, null, null);
		$this->addPrimaryKey('CULTURE', 'culture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('deaccession', 'deaccession', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // DeaccessionI18nTableMap
