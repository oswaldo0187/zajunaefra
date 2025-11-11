<?php
/**
 * Toggle visibility of slider for students
 *
 * @package     local_slider
 * @copyright   2025 Your Name
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// Get parameters
$courseid = required_param('courseid', PARAM_INT);

// Security checks
require_login($courseid);
$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);
require_sesskey();

// Get current visibility state for this course's sliders
$current = $DB->get_field_sql(
    "SELECT visible_to_students 
     FROM {local_slider} 
     WHERE course_state = ? 
     LIMIT 1",
    ['1']
);

// Toggle the visibility (if currently visible '1', make hidden '0', and vice versa)
$newvisibility = ($current === '1') ? '0' : '1';

// Update all slider images for this course
$DB->execute(
    "UPDATE {local_slider} 
     SET visible_to_students = ? 
     WHERE course_state = ?",
    [$newvisibility, '1']
);

// Purge all caches
cache_helper::purge_by_event('changesincourse');
purge_all_caches();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'visible_to_students' => $newvisibility,
    'message' => get_string(($newvisibility === '1') ? 'slidershown' : 'sliderhidden', 'local_slider')
]);
exit;
