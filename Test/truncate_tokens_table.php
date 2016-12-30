<?php

    include "app.php";

    // Run The Application
    $App = new Application();


    $App->DB->Delete('tokens' , []);
