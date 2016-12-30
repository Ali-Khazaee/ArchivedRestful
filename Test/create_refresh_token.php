<?php

    include "app.php";

    // Run The Application
    $App = new Application();


    $token_data = $App->Auth->CreateRefreshToken(['a' => 'b']);
    print_r("Token Refresh is  : \n");
    var_dump($token_data);