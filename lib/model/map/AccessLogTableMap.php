<?php


/**
 * This class defines the structure of the 'access_log' table.
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
class AccessLogTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.AccessLogTableMap';

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
		$this->setName('access_log');
		$this->setPhpName('accessLog');
		$this->setClassname('QubitAccessLog');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addForeignKey('OBJECT_ID', 'objectId', 'INTEGER', 'object', 'ID', true, null, null);
		$this->addColumn('ACCESS_DATE', 'accessDate', 'TIMESTAMP', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('object_id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // AccessLogTableMap
