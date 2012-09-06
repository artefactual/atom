<?php


/**
 * This class defines the structure of the 'oai_repository' table.
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
class OaiRepositoryTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.OaiRepositoryTableMap';

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
		$this->setName('oai_repository');
		$this->setPhpName('oaiRepository');
		$this->setClassname('QubitOaiRepository');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addColumn('NAME', 'name', 'VARCHAR', false, 1024, null);
		$this->addColumn('URI', 'uri', 'VARCHAR', false, 1024, null);
		$this->addColumn('ADMIN_EMAIL', 'adminEmail', 'VARCHAR', false, 255, null);
		$this->addColumn('EARLIEST_TIMESTAMP', 'earliestTimestamp', 'TIMESTAMP', false, null, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('UPDATED_AT', 'updatedAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('oaiHarvest', 'oaiHarvest', RelationMap::ONE_TO_MANY, array('id' => 'oai_repository_id', ), 'CASCADE', null);
	} // buildRelations()

} // OaiRepositoryTableMap
