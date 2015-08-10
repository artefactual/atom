<?php


/**
 * This class defines the structure of the 'rights_i18n' table.
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
class RightsI18nTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.RightsI18nTableMap';

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
		$this->setName('rights_i18n');
		$this->setPhpName('rightsI18n');
		$this->setClassname('QubitRightsI18n');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addColumn('RIGHTS_NOTE', 'rightsNote', 'LONGVARCHAR', false, null, null);
		$this->addColumn('COPYRIGHT_NOTE', 'copyrightNote', 'LONGVARCHAR', false, null, null);
		$this->addColumn('IDENTIFIER_VALUE', 'identifierValue', 'LONGVARCHAR', false, null, null);
		$this->addColumn('IDENTIFIER_TYPE', 'identifierType', 'LONGVARCHAR', false, null, null);
		$this->addColumn('IDENTIFIER_ROLE', 'identifierRole', 'LONGVARCHAR', false, null, null);
		$this->addColumn('LICENSE_TERMS', 'licenseTerms', 'LONGVARCHAR', false, null, null);
		$this->addColumn('LICENSE_NOTE', 'licenseNote', 'LONGVARCHAR', false, null, null);
		$this->addColumn('STATUTE_JURISDICTION', 'statuteJurisdiction', 'LONGVARCHAR', false, null, null);
		$this->addColumn('STATUTE_NOTE', 'statuteNote', 'LONGVARCHAR', false, null, null);
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'rights', 'ID', true, null, null);
		$this->addPrimaryKey('CULTURE', 'culture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('rights', 'rights', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // RightsI18nTableMap
