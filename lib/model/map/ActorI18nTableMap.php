<?php


/**
 * This class defines the structure of the 'actor_i18n' table.
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
class ActorI18nTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.ActorI18nTableMap';

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
		$this->setName('actor_i18n');
		$this->setPhpName('actorI18n');
		$this->setClassname('QubitActorI18n');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addColumn('AUTHORIZED_FORM_OF_NAME', 'authorizedFormOfName', 'VARCHAR', false, 1024, null);
		$this->addColumn('DATES_OF_EXISTENCE', 'datesOfExistence', 'VARCHAR', false, 1024, null);
		$this->addColumn('HISTORY', 'history', 'LONGVARCHAR', false, null, null);
		$this->addColumn('PLACES', 'places', 'LONGVARCHAR', false, null, null);
		$this->addColumn('LEGAL_STATUS', 'legalStatus', 'LONGVARCHAR', false, null, null);
		$this->addColumn('FUNCTIONS', 'functions', 'LONGVARCHAR', false, null, null);
		$this->addColumn('MANDATES', 'mandates', 'LONGVARCHAR', false, null, null);
		$this->addColumn('INTERNAL_STRUCTURES', 'internalStructures', 'LONGVARCHAR', false, null, null);
		$this->addColumn('GENERAL_CONTEXT', 'generalContext', 'LONGVARCHAR', false, null, null);
		$this->addColumn('INSTITUTION_RESPONSIBLE_IDENTIFIER', 'institutionResponsibleIdentifier', 'VARCHAR', false, 1024, null);
		$this->addColumn('RULES', 'rules', 'LONGVARCHAR', false, null, null);
		$this->addColumn('SOURCES', 'sources', 'LONGVARCHAR', false, null, null);
		$this->addColumn('REVISION_HISTORY', 'revisionHistory', 'LONGVARCHAR', false, null, null);
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'actor', 'ID', true, null, null);
		$this->addPrimaryKey('CULTURE', 'culture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('actor', 'actor', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // ActorI18nTableMap
