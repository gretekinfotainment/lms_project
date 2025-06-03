<?php
require('../../config.php');
require_login();

$courseid = required_param('id', PARAM_INT);
$context = context_course::instance($courseid);

// Temporarily disable capability check to rule it out
// require_capability('block/facedetectattendance:managephotos', $context);

$PAGE->set_url(new moodle_url('/blocks/facedetectattendance/viewlogs.php', ['id' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title('Attendance Logs');

echo $OUTPUT->header();
echo $OUTPUT->heading('Student Attendance Logs');

try {
    $records = $DB->get_records('block_facedetectattendance', ['courseid' => $courseid], 'timestamp DESC');
} catch (dml_exception $e) {
    echo $OUTPUT->notification('Database error: ' . $e->getMessage(), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}

if ($records) {
    echo html_writer::start_tag('table', ['class' => 'generaltable']);
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Student');
    echo html_writer::tag('th', 'Timestamp');
    echo html_writer::end_tag('tr');

    foreach ($records as $rec) {
        $user = $DB->get_record('user', ['id' => $rec->userid], '*', MUST_EXIST);
        $name = fullname($user);
        $time = date('Y-m-d H:i:s', $rec->timestamp);

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', $name);
        echo html_writer::tag('td', $time);
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('table');
} else {
    echo $OUTPUT->notification('No attendance records found.', 'notifyproblem');
}

echo $OUTPUT->footer();
