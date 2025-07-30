<?php
/**
 * This class defines the structure of the 'requesttopublish' table.
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
class RequestToPublishTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.RequestToPublishTableMap';

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
		$this->setName('request_to_publish');
		$this->setPhpName('requestToPublish');
		$this->setClassname('QubitRequestToPublish');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addForeignKey('PARENT_ID', 'parentId', 'INTEGER', 'request_to_publish', 'ID', false, null, null);
		$this->addColumn('LFT', 'lft', 'INTEGER', true, null, null);
		$this->addColumn('RGT', 'rgt', 'INTEGER', true, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		
	} 

	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('term', 'term', RelationMap::MANY_TO_ONE, array('requestor_id' => 'id', ), 'SET NULL', null);
	$this->addRelation('term', 'term', RelationMap::MANY_TO_ONE, array('dispatcher_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('requesttopublishRelatedByparentId', 'requestToPublish', RelationMap::MANY_TO_ONE, array('parent_id' => 'id', ), null, null);
    $this->addRelation('requesttopublishRelatedByparentId', 'requestToPublish', RelationMap::ONE_TO_MANY, array('id' => 'parent_id', ), null, null);
    $this->addRelation('requesttopublishI18n', 'requesttopublishI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} 
