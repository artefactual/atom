<?php


/**
 * This class defines the structure of the 'information_object_i18n' table.
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
class InformationObjectI18nTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.InformationObjectI18nTableMap';

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
		$this->setName('information_object_i18n');
		$this->setPhpName('informationObjectI18n');
		$this->setClassname('QubitInformationObjectI18n');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addColumn('TITLE', 'title', 'VARCHAR', false, 1024, null);
		$this->addColumn('ALTERNATE_TITLE', 'alternateTitle', 'VARCHAR', false, 1024, null);
		$this->addColumn('EDITION', 'edition', 'VARCHAR', false, 1024, null);
		$this->addColumn('EXTENT_AND_MEDIUM', 'extentAndMedium', 'LONGVARCHAR', false, null, null);
		$this->addColumn('ARCHIVAL_HISTORY', 'archivalHistory', 'LONGVARCHAR', false, null, null);
		$this->addColumn('ACQUISITION', 'acquisition', 'LONGVARCHAR', false, null, null);
		$this->addColumn('SCOPE_AND_CONTENT', 'scopeAndContent', 'LONGVARCHAR', false, null, null);
		$this->addColumn('APPRAISAL', 'appraisal', 'LONGVARCHAR', false, null, null);
		$this->addColumn('ACCRUALS', 'accruals', 'LONGVARCHAR', false, null, null);
		$this->addColumn('ARRANGEMENT', 'arrangement', 'LONGVARCHAR', false, null, null);
		$this->addColumn('ACCESS_CONDITIONS', 'accessConditions', 'LONGVARCHAR', false, null, null);
		$this->addColumn('REPRODUCTION_CONDITIONS', 'reproductionConditions', 'LONGVARCHAR', false, null, null);
		$this->addColumn('PHYSICAL_CHARACTERISTICS', 'physicalCharacteristics', 'LONGVARCHAR', false, null, null);
		$this->addColumn('FINDING_AIDS', 'findingAids', 'LONGVARCHAR', false, null, null);
		$this->addColumn('LOCATION_OF_ORIGINALS', 'locationOfOriginals', 'LONGVARCHAR', false, null, null);
		$this->addColumn('LOCATION_OF_COPIES', 'locationOfCopies', 'LONGVARCHAR', false, null, null);
		$this->addColumn('RELATED_UNITS_OF_DESCRIPTION', 'relatedUnitsOfDescription', 'LONGVARCHAR', false, null, null);
		$this->addColumn('INSTITUTION_RESPONSIBLE_IDENTIFIER', 'institutionResponsibleIdentifier', 'VARCHAR', false, 1024, null);
		$this->addColumn('RULES', 'rules', 'LONGVARCHAR', false, null, null);
		$this->addColumn('SOURCES', 'sources', 'LONGVARCHAR', false, null, null);
		$this->addColumn('REVISION_HISTORY', 'revisionHistory', 'LONGVARCHAR', false, null, null);
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'information_object', 'ID', true, null, null);
		$this->addPrimaryKey('CULTURE', 'culture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('informationObject', 'informationObject', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // InformationObjectI18nTableMap
