<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Display Error Reporting
    ini_set('display_errors', 1);

    // Level Error Reporting
    ini_set('error_reporting', E_ALL);

    // Level Error Reporting
    error_reporting(E_ALL);

    // Error Handler
    set_error_handler("ErrorHandler");
?>