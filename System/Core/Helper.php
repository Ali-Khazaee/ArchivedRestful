<?php
    if (!defined("ROOT")) { exit(); }

    function ErrorHandler($errno, $errstr, $errfile, $errline)
    {
        echo "Line $errline IN $errfile -- $errstr</br>";
        Tracer("ApplicationError.log", "Line $errline IN $errfile -- $errstr");
    }

    function Tracer($FileName, $Message)
    {
        file_put_contents(CONFIG_TRACE_DIRECTORY . $FileName, (date("[ Y-m-d H:i:s ] ", microtime(true)) . $Message . "\n"), FILE_APPEND);
    }

    function JSON($Message, $Code = 200)
    {
        header_remove();

        http_response_code($Code);

        header('Content-Type: application/json');

        exit(json_encode($Message));
    }

    function _Mail($To, $Subject, $Message, $Custom = null)
    {
        $Header  = "MIME-Version: 1.0" . "\r\n";
        $Header .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $Header .= "From: BattleGame <no-reply@battlegame.ir>" . "\r\n"; // @TODO Add new Config file for me
        $Header .= "Reply-To: support@battlegame.ir" . "\r\n";
        $Header .= $Custom;

        mail($To, $Subject, $Message, $Header);
    }
?>