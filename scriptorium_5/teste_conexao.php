<?php
include "conexao.php";

if ($conn) {
    echo "Conexão com o banco de dados bd_scriptorium realizada com sucesso!";
} else {
    echo "Erro ao conectar: " . mysqli_connect_error();
}
?>