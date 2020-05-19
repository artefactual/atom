<?php


/**
 * This class defines the structure of the 'function_object_i18n' table.
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
class FunctionObjectI18nTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.FunctionObjectI18nTableMap';

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
		$this->setName('function_object_i18n');
		$this->setPhpName('functionObjectI18n');
		$this->setClassname('QubitFunctionObjectI18n');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addColumn('AUTHORIZED_FORM_OF_NAME', 'authorizedFormOfName', 'VARCHAR', false, 1024, null);
		$this->addColumn('CLASSIFICATION', 'classification', 'VARCHAR', false, 1024, null);
		$this->addColumn('DATES', 'dates', 'VARCHAR', false, 1024, null);
		$this->addColumn('DESCRIPTION', 'description', 'LONGVARCHAR', false, null, null);
		$this->addColumn('HISTORY', 'history', 'LONGVARCHAR', false, null, null);
		$this->addColumn('LEGISLATION', 'legislation', 'LONGVARCHAR', false, null, null);
		$this->addColumn('INSTITUTION_IDENTIFIER', 'institutionIdentifier', 'LONGVARCHAR', false, null, null);
		$this->addColumn('REVISION_HISTORY', 'revisionHistory', 'LONGVARCHAR', false, null, null);
		$this->addColumn('RULES', 'rules', 'LONGVARCHAR', false, null, null);
		$this->addColumn('SOURCES', 'sources', 'LONGVARCHAR', false, null, null);
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'function_object', 'ID', true, null, null);
		$this->addPrimaryKey('CULTURE', 'culture', 'VARCHAR', true, 16, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('functionObject', 'functionObject', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // FunctionObjectI18nTableMap
