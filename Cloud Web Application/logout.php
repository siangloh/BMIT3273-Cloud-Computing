<?php
require_once "_base.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();
alert_msg("Log out successful. ", '/admin/admin_login.php');
