<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Load Auth Config
    include_once(ROOT . "System/Config/Auth.php");

    // Load Core Config
    include_once(ROOT . "System/Config/Core.php");

    // Load DataBase Config
    include_once(ROOT . "System/Config/DataBase.php");

    // Load ErrorHandling Config
    include_once(ROOT . "System/Config/ErrorHandling.php");
?>