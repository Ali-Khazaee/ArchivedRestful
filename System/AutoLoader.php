<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Load Helper
    include_once(ROOT . "System/Core/Helper.php");

    // Load Config
    include_once(ROOT . "System/Config/Config.php");

    // Load Application
    include_once(ROOT . "System/Core/Application.php");

    // Load Authentication
    include_once(ROOT . "System/Core/Authentication.php");

    // Load DataBase
    include_once(ROOT . "System/Core/DataBase.php");

    // Load Language
    include_once(ROOT . "System/Core/Language.php");

    // Load Router
    include_once(ROOT . "System/Core/Router.php");

    // Load Upload
    include_once(ROOT . "System/Core/Upload.php");

    // Load Upload
    include_once(ROOT . "System/Core/RateLimit.php");

    // Load Upload
    include_once(ROOT . "System/Core/Log.php");

    // Load Account
    include_once(ROOT . "System/Module/Account.php");
?>