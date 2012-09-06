<?php


/**
 * This class defines the structure of the 'accession_i18n' table.
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
class AccessionI18nTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'plugins.qtAccessionPlugin.lib.model.map.AccessionI18nTableMap';

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
		$this->setName('accession_i18n');
		$this->setPhpName('accessionI18n');
		$this->setClassname('QubitAccessionI18n');
		$this->setPackage('plugins.qtAccessionPlugin.lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addColumn('APPRAISAL', 'appraisal', 'LONGVARCHAR', false, null, null);
		$this->addColumn('ARCHIVAL_HISTORY', 'archivalHistory', 'LONGVARCHAR', false, null, null);
		$this->addColumn('LOCATION_INFORMATION', 'locationInformation', 'LONGVARCHAR', false, null, null);
		$this->addColumn('PHYSICAL_CHARACTERISTICS', 'physicalCharacteristics', 'LONGVARCHAR', false, null, null);
		$this->addColumn('PROCESSING_NOTES', 'processingNotes', 'LONGVARCHAR', false, null, null);
		$this->addColumn('RECEIVED_EXTENT_UNITS', 'receivedExtentUnits', 'LONGVARCHAR', false, null, null);
		$this->addColumn('SCOPE_AND_CONTENT', 'scopeAndContent', 'LONGVARCHAR', false, null, null);
		$this->addColumn('SOURCE_OF_ACQUISITION', 'sourceOfAcquisition', 'LONGVARCHAR', false, null, null);
		$this->addColumn('TITLE', 'title', 'VARCHAR', false, 255, null);
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'accession', 'ID', true, null, null);
		$this->addPrimaryKey('CULTURE', 'culture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('accession', 'accession', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // AccessionI18nTableMap
