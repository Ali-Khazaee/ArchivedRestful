<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Load Config
    include_once(ROOT . "System/Core/Config/Config.php");

    // Load Application
    include_once(ROOT . "System/Core/Application.php");

    // Load Module Account
    include_once(ROOT . "System/Module/Account.php");

    // Load Database Driver
    include_once(ROOT . "System/Core/Database.php");
?>