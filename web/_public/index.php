<?php

include '../app/vendor/autoload.php';
$foo = new App\Acme\Foo();
$connection = new App\Acme\Connection();

?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Docker <?php echo $foo->getName(); ?></title>
    </head>
    <body>
        <h1>Docker <?php echo $foo->noName(); ?></h1>
        <h1>MySQL: <?php echo $connection->connect(); ?></h1>
    </body>
</html>
