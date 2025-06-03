<?php
require('../../config.php');
require_login();

$courseid = required_param('id', PARAM_INT);
$context = context_course::instance($courseid);
require_capability('block/facedetectattendance:view', $context);

$PAGE->set_url(new moodle_url('/blocks/facedetectattendance/markattendance.php', ['id' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title('Mark Attendance');

echo $OUTPUT->header();
echo $OUTPUT->heading('Mark Attendance - Face Detection');
?>

<!-- Webcam.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>

<!-- Camera Preview -->
<div id="my_camera" style="width:320px; height:240px;"></div>
<br>
<form method="post">
    <input type="hidden" name="photoData" id="photoData">
    <button type="button" onclick="capturePhoto()">📸 Capture & Verify</button>
</form>

<br>
<div id="result" style="font-weight: bold;"></div>

<script>
    Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    Webcam.attach('#my_camera');

    function capturePhoto() {
        Webcam.snap(function (data_uri) {
            document.getElementById('photoData').value = data_uri;
            document.forms[0].submit(); // Submit to PHP (not Flask)
        });
    }
</script>

<?php
// Handle form submission and communicate with Flask
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['photoData'])) {
    $userid = $USER->id;
    $image = $_POST['photoData'];

    $data = json_encode([
        'userid' => $userid,
        'image' => $image
    ]);

    $ch = curl_init('http://127.0.0.1:5000/verify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    echo '<pre>Raw Flask Response: ';
    print_r($response);
    echo '</pre>';

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo $OUTPUT->notification('Error contacting Flask server: ' . $error, 'notifyproblem');
    } else {
        $result = json_decode($response, true);
        if ($result && isset($result['status'])) {
            if ($result['status'] === 'match') {
                global $DB;
                $record = new stdClass();
                $record->userid = $USER->id;
                $record->courseid = $courseid;
                $record->timestamp = time();

                $DB->insert_record('block_facedetectattendance', $record);

                echo $OUTPUT->notification('Face matched! Attendance marked.', 'notifysuccess');
            } elseif ($result['status'] === 'nomatch') {
                echo $OUTPUT->notification('Face not recognized. Try again.', 'notifyproblem');
            } else {
                echo $OUTPUT->notification('Error: ' . $result['message'], 'notifyproblem');
            }
        } else {
            echo $OUTPUT->notification('Invalid response from Flask API.', 'notifyproblem');
        }
    }
}

echo $OUTPUT->footer();
?>
