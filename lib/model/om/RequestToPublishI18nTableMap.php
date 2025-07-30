<?php


/**
 * This class defines the structure of the 'rtpback_i18n' table.
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
class RequestToPublishI18nTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.RequestToPublishI18nTableMap';

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
		$this->setName('request_to_publish_i18n');
		$this->setPhpName('requestToPublishI18n');
		$this->setClassname('QubitRequestToPublishI18n');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(false);
		// columns
        $this->addColumn('UNIQUE_IDENTIFIER', 'unique_identifier', 'LONGVARCHAR', false, 1024, null);			
		$this->addColumn('RTP_NAME', 'rtp_name', 'VARCHAR', false, null, null);	
		$this->addColumn('RTP_SURNAME', 'rtp_surname', 'VARCHAR', false, null, null);	
		$this->addColumn('RTP_PHONE', 'rtp_phone', 'VARCHAR', false, null, null);	
		$this->addColumn('RTP_EMAIL', 'rtp_email', 'VARCHAR', false, null, null);	
		$this->addColumn('RTP_INSTITUTION', 'rtp_institution', 'VARCHAR', false, null, null);	
		$this->addColumn('RTP_MOTIVATION', 'rtp_motivation', 'VARCHAR', false, null, null);	
		$this->addColumn('RTP_PLANNED_USE', 'rtp_planned_use', 'LONGVARCHAR', false, null, null);	
		$this->addColumn('RTP_NEED_IMAGE_BY', 'rtp_need_image_by', 'TIMESTAMP', true, null, null);
		$this->addColumn('STATUS_ID', 'statusId', 'INTEGER', true, null, null);
		$this->addForeignPrimaryKey('ID', 'id', 'INTEGER' , 'request_to_publish', 'ID', true, null, null);
		$this->addColumn('OBJECT_ID', 'object_id', 'VARCHAR', false, 20, null);
		$this->addColumn('CREATED_AT', 'createdAt', 'TIMESTAMP', true, null, null);
		$this->addColumn('COMPLETED_AT', 'completedAt', 'TIMESTAMP', true, null, null);
		$this->addPrimaryKey('CULTURE', 'culture', 'VARCHAR', true, 7, null);
		// validators
	} // initialize()

	public function buildRelations()
	{
    $this->addRelation('requestToPublish', 'requestToPublish', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
	} 

} 
