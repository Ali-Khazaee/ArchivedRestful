<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Error Handler Function
    function ErrorHandler($errno, $errstr, $errfile, $errline)
    {
        echo "Line $errline IN $errfile -- $errstr</br>";
        Tracer("ApplicationError.log", "Line $errline IN $errfile -- $errstr");
    }

    // Tracer
    function Tracer($FileName, $Message)
    {
        file_put_contents(CONFIG_TRACE_DIRECTORY . $FileName, (date("[ Y-m-d H:i:s ] ", microtime(true)) . $Message . "\n"), FILE_APPEND);
    }

    // JSON Response
    function JSON($Message, $Code = 200)
    {
        // Clear Headers
        header_remove();

        // Set HTTP Code
        http_response_code($Code);

        // Set Content Type
        header('Content-Type: application/json');

        // Return The Encoded JSON
        exit(json_encode($Message));
    }

    function _Mail($To, $Subject, $Message, $Custom = null){

        $Header  = "MIME-Version: 1.0" . "\r\n";
        $Header .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $Header .= "From: BattleGame <no-reply@battlegame.ir>" . "\r\n";
        $Header .= "Reply-To: support@battlegame.ir" . "\r\n";
        $Header .= $Custom;

        mail($To, $Subject, $Message, $Header);
    }
?>