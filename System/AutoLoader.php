<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Load Config
    include_once(ROOT . "System/Config/Config.php");

    // Load Application
    include_once(ROOT . "System/Core/Application.php");

    // Load Authentication
    include_once(ROOT . "System/Core/Authentication.php");

    // Load DataBase Driver
    include_once(ROOT . "System/Core/DataBase.php");

    // Load Helper Function
    include_once(ROOT . "System/Core/Helper.php");

    // Load Router
    include_once(ROOT . "System/Core/Router.php");

    // Load Module Account
    include_once(ROOT . "System/Module/Account.php");
?>