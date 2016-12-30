<?php

    include "app.php";

    // Run The Application
    $App = new Application();


    $data = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID('57f5fe4b9a89200551596d81')])->toArray();


    print_r(" User : \n");
    var_dump($data[0]);