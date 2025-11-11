<?php
// This file keeps track of upgrades to the local_slider plugin

defined('MOODLE_INTERNAL') || die();

function xmldb_local_slider_upgrade($oldversion = 0) {

    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    // Example version number for when we add course_state. Must be <= plugin->version in version.php.
    // Use a high, unique number to ensure Moodle runs the upgrade step.
    $newversion = 2025101700; // 2025-10-17 (build)

    if ($oldversion < $newversion) {

        // Define table local_slider to be updated.
        $table = new xmldb_table('local_slider');

    // Define field course_state to be added to local_slider.
    // Use TEXT and allow nulls (we will store '1' or '0' strings).
    $field = new xmldb_field('course_state', XMLDB_TYPE_TEXT, null, null, null, null, null, 'state');

        // Conditionally launch add field course_state.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    // Slider savepoint reached.
    upgrade_plugin_savepoint(true, $newversion, 'local', 'slider');
    }

    // Add visible_to_students field for controlling student visibility.
    $newversion = 2025110701;

    if ($oldversion < $newversion) {
        // Define table local_slider to be updated.
        $table = new xmldb_table('local_slider');

        // Define field visible_to_students to be added to local_slider.
        // Use TEXT to store '1' (visible) or '0' (hidden). Default '1'.
        $field = new xmldb_field('visible_to_students', XMLDB_TYPE_TEXT, null, null, null, null, '1', 'course_state');

        // Conditionally launch add field visible_to_students.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Set all existing records to visible by default.
        $DB->execute("UPDATE {local_slider} SET visible_to_students = ? WHERE visible_to_students IS NULL OR visible_to_students = ?", ['1', '']);

        // Slider savepoint reached.
        upgrade_plugin_savepoint(true, $newversion, 'local', 'slider');
    }

    return true;
}
