<?php
session_start();
include 'conexao.php';

$id_usuario = $_SESSION['id_usuario'];
$id_livro = $_POST['id_livro'];

// Verifica se o livro já está registrado como lido
$sqlCheck = "SELECT * FROM devolucoes WHERE id_usuario = ? AND id_livro = ?";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("ii", $id_usuario, $id_livro);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    // Insere devolução sem empréstimo
    $sqlInsert = "INSERT INTO devolucoes (id_usuario, id_livro, data_devolucao, id_emprestimo) VALUES (?, ?, CURDATE(), NULL)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ii", $id_usuario, $id_livro);
    $stmtInsert->execute();
}

header("Location: buscar_livros.php");
exit;
?>