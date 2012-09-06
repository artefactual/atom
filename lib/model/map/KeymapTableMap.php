<?php


/**
 * This class defines the structure of the 'keymap' table.
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
class KeymapTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.KeymapTableMap';

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
		$this->setName('keymap');
		$this->setPhpName('keymap');
		$this->setClassname('QubitKeymap');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addColumn('SOURCE_ID', 'sourceId', 'LONGVARCHAR', false, null, null);
		$this->addColumn('TARGET_ID', 'targetId', 'INTEGER', false, null, null);
		$this->addColumn('SOURCE_NAME', 'sourceName', 'LONGVARCHAR', false, null, null);
		$this->addColumn('TARGET_NAME', 'targetName', 'LONGVARCHAR', false, null, null);
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
	} // buildRelations()

} // KeymapTableMap
