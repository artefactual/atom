<?php


/**
 * This class defines the structure of the 'fixity_report' table.
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
class FixityReportTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.FixityReportTableMap';

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
		$this->setName('fixity_report');
		$this->setPhpName('fixityReport');
		$this->setClassname('QubitFixityReport');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addColumn('SUCCESS', 'success', 'BOOLEAN', false, null, null);
		$this->addColumn('MESSAGE', 'message', 'VARCHAR', false, 255, null);
		$this->addColumn('FAILURES', 'failures', 'LONGVARCHAR', false, null, null);
		$this->addColumn('COLLECTION_CHECK_ID', 'collectionCheckId', 'INTEGER', false, null, null);
		$this->addForeignKey('AIP_ID', 'aipId', 'INTEGER', 'aip', 'ID', false, null, null);
		$this->addColumn('UUID', 'uuid', 'VARCHAR', false, 36, null);
		$this->addColumn('TIME_STARTED', 'timeStarted', 'TIMESTAMP', false, null, null);
		$this->addColumn('TIME_COMPLETED', 'timeCompleted', 'TIMESTAMP', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('aip', 'aip', RelationMap::MANY_TO_ONE, array('aip_id' => 'id', ), 'SET NULL', null);
	} // buildRelations()

} // FixityReportTableMap
