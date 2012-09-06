<?php


/**
 * This class defines the structure of the 'repository_i18n' table.
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
class RepositoryI18nTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.RepositoryI18nTableMap';

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
		$this->setName('repository_i18n');
		$this->setPhpName('repositoryI18n');
		$this->setClassname('QubitRepositoryI18n');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addColumn('GEOCULTURAL_CONTEXT', 'geoculturalContext', 'LONGVARCHAR', false, null, null);
		$this->addColumn('COLLECTING_POLICIES', 'collectingPolicies', 'LONGVARCHAR', false, null, null);
		$this->addColumn('BUILDINGS', 'buildings', 'LONGVARCHAR', false, null, null);
		$this->addColumn('HOLDINGS', 'holdings', 'LONGVARCHAR', false, null, null);
		$this->addColumn('FINDING_AIDS', 'findingAids', 'LONGVARCHAR', false, null, null);
		$this->addColumn('OPENING_TIMES', 'openingTimes', 'LONGVARCHAR', false, null, null);
		$this->addColumn('ACCESS_CONDITIONS', 'accessConditions', 'LONGVARCHAR', false, null, null);
		$this->addColumn('DISABLED_ACCESS', 'disabledAccess', 'LONGVARCHAR', false, null, null);
		$this->addColumn('RESEARCH_SERVICES', 'researchServices', 'LONGVARCHAR', false, null, null);
		$this->addColumn('REPRODUCTION_SERVICES', 'reproductionServices', 'LONGVARCHAR', false, null, null);
		$this->addColumn('PUBLIC_FACILITIES', 'publicFacilities', 'LONGVARCHAR', false, null, null);
		$this->addColumn('DESC_INSTITUTION_IDENTIFIER', 'descInstitutionIdentifier', 'VARCHAR', false, 1024, null);
		$this->addColumn('DESC_RULES', 'descRules', 'LONGVARCHAR', false, null, null);
		$this->addColumn('DESC_SOURCES', 'descSources', 'LONGVARCHAR', false, null, null);
		$this->addColumn('DESC_REVISION_HISTORY', 'descRevisionHistory', 'LONGVARCHAR', false, null, null);
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'repository', 'ID', true, null, null);
		$this->addPrimaryKey('CULTURE', 'culture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('repository', 'repository', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // RepositoryI18nTableMap
