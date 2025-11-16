<?php
session_start();
include "conexao.php";

$id_usuario = $_SESSION['id_usuario'] ?? 0;

if (!$id_usuario) {
    die("Usuário não logado.");
}

$mensagem = "";

if (isset($_GET['id_livro'])) {
    $id_livro = intval($_GET['id_livro']);

    $stmt = $conn->prepare("SELECT titulo_livro, paginas_livro, dificuldade, formatacao FROM livros WHERE id_livro = ?");
    $stmt->bind_param("i", $id_livro);
    $stmt->execute();
    $res = $stmt->get_result();
    $livro = $res->fetch_assoc();

    if (!$livro) die("Livro não encontrado.");

    $titulo = $livro['titulo_livro'];
    $paginas = $livro['paginas_livro'];
    $dificuldade = $livro['dificuldade'];
    $formatacao = $livro['formatacao'];
}

if (isset($_POST['solicitar_emprestimo'])) {
    $experiencia = $_POST['experiencia'] ?? 'iniciante';
    $horas_por_dia = floatval($_POST['horas_disponiveis'] ?? 1);

    $paginas_por_hora_base = ['fácil'=>50,'intermediário'=>45,'difícil'=>37,'muito difícil'=>30];
    $formatacao_mult = ['ruim'=>0.4,'ok'=>0.7,'boa'=>1,'ótima'=>1.3];
    $experiencia_mult = ['iniciante'=>0.7,'intermediário'=>1,'experiente'=>1.3];

    $paginas_por_hora = $paginas_por_hora_base[$dificuldade]*$formatacao_mult[$formatacao]*$experiencia_mult[$experiencia];
    $horas_totais = $paginas/$paginas_por_hora;
    $dias_totais = ceil($horas_totais/$horas_por_dia);

    $data_prev = strtotime("+$dias_totais days");
    $data_prevista = date("d/m/Y", $data_prev);

    $hoje = date("Y-m-d");
    $stmt2 = $conn->prepare("INSERT INTO emprestimos (id_usuario, id_livro, data_emprestimo, data_devolucao) VALUES (?, ?, ?, ?)");
    $data_devolucao_db = date("Y-m-d", $data_prev);
    $stmt2->bind_param("iiss", $id_usuario, $id_livro, $hoje, $data_devolucao_db);
    $stmt2->execute();

    $mensagem = "Livro <strong>$titulo</strong> emprestado com sucesso!<br>
                 Expectativa de leitura: $dias_totais dias<br>
                 Data prevista de devolução: $data_prevista<br><br>
                 <a href='painelusuario.php'><button class='botao'>Voltar para meus empréstimos</button></a>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
<title>Solicitar Empréstimo</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');

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
.main-box h2 {
    margin-bottom: 20px;
    text-align: center;
    text-shadow: 0 0 5px #000;
}
form { width: 100%; display: flex; flex-direction: column; align-items: center; }
label { margin-top: 10px; font-weight: bold; text-shadow: 0 0 3px #000; }
input, select { width: 80%; height: 35px; margin-top: 5px; border-radius: 5px; border:none; padding:5px; }
input[type="submit"] { margin-top:20px; cursor:pointer; background-color: rgb(39,30,18); color:#fff; box-shadow: 0 0 5px #000; }
input[type="submit"]:hover { background-color: rgb(65,50,30); }
.mensagem a button {
    width: 200px;
    height: 40px;
    margin-top: 15px;
    background-color: rgb(39, 30, 18); /* mesma cor dos botões principais */
    color: azure;
    border-radius: 5px;
    border: 1px solid rgb(182, 182, 181);
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s, color 0.3s;
}

.mensagem a button:hover {
    background-color: rgb(65, 50, 30);
    color: rgb(255, 255, 255);
}
.mensagem { 
    text-align: center; 
    color: #5C3A21; /* marrom escuro */
    font-weight: bold;
}
.mensagem a button { 
    margin-top:15px; 
}
</style>
</head>
<body>
<div class="main-box">
    <h2><?= htmlspecialchars($titulo) ?></h2>

    <?php if ($mensagem): ?>
        <div class="mensagem"><?= $mensagem ?></div>
    <?php else: ?>
        <form method="post">
            <label>Experiência do leitor:</label>
            <select name="experiencia">
                <option value="iniciante">Iniciante</option>
                <option value="intermediário">Intermediário</option>
                <option value="experiente">Experiente</option>
            </select>

            <label>Horas disponíveis por dia:</label>
            <input type="number" step="0.1" name="horas_disponiveis" value="1" required>

            <input type="submit" name="solicitar_emprestimo" value="Solicitar Empréstimo">
        </form>
    <?php endif; ?>
</div>
</body>
</html>

