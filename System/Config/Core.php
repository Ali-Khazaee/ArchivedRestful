<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Set TimeZone ( Asia / Tehran )
    date_default_timezone_set("Asia/Tehran");

    // Tracer Location Config
    define("CONFIG_TRACE_DIRECTORY", ROOT . "Storage" . DIRECTORY_SEPARATOR, true);

    // API URL
    define("CONFIG_BASE_ROUTE", '/', true);
?>