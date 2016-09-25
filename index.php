<?php
    // Root Path And Key Access
    define("ROOT", dirname(__FILE__) . DIRECTORY_SEPARATOR, true);

    // Auto Loader
    include_once(ROOT . "System/AutoLoader.php");

    // Run The Application
    $App = new Application();




    $App->testInsert('users', ['name' => 'ahmad']);
    $data = $App->testTable('users');

    foreach ($data as $d) {
        echo $d->name . "\n";
    }


?>