<?php 
    require_once "utils.php";
    if(!isset($_GET["name"])) URL::redirect("./");
    require_once "db.php";
    if(!isset($_SESSION)) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <a href="./">Volver</a>
    <?php
        $name = URL::decode("name",$_GET);
        $conn = DB::getInstance();
        $conn->execute("SELECT * FROM $name");
        $result = $conn->fetchAll();
        if($result) echo HTML::matrix2table($result,array_keys($result[0]));
        $conn->execute("DESCRIBE $name");
        $result = $conn->fetchAll();
        //svar_dump($result);
        echo "<fieldset><legend>Nuevo registro (no funciona aun)</legend>";
        foreach ($result as $value) {
            $type = "text";
            echo HTML::input(label: $value["Field"], type: $type);
        }
        echo "<button>Registrar</button></fieldset>";
    ?>
</body>
</html>