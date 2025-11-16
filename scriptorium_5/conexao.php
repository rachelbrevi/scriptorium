<?php
$host = "localhost";
$user = "root";       
$pass = "";           
$db   = "bd_scriptorium";  

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Erro ao conectar: " . mysqli_connect_error());
}
?>