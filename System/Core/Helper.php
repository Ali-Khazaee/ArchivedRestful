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

    function _Mail($To, $Subject, $Message, $Custom = "")
    {
        $Header  = "MIME-Version: 1.0" . "\r\n";
        $Header .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $Header .= "From: BattleGame <no-reply@battlegame.ir>" . "\r\n"; // @TODO Add new Config file for me
        $Header .= "Reply-To: support@battlegame.ir" . "\r\n";
        $Header .= $Custom;

        mail($To, $Subject, $Message, $Header);
    }
?>