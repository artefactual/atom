<?php


/**
 * This class defines the structure of the 'clipboard_save_item' table.
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
class ClipboardSaveItemTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.ClipboardSaveItemTableMap';

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
		$this->setName('clipboard_save_item');
		$this->setPhpName('clipboardSaveItem');
		$this->setClassname('QubitClipboardSaveItem');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addForeignKey('SAVE_ID', 'saveId', 'INTEGER', 'clipboard_save', 'ID', false, null, null);
		$this->addColumn('ITEM_CLASS_NAME', 'itemClassName', 'VARCHAR', false, 255, null);
		$this->addColumn('SLUG', 'slug', 'VARCHAR', false, 255, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('clipboardSave', 'clipboardSave', RelationMap::MANY_TO_ONE, array('save_id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // ClipboardSaveItemTableMap
