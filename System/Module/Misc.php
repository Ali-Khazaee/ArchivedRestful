<?php
    if (!defined("ROOT")) { exit(); }

    function Crash($App)
    {
        if (!isset($_POST["Crash"]) || empty($_POST["Crash"]))
            JSON(["Message" => 1]);

        $App->DB->Insert('crash', ["Crash" => $_POST["Crash"], "Time" => time()]);

        include(ROOT . "System/Library/PHPMailer/phpmailer.php");
        include(ROOT . "System/Library/PHPMailer/smtp.php");

        $Mail = new PHPMailer;
        $Mail->isSMTP();
        $Mail->Host = 'smtp.gmail.com';
        $Mail->Port = 587;
        $Mail->SMTPSecure = 'tls';
        $Mail->SMTPAuth = true;
        $Mail->Username = "ali.khazaee.mighty@gmail.com";
        $Mail->Password = "vcbhjsxvarbjicwi";
        $Mail->setFrom('Crash@biogram.co', 'Crash Biogram');
        $Mail->addAddress('dev.khazaee@gmail.com', 'Ali Khazaee');
        $Mail->Subject = "Biogram -- Crash";
        $Mail->Body = $_POST["Crash"];
        $Mail->IsHTML(false); 
        $Mail->send();

        JSON(["Message" => 1000]);
    }

    function Update($App)
    {
        JSON(["Message" => 1000, "VersionCode" => 5]);
    }
?>