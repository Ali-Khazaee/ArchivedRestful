<?php

    include "app.php";

    // Run The Application
    $App = new Application();


    $tokens = $App->DB->Find('images', [])->toArray();

    var_dump($tokens);