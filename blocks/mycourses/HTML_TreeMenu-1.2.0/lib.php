<?php
function marker($courseid, $userid=0) {
    global $USER;

    if ($userid == 0) {
        $userid = $USER->id;
    }

    return ((isadmin($userid)) ||
            (get_record('course_marker_FN', 'courseid', $courseid, 'userid', $userid)));
}
?>