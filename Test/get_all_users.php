<?php

    include "app.php";

    // Run The Application
    $App = new Application();


    $data = $App->DB->Find('account', [])->toArray();
    print_r("All Users : \n");
    var_dump($data);