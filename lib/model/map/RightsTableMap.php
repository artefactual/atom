<?php


/**
 * This class defines the structure of the 'rights' table.
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
class RightsTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.RightsTableMap';

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
		$this->setName('rights');
		$this->setPhpName('rights');
		$this->setClassname('QubitRights');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addColumn('START_DATE', 'startDate', 'DATE', false, null, null);
		$this->addColumn('END_DATE', 'endDate', 'DATE', false, null, null);
		$this->addForeignKey('BASIS_ID', 'basisId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('RIGHTS_HOLDER_ID', 'rightsHolderId', 'INTEGER', 'actor', 'ID', false, null, null);
		$this->addForeignKey('COPYRIGHT_STATUS_ID', 'copyrightStatusId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('COPYRIGHT_STATUS_DATE', 'copyrightStatusDate', 'DATE', false, null, null);
		$this->addColumn('COPYRIGHT_JURISDICTION', 'copyrightJurisdiction', 'VARCHAR', false, 1024, null);
		$this->addColumn('STATUTE_DETERMINATION_DATE', 'statuteDeterminationDate', 'DATE', false, null, null);
		$this->addForeignKey('STATUTE_CITATION_ID', 'statuteCitationId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('termRelatedBybasisId', 'term', RelationMap::MANY_TO_ONE, array('basis_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('actor', 'actor', RelationMap::MANY_TO_ONE, array('rights_holder_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBycopyrightStatusId', 'term', RelationMap::MANY_TO_ONE, array('copyright_status_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBystatuteCitationId', 'term', RelationMap::MANY_TO_ONE, array('statute_citation_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('grantedRight', 'grantedRight', RelationMap::ONE_TO_MANY, array('id' => 'rights_id', ), 'CASCADE', null);
    $this->addRelation('rightsI18n', 'rightsI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // RightsTableMap
