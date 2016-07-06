<?php


/**
 * This class defines the structure of the 'premis_object' table.
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
class PremisObjectTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.PremisObjectTableMap';

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
		$this->setName('premis_object');
		$this->setPhpName('premisObject');
		$this->setClassname('QubitPremisObject');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addForeignKey('INFORMATION_OBJECT_ID', 'informationObjectId', 'INTEGER', 'information_object', 'ID', false, null, null);
		$this->addColumn('PUID', 'puid', 'VARCHAR', false, 255, null);
		$this->addColumn('FILENAME', 'filename', 'VARCHAR', false, 1024, null);
		$this->addColumn('LAST_MODIFIED', 'lastModified', 'TIMESTAMP', false, null, null);
		$this->addColumn('DATE_INGESTED', 'dateIngested', 'DATE', false, null, null);
		$this->addColumn('SIZE', 'size', 'INTEGER', false, null, null);
		$this->addColumn('MIME_TYPE', 'mimeType', 'VARCHAR', false, 255, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('informationObject', 'informationObject', RelationMap::MANY_TO_ONE, array('information_object_id' => 'id', ), null, null);
	} // buildRelations()

} // PremisObjectTableMap
