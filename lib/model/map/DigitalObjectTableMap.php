<?php


/**
 * This class defines the structure of the 'digital_object' table.
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
class DigitalObjectTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.DigitalObjectTableMap';

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
		$this->setName('digital_object');
		$this->setPhpName('digitalObject');
		$this->setClassname('QubitDigitalObject');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addForeignKey('INFORMATION_OBJECT_ID', 'informationObjectId', 'INTEGER', 'information_object', 'ID', false, null, null);
		$this->addForeignKey('USAGE_ID', 'usageId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('MIME_TYPE', 'mimeType', 'VARCHAR', false, 255, null);
		$this->addForeignKey('MEDIA_TYPE_ID', 'mediaTypeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('NAME', 'name', 'VARCHAR', true, 1024, null);
		$this->addColumn('PATH', 'path', 'VARCHAR', true, 1024, null);
		$this->addColumn('SEQUENCE', 'sequence', 'INTEGER', false, null, null);
		$this->addColumn('BYTE_SIZE', 'byteSize', 'INTEGER', false, null, null);
		$this->addColumn('CHECKSUM', 'checksum', 'VARCHAR', false, 255, null);
		$this->addColumn('CHECKSUM_TYPE', 'checksumType', 'VARCHAR', false, 50, null);
		$this->addForeignKey('PARENT_ID', 'parentId', 'INTEGER', 'digital_object', 'ID', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('informationObject', 'informationObject', RelationMap::MANY_TO_ONE, array('information_object_id' => 'id', ), null, null);
    $this->addRelation('termRelatedByusageId', 'term', RelationMap::MANY_TO_ONE, array('usage_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBymediaTypeId', 'term', RelationMap::MANY_TO_ONE, array('media_type_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('digitalObjectRelatedByparentId', 'digitalObject', RelationMap::MANY_TO_ONE, array('parent_id' => 'id', ), null, null);
    $this->addRelation('digitalObjectRelatedByparentId', 'digitalObject', RelationMap::ONE_TO_MANY, array('id' => 'parent_id', ), null, null);
	} // buildRelations()

} // DigitalObjectTableMap
