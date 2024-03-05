
<?php
// MYSQL DB hostname
define('SQL_SERVER_HOSTNAME', 'percona');

// MYSQL DB port
define('SQL_SERVER_PORT', 3306);

// MYSQL DB database
define('SQL_SERVER_DB', 'atom');

// MYSQL DB username
define('SQL_SERVER_USER', 'atom');

// MYSQL DB password
define('SQL_SERVER_PASSWORD', 'atom_12345');

// Domain name of the Solr server
define('SOLR_SERVER_HOSTNAME', 'solr1');

// Whether or not to run in secure mode
define('SOLR_SECURE', false);

// HTTP Port to connection
define('SOLR_SERVER_PORT', ((SOLR_SECURE) ? 8443 : 8983));

// HTTP Basic Authentication Username
define('SOLR_SERVER_USERNAME', 'solr');

// HTTP Basic Authentication password
define('SOLR_SERVER_PASSWORD', '');

// Solr collection name
define('SOLR_COLLECTION', 'atom');

// HTTP connection timeout
// This is maximum time in seconds allowed for the http data transfer operation. Default value is 30 seconds
define('SOLR_SERVER_TIMEOUT', 10);

// File name to a PEM-formatted private key + private certificate (concatenated in that order)
define('SOLR_SSL_CERT', 'certs/combo.pem');

// File name to a PEM-formatted private certificate only
define('SOLR_SSL_CERT_ONLY', 'certs/solr.crt');

// File name to a PEM-formatted private key
define('SOLR_SSL_KEY', 'certs/solr.key');

// Password for PEM-formatted private key file
define('SOLR_SSL_KEYPASSWORD', 'StrongAndSecurePassword');

// Name of file holding one or more CA certificates to verify peer with
define('SOLR_SSL_CAINFO', 'certs/cacert.crt');

// Name of directory holding multiple CA certificates to verify peer with
define('SOLR_SSL_CAPATH', 'certs/');

?>
