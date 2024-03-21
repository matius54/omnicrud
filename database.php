<?php
    require_once "utils.php";
    require_once "db.php";
    session_start();
    $action = URL::decode("action",$_POST);
    switch($action){
        case "new":
            $database = URL::decode("dname",$_POST);
            $database = strtolower($database);
            if($database){
                DB::createDB($database);
                $_SESSION["database"] = $database;
            }
        break;
        case "select":
            $database = URL::decode("database",$_POST);
            if($database) $_SESSION["database"] = $database;
        break;
        case "drop":
            $database = URL::decode("database",$_POST);
            if($database){
                DB::dropDB($database);
                if($database === $_SESSION["database"]) unset($_SESSION["database"]);
            }
        break;
        case "delete":
            unset($_SESSION["database"]);
        break;
    }
    URL::redirect("./");
?>