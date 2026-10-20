<?php
session_start();
session_destroy();
header("Location: index.php");
exit;
?>

<--no recuerdo bien que hace esto pero creo que es para cerrar sesion y redirigir al index.php, lo deje por si acaso -->