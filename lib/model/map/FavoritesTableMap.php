<?php


/**
 * This class defines the structure of the 'favorites' table.
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
class FavoritesTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.FavoritesTableMap';

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
		$this->setName('favorites');
		$this->setPhpName('favorites');
		$this->setClassname('QubitFavorites');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addColumn('ARCHIVAL_DESCRIPTION_ID', 'archivalDescriptionId', 'VARCHAR', false, null, null);	
		$this->addColumn('ARCHIVAL_DESCRIPTION', 'archivalDescription', 'VARCHAR', false, null, null);	
		$this->addColumn('SLUG', 'slug', 'VARCHAR', false, null, null);	
		$this->addColumn('USER_ID', 'userId', 'VARCHAR', false, null, null);	
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('COMPLETED_AT', 'completedAt', 'TIMESTAMP', true, null, null);

	} 

	
	public function buildRelations()
	{
	} // buildRelations()

} 
