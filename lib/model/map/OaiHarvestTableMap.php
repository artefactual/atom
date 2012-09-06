<?php


/**
 * This class defines the structure of the 'oai_harvest' table.
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
class OaiHarvestTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.OaiHarvestTableMap';

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
		$this->setName('oai_harvest');
		$this->setPhpName('oaiHarvest');
		$this->setClassname('QubitOaiHarvest');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addForeignKey('OAI_REPOSITORY_ID', 'oaiRepositoryId', 'INTEGER', 'oai_repository', 'ID', true, null, null);
		$this->addColumn('START_TIMESTAMP', 'startTimestamp', 'TIMESTAMP', false, null, null);
		$this->addColumn('END_TIMESTAMP', 'endTimestamp', 'TIMESTAMP', false, null, null);
		$this->addColumn('LAST_HARVEST', 'lastHarvest', 'TIMESTAMP', false, null, null);
		$this->addColumn('LAST_HARVEST_ATTEMPT', 'lastHarvestAttempt', 'TIMESTAMP', false, null, null);
		$this->addColumn('METADATAPREFIX', 'metadataPrefix', 'VARCHAR', false, 255, null);
		$this->addColumn('SET', 'set', 'VARCHAR', false, 1024, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('oaiRepository', 'oaiRepository', RelationMap::MANY_TO_ONE, array('oai_repository_id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // OaiHarvestTableMap
