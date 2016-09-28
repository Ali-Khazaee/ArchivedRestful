<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Error Handler
    set_error_handler("ErrorHandler");

    // Error Handler Function
    function ErrorHandler($errno, $errstr, $errfile, $errline)
    {
        echo "Line $errline IN $errfile -- $errstr</br>";
        Tracer("error.log", "Line $errline IN $errfile -- $errstr");
    }

    // Tracer
    function Tracer($FileName, $Message)
    {
        file_put_contents(CONFIG_TRACE_DIRECTORY . $FileName, (date("[ Y-m-d H:i:s ] ", microtime(true)) . $Message . "\n"), FILE_APPEND);
    }
?>