<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'az-sena-dev-psql-moodle-e1-000.postgres.database.azure.com';
$CFG->dbname    = 'zajunadb';
$CFG->dbuser    = 'admpsqlsena';
$CFG->dbpass    = '/76nF6px(&k)Ng_';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

$CFG->wwwroot   = 'http://lms.sena.edu.co/zajuna';
$CFG->dataroot  = '/var/www/zajunadata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');
$CFG->php_memory_limit = '41205M';

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
