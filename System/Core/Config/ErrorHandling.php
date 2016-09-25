<?php

    // Display Error Reporting
    ini_set('display_errors', 1);

    // Level Error Reporting
    ini_set('error_reporting', E_ALL);

    // Level Error Reporting
    error_reporting(E_ALL);


    // Error Handler
    set_error_handler("ErrorHandler");

    function ErrorHandler($errno, $errstr, $errfile, $errline)
    {
        echo "Line $errline IN $errfile -- $errstr</br>";
        Tracer("error.log", "Line $errline IN $errfile -- $errstr");
    }

    // Trace Config
    define("CONFIG_TRACE_DIRECTORY", ROOT . "Storage" . DIRECTORY_SEPARATOR, true);

    // Tracer
    function Tracer($FileName, $Message)
    {
        file_put_contents(CONFIG_TRACE_DIRECTORY . $FileName, (date("[ Y-m-d H:i:s ] ", microtime(true)) . $Message . "\n"), FILE_APPEND);
    }