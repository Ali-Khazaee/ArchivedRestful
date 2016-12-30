<?php

    include "app.php";

    // Run The Application
    $App = new Application();


        $token_data = $App->Auth->GetToken();
    print_r("Token Data is (UserId) : \n");
    var_dump($token_data->UserId);