<?php
require_once '../_base.php';

if (is_post()) {
    // multiple remove
    $ids = req('checkboxItem', []);
    if (!is_array($ids)) $ids = [$ids];

    if (sizeof($ids) < 1) {
        alert_msg("No item selected. ", 'student_list.php');
    }

    $num = 0;

    if (isset($_POST['btn-delete'])) {
        $updateValue = 0;
        $action = 'deleted';

        foreach ($ids as $id) {
            $stm = $_db->prepare("DELETE FROM student WHERE studid = ?");
            $stm->execute([$id]);
            if ($stm->rowCount() > 0) {
                $num++;
            }
        }
    } else {
        alert_msg('Invalid action.', $_SERVER['HTTP_REFERER']);
    }
    
    if ($num > 0) {
        alert_msg("$num record(s) has been $action. ", $_SERVER['HTTP_REFERER']);
    } else {
        alert_msg("No changes made. ", $_SERVER['HTTP_REFERER']);
    }
}
