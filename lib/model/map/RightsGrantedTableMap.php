<?php


/**
 * This class defines the structure of the 'rights_granted' table.
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
class RightsGrantedTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.RightsGrantedTableMap';

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
		$this->setName('rights_granted');
		$this->setPhpName('rightsGranted');
		$this->setClassname('QubitRightsGranted');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addForeignKey('RIGHTS_ID', 'rightsId', 'INTEGER', 'rights', 'ID', true, null, null);
		$this->addForeignKey('ACT_ID', 'actId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('RESTRICTION', 'restriction', 'BOOLEAN', false, null, true);
		$this->addColumn('START_DATE', 'startDate', 'DATE', false, null, null);
		$this->addColumn('END_DATE', 'endDate', 'DATE', false, null, null);
		$this->addColumn('NOTES', 'notes', 'LONGVARCHAR', false, null, null);
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('rights', 'rights', RelationMap::MANY_TO_ONE, array('rights_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('term', 'term', RelationMap::MANY_TO_ONE, array('act_id' => 'id', ), 'SET NULL', null);
	} // buildRelations()

} // RightsGrantedTableMap
