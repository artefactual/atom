<?php


/**
 * This class defines the structure of the 'repository' table.
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
class RepositoryTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.RepositoryTableMap';

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
		$this->setName('repository');
		$this->setPhpName('repository');
		$this->setClassname('QubitRepository');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'actor', 'ID', true, null, null);
		$this->addColumn('IDENTIFIER', 'identifier', 'VARCHAR', false, 1024, null);
		$this->addForeignKey('DESC_STATUS_ID', 'descStatusId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('DESC_DETAIL_ID', 'descDetailId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('DESC_IDENTIFIER', 'descIdentifier', 'VARCHAR', false, 1024, null);
		$this->addColumn('UPLOAD_LIMIT', 'uploadLimit', 'FLOAT', false, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('actor', 'actor', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('termRelatedBydescStatusId', 'term', RelationMap::MANY_TO_ONE, array('desc_status_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedBydescDetailId', 'term', RelationMap::MANY_TO_ONE, array('desc_detail_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('informationObject', 'informationObject', RelationMap::ONE_TO_MANY, array('id' => 'repository_id', ), null, null);
    $this->addRelation('repositoryI18n', 'repositoryI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // RepositoryTableMap
