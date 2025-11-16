<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$nome_usuario = $_SESSION['nome_usuario'];
$pontos_usuario = $_SESSION['pontos_usuario'] ?? 0;

// Busca ranking completo
$stmt = $conn->prepare("SELECT nome_usuario, pontos_usuario FROM usuarios ORDER BY pontos_usuario DESC");
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scriptorium: Ranking</title>
<link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
<style>
/* Reaproveitando o CSS do rankingadm para Top 3 + tabela */
@import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');

* {
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    width: 100vw;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: rgb(37, 37, 37);
}

main {
    width: 1000px;
    height: 550px;
    background-color: rgb(113, 129, 129);
    display: flex;
    flex-direction: row;
    border: 1px solid rgb(230, 203, 168);
    box-shadow: 0px 0px 5px white;
}

.colunaesquerda {
    background-image: url(imagens/bk_login.png);
    width: 20%;
    height: 100%;
    padding: 10px 20px 10px 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    text-align: center;
    color: rgb(88, 88, 88);
}

.colunaesquerdap { color: rgb(202, 181, 154); }

.colunaesquerda img {
    border:3px dotted rgb(156, 133, 103);
    border-radius: 65px;
    box-shadow: 0px 0px 5px rgb(100, 70, 46);
}

.boxperfil { margin-left: 9px; }

.caixabotoes {
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    align-items: center;
    text-align: center;
}

.caixabotoes a { width: 100%; text-decoration: none; }

.botao {
    width: 100%;
    height: 35px;
    margin: 5px;
    background-color: rgb(39, 30, 18);
    color: rgb(255, 255, 255);
    border-radius: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid rgb(182, 182, 181);
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
}

.botao:hover {
    background-color: rgb(65, 50, 30);
    color: azure;
    border: 1px solid rgb(255, 255, 255);
}

.botaoselect {
    width: 100%;
    height: 35px;
    margin: 5px;
    background-color: rgb(39, 30, 18);
    color: azure;
    border-radius: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
    border-width: 1px; 
    border-style: solid; 
    border-image: linear-gradient(to right, rgb(255, 162, 23), rgb(223, 209, 11)) 1;
}

.colunadireita {
    background-color: azure;
    width: 80%;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.linhasuperior {
    background-color: rgb(39, 30, 18);
    width: 100%;
    height: 10%;
    padding: 10px;
    color: azure;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
}

.boxpontos {
    background-color: rgb(206, 186, 164);
    border-radius: 10px;
    width: 100px;
    height: 35px;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border: 1px solid rgb(255, 220, 24);
    text-shadow: 0px 0px 5px black;
}

.boxpontos h3 { text-shadow: 0px 0px 5px rgb(44, 27, 20); }

.telabusca {
    background-color: rgb(255, 255, 255);
    width: 100%;
    height: 90%;
    padding: 10px;
    border: 1px solid black;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.top3-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}

.top3-card {
    background-color: #f1c40f;
    padding: 10px;
    border-radius: 10px;
    text-align: center;
    width: 30%;
    margin: 5px;
    border: 1px solid black;
    box-shadow: 2px 2px 5px #acacacff;
}

.top3-card h3 { color: white; margin-bottom: 5px; text-shadow: 2px 2px 2px black; }
.top3-card p { font-weight: 700; color: white; font-size: 18px; text-shadow: 2px 2px 2px #000; }

.resultado {
    background-color: rgb(255, 255, 255);
    width: 98%;
    height: 70%;
    margin: 10px;
    border: 1px solid rgb(197, 197, 197);
    overflow-y: auto;
    scrollbar-color: #dac7bfff rgb(39, 30, 18);
}

.resultado table {
    width: 100%;
    border-collapse: collapse;
}

.resultado th, .resultado td {
    border: 1px solid #ccccccff;
    padding: 8px;
    text-align: left;
}

.resultado th {
    background-color: rgb(39, 30, 18);
    color: white;
}

.resultado tr:nth-child(even) { background-color: #C6B2A9; }
.resultado tr:nth-child(odd) { background-color: #f2f2f2; }

</style>
</head>
<body>
<main>
    <div class="colunaesquerda">
        <div class="boxperfil"><img src="imagens/imguser.png" alt="" width="80%"></div>
        <div class="caixabotoes">
        <a href=""><div class="botao"><h3>Meus Livros</h3></div></a>
        <a href="buscalivro.php"><div class="botao"><h3>Buscar Livros</h3></div></a>
        <a href="ranking.php"><div class="botaoselect"><h3>Ranking</h3></div></a>
        <a href="suporte.php"><div class="botao"><h3>Suporte</h3></div></a>
        <a href="logout.php"><div class="botao"><h3>Sair</h3></div></a>
        </div>
        <div class="colunaesquerdap"><p>.</p></div> 
        <div class="colunaesquerdap"><p>.</p></div>           
        <div><p>Versão 1.0</p></div>
    </div>

    <div class="colunadireita">
        <div class="linhasuperior">
            <div class="boxnomeuser"><h5>USUÁRIO: <?php echo strtoupper(htmlspecialchars($_SESSION['nome_usuario'])); ?></h5></div>
            <div class="boxpontos"><img src="imagens/star.png" width="20"><h3><?= number_format($pontos_usuario,0) ?></h3></div>
        </div>

        <div class="telabusca">
            <h2>Ranking</h2>

            <div class="top3-container">
                <?php for($i=0; $i<min(3,count($usuarios)); $i++): ?>
                    <div class="top3-card">
                        <h3>#<?= $i+1 ?> - <?= htmlspecialchars($usuarios[$i]['nome_usuario']) ?></h3>
                        <p><?= number_format($usuarios[$i]['pontos_usuario'],0) ?> pts</p>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="resultado">
                <?php if(count($usuarios)>3): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Posição</th>
                                <th>Usuário</th>
                                <th>Pontos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i=3; $i<count($usuarios); $i++): ?>
                                <tr>
                                    <td><?= $i+1 ?></td>
                                    <td><?= htmlspecialchars($usuarios[$i]['nome_usuario']) ?></td>
                                    <td><?= number_format($usuarios[$i]['pontos_usuario'],0) ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Não há outros usuários além do Top 3.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>
</body>
</html>