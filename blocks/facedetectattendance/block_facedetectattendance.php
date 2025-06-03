<?php

class block_facedetectattendance extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_facedetectattendance');
    }

    public function get_content() {
        global $OUTPUT, $USER, $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);
        $this->content = new stdClass();
        $this->content->text = '';

        if (has_capability('block/facedetectattendance:managephotos', $context)) {
            // Admin/Teacher View
            $this->content->text .= html_writer::link(
                new moodle_url('/blocks/facedetectattendance/studentlist.php', ['id' => $COURSE->id]),
                get_string('studentlist', 'block_facedetectattendance')
            );

            $this->content->text .= html_writer::empty_tag('br');
            $this->content->text .= html_writer::link(
                new moodle_url('/blocks/facedetectattendance/viewlogs.php', ['id' => $COURSE->id]),
                'View Attendance Logs'
            );
        } else {
            // Student View
            $this->content->text .= html_writer::link(
                new moodle_url('/blocks/facedetectattendance/markattendance.php', ['id' => $COURSE->id]),
                get_string('markattendance', 'block_facedetectattendance')
            );
        }

        return $this->content;
    }
}
