<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Load Config
    include_once(ROOT . "System/Config/Config.php");

    // Load Anonymous Function
    include_once(ROOT . "System/Core/Anonymous.php");

    // Load Anonymous Function
    include_once(ROOT . "System/Core/Helpers.php");

    // Load DataBase Driver
    include_once(ROOT . "System/Core/DataBase.php");

    // Load DataBase Driver
    include_once(ROOT . "System/Core/Token.php");

    // Load DataBase Driver
    include_once(ROOT . "System/Core/Router.php");

    // Load Application
    include_once(ROOT . "System/Core/Application.php");

    // Load Module Account
    include_once(ROOT . "System/Module/Account.php");
?>