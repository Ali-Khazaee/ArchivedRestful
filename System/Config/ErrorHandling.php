<?php
    if (!defined("ROOT")) { exit(); }

    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ALL);

    set_error_handler("ErrorHandler");
?>