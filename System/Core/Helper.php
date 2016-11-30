<?php
    if (!defined("ROOT")) { exit(); }

    function ErrorHandler($Type, $Message, $File, $Line)
    {
        Tracer("ApplicationError.log", "Line: $Line File: $File Type: $Type Message: $Message");
        JSON(["Status" => "Failed", "Line" => $Line, "File" => $File, "Message" => $Message, "Type" => $Type]);
    }

    function Tracer($FileName, $Message)
    {
        file_put_contents(CONFIG_TRACE_DIRECTORY . $FileName, (date("[ Y-m-d H:i:s ] ", microtime(true)) . $Message . "\n"), FILE_APPEND);
    }

    function JSON($Message)
    {
        header_remove();
        header('Content-Type: application/json');
        exit(json_encode($Message));
    }
?>