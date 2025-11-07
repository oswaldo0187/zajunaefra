<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'zajuna';
$CFG->dbuser    = 'postgres';
$CFG->dbpass    = '12345';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '5432',
  'dbsocket' => '',
);

$CFG->wwwroot   = 'http://localhost/zajuna';
$CFG->dataroot  = 'C:\wamp64\www\zajunadata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;
//$CFG->php_memory_limit = '41205M';
require_once(__DIR__ . '/lib/setup.php');


//$CFG->php_memory_limit = '41205M';
// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
