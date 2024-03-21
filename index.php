<?php
    require_once "utils.php";
    require_once "db.php";
    if(!isset($_SESSION)) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMNICRUD</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <h1>OMNICRUD</h1>
    <form method="post" action="connection.php">
        <fieldset>
            <legend>Conexion</legend>
            <?= HTML::input(label: "Direccion", name: "host", value: "localhost") ?>
            <?= HTML::input(label: "Usuario", name: "username", value: "root") ?>
            <?= HTML::input(label: "Contraseña", name: "password", value: "") ?>
            <button name="action" value="update">Actualizar datos de conexion</button>
            <?php if(isset($_SESSION["connection"])){ ?>
                <i><?= DB::testDB() ? "La conexion funciona" : "La conexion no funciona" ?></i>
            <?php } ?>
            <button name="action" value="delete">Borrar y restablecer</button>
        </fieldset>
    </form>
    <?php if(isset($_SESSION["connection"])){ ?>
        <form method="post" action="database.php">
            <fieldset>
                <legend>Base de datos</legend>
                <?= HTML::array2list(array: DB::getDB(), title: "Selecciona tu base de datos", name: "database") ?>
                <button name="action" value="select">Seleccionar base de datos</button>
                <button name="action" value="drop">Borrar base de datos seleccionada</button>
                <?= HTML::input(label: "Nombre de nueva base de datos", name: "dname") ?>
                <button name="action" value="new">Crear nueva base de datos</button>
                <?= isset($_SESSION["database"]) ? "Seleccionada la base de datos: ".$_SESSION["database"] : "Base de datos sin seleccionar" ?>
                <button name="action" value="delete">Borrar y restablecer</button>
            </fieldset>
        </form>
    <?php } ?>
    <?php if(isset($_SESSION["connection"]) and isset($_SESSION["database"])){ ?>
        <form method="post" action="database.php">
            <fieldset>
                <legend>Tablas de <?= $_SESSION["database"] ?></legend>
                <ul>
                <?php
                    $db = DB::getInstance();
                    $db->execute("SHOW TABLES");
                    $result = $db->fetchAll(htmlspecialchars: true);
                    if(!$result) echo "<i>no tiene</i>";
                    foreach ($result as $value) {
                        $value = implode($value);
                ?>
                    <li><a href="table.php?name=<?= $value ?>"><?= $value ?></a></li>
                <?php } ?>
                </ul>
            </fieldset>
        </form>
        <form method="post" action="database.php">
            <fieldset>
                <legend>Crear nueva tabla</legend>
                <i>Proximamente</i>
            </fieldset>
        </form>
    <?php } ?>
    <?php
        if(isset($_SESSION["db_error"])){
            echo " <fieldset><legend>Error</legend>".$_SESSION["db_error"]."</fieldset>";
            unset($_SESSION["db_error"]);
        }
    ?>
</body>
</html>