<?php


/**
 * This class defines the structure of the 'accession_event' table.
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
class AccessionEventTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'plugins.qtAccessionPlugin.lib.model.map.AccessionEventTableMap';

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
		$this->setName('accession_event');
		$this->setPhpName('accessionEvent');
		$this->setClassname('QubitAccessionEvent');
		$this->setPackage('plugins.qtAccessionPlugin.lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addForeignKey('TYPE_ID', 'typeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('ACCESSION_ID', 'accessionId', 'INTEGER', 'accession', 'ID', false, null, null);
		$this->addColumn('DATE', 'date', 'DATE', false, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 16, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('term', 'term', RelationMap::MANY_TO_ONE, array('type_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('accession', 'accession', RelationMap::MANY_TO_ONE, array('accession_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('accessionEventI18n', 'accessionEventI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // AccessionEventTableMap
