<?php


/**
 * This class defines the structure of the 'function' table.
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
class FunctionTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.FunctionTableMap';

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
		$this->setName('function');
		$this->setPhpName('function');
		$this->setClassname('QubitFunction');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addForeignKey('TYPE_ID', 'typeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('PARENT_ID', 'parentId', 'INTEGER', 'function', 'ID', false, null, null);
		$this->addForeignKey('DESCRIPTION_STATUS_ID', 'descriptionStatusId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('DESCRIPTION_DETAIL_ID', 'descriptionDetailId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('DESCRIPTION_IDENTIFIER', 'descriptionIdentifier', 'VARCHAR', false, 1024, null);
		$this->addColumn('SOURCE_STANDARD', 'sourceStandard', 'VARCHAR', false, 1024, null);
		$this->addColumn('LFT', 'lft', 'INTEGER', false, null, null);
		$this->addColumn('RGT', 'rgt', 'INTEGER', false, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('termRelatedBytypeId', 'term', RelationMap::MANY_TO_ONE, array('type_id' => 'id', ), null, null);
    $this->addRelation('functionRelatedByparentId', 'function', RelationMap::MANY_TO_ONE, array('parent_id' => 'id', ), null, null);
    $this->addRelation('termRelatedBydescriptionStatusId', 'term', RelationMap::MANY_TO_ONE, array('description_status_id' => 'id', ), null, null);
    $this->addRelation('termRelatedBydescriptionDetailId', 'term', RelationMap::MANY_TO_ONE, array('description_detail_id' => 'id', ), null, null);
    $this->addRelation('functionRelatedByparentId', 'function', RelationMap::ONE_TO_MANY, array('id' => 'parent_id', ), null, null);
    $this->addRelation('functionI18n', 'functionI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // FunctionTableMap
