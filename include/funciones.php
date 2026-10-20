<?php
function incluirTemplates(string $nombre, string $case = 'a', bool $inicio = false) {
    $opcion = $case; 
    include __DIR__ . "/header/{$nombre}.php";
}
?>