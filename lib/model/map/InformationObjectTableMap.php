<?php


/**
 * This class defines the structure of the 'information_object' table.
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
class InformationObjectTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.InformationObjectTableMap';

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
		$this->setName('information_object');
		$this->setPhpName('informationObject');
		$this->setClassname('QubitInformationObject');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addColumn('IDENTIFIER', 'identifier', 'VARCHAR', false, 1024, null);
		$this->addColumn('OAI_LOCAL_IDENTIFIER', 'oaiLocalIdentifier', 'INTEGER', true, null, null);
		$this->addForeignKey('LEVEL_OF_DESCRIPTION_ID', 'levelOfDescriptionId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('COLLECTION_TYPE_ID', 'collectionTypeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('REPOSITORY_ID', 'repositoryId', 'INTEGER', 'repository', 'ID', false, null, null);
		$this->addForeignKey('PARENT_ID', 'parentId', 'INTEGER', 'information_object', 'ID', false, null, null);
		$this->addForeignKey('DESCRIPTION_STATUS_ID', 'descriptionStatusId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('DESCRIPTION_DETAIL_ID', 'descriptionDetailId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('DESCRIPTION_IDENTIFIER', 'descriptionIdentifier', 'VARCHAR', false, 1024, null);
		$this->addColumn('SOURCE_STANDARD', 'sourceStandard', 'VARCHAR', false, 1024, null);
		$this->addForeignKey('DISPLAY_STANDARD_ID', 'displayStandardId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('LFT', 'lft', 'INTEGER', true, null, null);
		$this->addColumn('RGT', 'rgt', 'INTEGER', true, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('termRelatedBylevelOfDescriptionId', 'term', RelationMap::MANY_TO_ONE, array('level_of_description_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBycollectionTypeId', 'term', RelationMap::MANY_TO_ONE, array('collection_type_id' => 'id', ), null, null);
    $this->addRelation('repository', 'repository', RelationMap::MANY_TO_ONE, array('repository_id' => 'id', ), null, null);
    $this->addRelation('informationObjectRelatedByparentId', 'informationObject', RelationMap::MANY_TO_ONE, array('parent_id' => 'id', ), null, null);
    $this->addRelation('termRelatedBydescriptionStatusId', 'term', RelationMap::MANY_TO_ONE, array('description_status_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBydescriptionDetailId', 'term', RelationMap::MANY_TO_ONE, array('description_detail_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBydisplayStandardId', 'term', RelationMap::MANY_TO_ONE, array('display_standard_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('digitalObject', 'digitalObject', RelationMap::ONE_TO_MANY, array('id' => 'information_object_id', ), null, null);
    $this->addRelation('informationObjectRelatedByparentId', 'informationObject', RelationMap::ONE_TO_MANY, array('id' => 'parent_id', ), null, null);
    $this->addRelation('informationObjectI18n', 'informationObjectI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('premisObject', 'premisObject', RelationMap::ONE_TO_MANY, array('id' => 'information_object_id', ), null, null);
	} // buildRelations()

} // InformationObjectTableMap
