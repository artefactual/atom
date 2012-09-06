<?php


/**
 * This class defines the structure of the 'contact_information' table.
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
class ContactInformationTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.ContactInformationTableMap';

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
		$this->setName('contact_information');
		$this->setPhpName('contactInformation');
		$this->setClassname('QubitContactInformation');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addForeignKey('ACTOR_ID', 'actorId', 'INTEGER', 'actor', 'ID', true, null, null);
		$this->addColumn('PRIMARY_CONTACT', 'primaryContact', 'BOOLEAN', false, null, null);
		$this->addColumn('CONTACT_PERSON', 'contactPerson', 'VARCHAR', false, 1024, null);
		$this->addColumn('STREET_ADDRESS', 'streetAddress', 'LONGVARCHAR', false, null, null);
		$this->addColumn('WEBSITE', 'website', 'VARCHAR', false, 1024, null);
		$this->addColumn('EMAIL', 'email', 'VARCHAR', false, 255, null);
		$this->addColumn('TELEPHONE', 'telephone', 'VARCHAR', false, 255, null);
		$this->addColumn('FAX', 'fax', 'VARCHAR', false, 255, null);
		$this->addColumn('POSTAL_CODE', 'postalCode', 'VARCHAR', false, 255, null);
		$this->addColumn('COUNTRY_CODE', 'countryCode', 'VARCHAR', false, 255, null);
		$this->addColumn('LONGITUDE', 'longitude', 'FLOAT', false, null, null);
		$this->addColumn('LATITUDE', 'latitude', 'FLOAT', false, null, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('UPDATED_AT', 'updatedAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('SOURCE_CULTURE', 'sourceCulture', 'VARCHAR', true, 7, null);
		$this->addPrimaryKey('ID', 'id', 'INTEGER', true, null, null);
		$this->addColumn('SERIAL_NUMBER', 'serialNumber', 'INTEGER', true, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('actor', 'actor', RelationMap::MANY_TO_ONE, array('actor_id' => 'id', ), 'CASCADE', null);
    $this->addRelation('contactInformationI18n', 'contactInformationI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // ContactInformationTableMap
