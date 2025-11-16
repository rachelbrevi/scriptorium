<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Pega o id_emprestimo enviado via GET ou POST
$id_emprestimo = isset($_GET['id_emprestimo']) ? $_GET['id_emprestimo'] : (isset($_POST['id_emprestimo']) ? $_POST['id_emprestimo'] : 0);

if (!$id_emprestimo) {
    header("Location: painelusuario.php");
    exit;
}

// Primeiro, busca os dados do empréstimo
$stmt = $conn->prepare("SELECT id_livro FROM emprestimos WHERE id_emprestimo = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_emprestimo, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$emprestimo = $result->fetch_assoc();

if (!$emprestimo) {
    // Empréstimo não encontrado ou não pertence ao usuário
    header("Location: painelusuario.php");
    exit;
}

$id_livro = $emprestimo['id_livro'];

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marcarComoLido = isset($_POST['marcar_lido']) && $_POST['marcar_lido'] === 'sim';

    // Inserir na tabela devolucoes
    $stmt = $conn->prepare("INSERT INTO devolucoes (id_usuario, id_livro, data_devolucao, id_emprestimo) VALUES (?, ?, CURDATE(), ?)");
    $stmt->bind_param("iii", $id_usuario, $id_livro, $id_emprestimo);
    $stmt->execute();

    // Se marcou como lido, insere na tabela livros_lidos (se ainda não estiver lá)
    if ($marcarComoLido) {
        $stmtCheck = $conn->prepare("SELECT id FROM livros_lidos WHERE id_usuario = ? AND id_livro = ?");
        $stmtCheck->bind_param("ii", $id_usuario, $id_livro);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();

        if ($resCheck->num_rows == 0) {
            $stmtInsert = $conn->prepare("INSERT INTO livros_lidos (id_usuario, id_livro, data_lido) VALUES (?, ?, CURDATE())");
            $stmtInsert->bind_param("ii", $id_usuario, $id_livro);
            $stmtInsert->execute();
        }
    }

    // Redireciona para painel
    header("Location: painelusuario.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Devolver Livro</title>
<link href="https://fonts.googleapis.com/css2?family=Gloock&display=swap" rel="stylesheet">
<style>
* { padding:0; margin:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { width:100vw; height:100vh; display:flex; justify-content:center; align-items:center; background-color:rgb(37,37,37); }
.main-box {
    width: 500px;
    padding: 30px;
    background-image: url(imagens/bk_login.png);
    background-size: cover;
    background-position: center;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(255,255,255,0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #fff;
}
.main-box h2 { margin-bottom: 20px; text-align: center; text-shadow: 0 0 5px #000; }
form { width: 100%; display: flex; flex-direction: column; align-items: center; }
button { width: 250px; height: 40px; margin-top: 15px; background-color: rgb(39, 30, 18); color: azure; border-radius: 5px; border: 1px solid rgb(182, 182, 181); box-shadow: 0px 0px 5px rgb(59, 50, 35); cursor: pointer; font-weight: bold; transition: background-color 0.3s, color 0.3s; }
button:hover { background-color: rgb(65, 50, 30); color: #fff; }
.mensagem { text-align: center; color: #5C3A21; font-weight: bold; margin-top: 10px; }
</style>
</head>
<body>
<div class="main-box">
    <h2>Devolver Livro</h2>
    <p class="mensagem">Você terminou de ler este livro?</p>
    <form method="POST">
        <input type="hidden" name="id_emprestimo" value="<?= $id_emprestimo ?>">
        <button type="submit" name="marcar_lido" value="nao">Não, apenas devolver</button>
        <button type="submit" name="marcar_lido" value="sim">Sim, devolver e marcar como lido</button>
    </form>
</div>
</body>
</html>