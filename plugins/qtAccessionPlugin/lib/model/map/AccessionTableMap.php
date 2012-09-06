<?php


/**
 * This class defines the structure of the 'accession' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    plugins.qtAccessionPlugin.lib.model.map
 */
class AccessionTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'plugins.qtAccessionPlugin.lib.model.map.AccessionTableMap';

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
		$this->setName('accession');
		$this->setPhpName('accession');
		$this->setClassname('QubitAccession');
		$this->setPackage('plugins.qtAccessionPlugin.lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'object', 'ID', true, null, null);
		$this->addForeignKey('ACQUISITION_TYPE_ID', 'acquisitionTypeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('DATE', 'date', 'DATE', false, null, null);
		$this->addColumn('IDENTIFIER', 'identifier', 'VARCHAR', false, 255, null);
		$this->addForeignKey('PROCESSING_PRIORITY_ID', 'processingPriorityId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('PROCESSING_STATUS_ID', 'processingStatusId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addForeignKey('RESOURCE_TYPE_ID', 'resourceTypeId', 'INTEGER', 'term', 'ID', false, null, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('UPDATED_AT', 'updatedAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('object', 'object', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('termRelatedByacquisitionTypeId', 'term', RelationMap::MANY_TO_ONE, array('acquisition_type_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedByprocessingPriorityId', 'term', RelationMap::MANY_TO_ONE, array('processing_priority_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedByprocessingStatusId', 'term', RelationMap::MANY_TO_ONE, array('processing_status_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('termRelatedByresourceTypeId', 'term', RelationMap::MANY_TO_ONE, array('resource_type_id' => 'id', ), 'SET NULL', null);
    $this->addRelation('accessionI18n', 'accessionI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
    $this->addRelation('deaccession', 'deaccession', RelationMap::ONE_TO_MANY, array('id' => 'accession_id', ), 'CASCADE', null);
	} // buildRelations()

} // AccessionTableMap
