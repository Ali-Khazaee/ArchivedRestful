<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Load Auth
    include_once(ROOT . "System/Config/Auth.php");

    // Load Core
    include_once(ROOT . "System/Config/Core.php");

    // Load DataBase
    include_once(ROOT . "System/Config/DataBase.php");

    // Load ErrorHandling
    include_once(ROOT . "System/Config/ErrorHandling.php");

    // Load Upload
    include_once(ROOT . "System/Config/Upload.php");
?>