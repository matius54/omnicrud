<?php
    require_once "utils.php";
    session_start();
    $action = URL::decode("action",$_POST);
    switch($action){
        case "update":
            $host = URL::decode("host",$_POST) ?? "localhost";
            $username = URL::decode("username",$_POST) ?? "root";
            $password = URL::decode("password",$_POST) ?? "";
            $_SESSION["connection"] = json_encode([
                "host" => $host,
                "username" => $username,
                "password" => $password
            ]);
        break;
        case "delete":
            unset($_SESSION["connection"]);
        break;
    }
    URL::redirect("./");
?>