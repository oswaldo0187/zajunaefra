<?php
define('AJAX_SCRIPT', true);
/**
 * Endpoint that returns active users belonging to given role IDs.
 * Expects POST param 'roleids' as comma-separated list.
 * Returns JSON: { users: [ 'John Doe', 'Jane Smith' ] }
 */

require_once(__DIR__ . '/../../config.php');

global $DB;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$roleidsraw = required_param('roleids', PARAM_RAW);
$sesskey   = required_param('sesskey', PARAM_RAW);

if (!confirm_sesskey($sesskey)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid sesskey']);
    exit;
}

require_login();

$roleids = array_filter(explode(',', $roleidsraw), function ($r) { return $r !== ''; });

if (empty($roleids)) {
    echo json_encode(['users' => []]);
    exit;
}

$systemcontext = context_system::instance();
list($rolesql, $roleparams) = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'roleid');

// Obtener usuarios y roles
$sql = "SELECT u.id, u.firstname, u.lastname, u.email, r.shortname
          FROM {user} u
          JOIN {role_assignments} ra ON ra.userid = u.id
          JOIN {role} r ON r.id = ra.roleid
         WHERE ra.roleid $rolesql
           AND u.suspended = 0 AND u.deleted = 0";

$records = $DB->get_records_sql($sql, $roleparams);

// Organizar usuarios y roles
$users = [];
foreach ($records as $rec) {
    $uid = $rec->id;
    $fullname = fullname($rec);
    if (!isset($users[$uid])) {
        $users[$uid] = ['name' => $fullname, 'email' => $rec->email, 'roles' => []];
    }
    $users[$uid]['roles'][] = $rec->shortname;
}

$result = [];
foreach ($users as $u) {
    $u['roles'] = array_values(array_unique($u['roles']));
    $result[] = $u;
}

echo json_encode(['users' => $result]); 