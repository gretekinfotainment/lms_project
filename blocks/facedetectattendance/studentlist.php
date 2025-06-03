<?php
require('../../config.php');
require_login();

$courseid = required_param('id', PARAM_INT);
$context = context_course::instance($courseid);
require_capability('block/facedetectattendance:managephotos', $context);

$PAGE->set_url(new moodle_url('/blocks/facedetectattendance/studentlist.php', ['id' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title('Student List - Upload Photos');

echo $OUTPUT->header();
echo $OUTPUT->heading('Student List - Upload Photos');

$uploadDir = __DIR__ . '/student_photos/';
$webPath = new moodle_url('/blocks/facedetectattendance/student_photos/');

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userid']) && isset($_FILES['studentphoto'])) {
    $userid = (int)$_POST['userid'];
    $file = $_FILES['studentphoto'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $uploadDir . $userid . '.' . $ext;

        // Remove any previous photos with different extensions
        foreach (glob($uploadDir . $userid . '.*') as $oldfile) {
            unlink($oldfile);
        }

        move_uploaded_file($file['tmp_name'], $filename);
        echo $OUTPUT->notification("Photo uploaded for user ID: $userid", 'notifysuccess');
    } else {
        echo $OUTPUT->notification("Error uploading photo for user ID: $userid", 'notifyproblem');
    }
}

// Fetch students enrolled in the course
$students = get_enrolled_users($context, 'mod/assign:view', 0, 'u.id, u.firstname, u.lastname');

if (empty($students)) {
    echo $OUTPUT->notification('No students found in this course.', 'notifyproblem');
} else {
    echo html_writer::start_tag('table', ['class' => 'generaltable']);
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Photo');
    echo html_writer::tag('th', 'Student Name');
    echo html_writer::tag('th', 'Upload New Photo');
    echo html_writer::end_tag('tr');

    foreach ($students as $student) {
        echo html_writer::start_tag('tr');

        // PHOTO COLUMN
        $photoFile = '';
        foreach (['jpg', 'jpeg', 'png'] as $ext) {
            $checkPath = $uploadDir . $student->id . '.' . $ext;
            if (file_exists($checkPath)) {
                $photoFile = $webPath . $student->id . '.' . $ext;
                break;
            }
        }

        if ($photoFile) {
            echo html_writer::tag('td', '<img src="' . $photoFile . '" width="60" height="60" style="object-fit:cover;border-radius:4px;">');
        } else {
            echo html_writer::tag('td', 'No Photo');
        }

        // STUDENT NAME
        echo html_writer::tag('td', fullname($student));

        // UPLOAD FORM
        echo html_writer::start_tag('td');
        echo '<form method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="userid" value="' . $student->id . '">';
        echo '<input type="file" name="studentphoto" accept="image/*" required>';
        echo '<input type="submit" value="Upload">';
        echo '</form>';
        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('table');
}

echo $OUTPUT->footer();
