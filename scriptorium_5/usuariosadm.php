<?php
session_start();
include 'conexao.php';

// Verifica login
if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel_usuario'] != 2) {
    header("Location: login.php");
    exit;
}

// Busca usuários
$busca = "";
if (isset($_GET['busca'])) {
    $busca = $_GET['busca'];
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nome_usuario LIKE ? OR email_usuario LIKE ? ORDER BY id_usuario ASC");
    $like = "%$busca%";
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM usuarios ORDER BY id_usuario ASC");
}
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scriptorium: Usuários</title>
<!--<link rel="stylesheet" href="rankingadm.css">-->
<link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
<style>
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
th, td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: left;
}
th {
    background-color: rgb(39, 30, 18);
    color: white;
}
.acao-btn {
    padding: 5px 8px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    display: inline-block;
}
.alterar { background-color:rgb(39, 30, 18); }
.alterar:hover { background-color:rgba(63, 49, 30, 1); }
.remover { background-color:rgb(39, 30, 18); }
.remover:hover { background-color:rgba(63, 49, 30, 1); }

.resultado {
    overflow-y: auto;
}
@import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');

* {
    padding: 0px;
    margin: 0px;
    box-sizing: border-box;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
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

.colunaesquerdap {
    color: rgb(202, 181, 154);
}

.colunaesquerda img {
    border:3px dotted rgb(156, 133, 103);
    border-radius: 65px;
    box-shadow: 0px 0px 5px rgb(100, 70, 46);
}

.boxperfil {
    margin-left: 9px;
}

.caixabotoes {
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    align-items: center;
    text-align: center;
}

.caixabotoes a {
    width: 100%;
    text-decoration: none;
}

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
    width: 100%;
    height: 35px;
    margin: 5px;
    background-color: rgb(65, 50, 30);
    color: azure;
    border-radius: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
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
}

.boxpontos h3 {
    text-shadow: 0px 0px 5px rgb(44, 27, 20);
}

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

.telabusca h3 {
    color: rgb(39, 30, 18);
}

.resultado {
    background-color: rgb(255, 255, 255);
    width: 98%;
    height: 80%;
    margin: 10px;
    border: 1px solid rgb(197, 197, 197);
    scrollbar-color: #dac7bfff rgb(39, 30, 18);
}

.botaosup {
    width: 115px;
    height: 35px;
    background-color: rgb(39, 30, 18);
    color: rgb(255, 255, 255);
    border-radius: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid rgb(223, 223, 223);
    box-shadow: 0px 0px 5px rgb(117, 117, 117);
    font-weight: bold;
}

.botaosup:hover {
    width: 115px;
    height: 35px;
    background-color: rgb(65, 50, 30);
    color: azure;
    border-radius: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid rgb(255, 255, 255);
    font-weight: bold;
}

form {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    margin: 25px;
}

form input {
    width: 500px;
    height: 30px;
    margin: 10px;
    border-radius: 20px;
    padding: 15px;
}

tr:nth-child(even) { background-color: #C6B2A9; }
tr:nth-child(odd) { background-color: #f2f2f2; }

.boxnomeuser {
    display: flex;
    flex-direction: row;
    align-items: center;
    text-align: center;
}

.boxnomeuser h5 {
    text-align: center;
    margin-top: 2px;
}

.boxnomeuser img {
    width: 18px;
    height: 18px;
    margin-left: 5px;
    margin-right: 5px;
}
</style>
</head>
<body>
<main>
    <div class="colunaesquerda">
        <div class="boxperfil"><img src="imagens/imguser.png" alt="" width="80%"></div>
        <div class="caixabotoes">
            <div class="colunaesquerdap"><p>.</p></div> 
            <a href="paineladm.php"><div class="botao"><h3>Painel</h3></div></a>
            <a href="buscalivroadm.php"><div class="botao"><h3>Livros</h3></div></a>
            <a href="usuariosadm.php"><div class="botaoselect"><h3>Usuários</h3></div></a>
            <a href="rankingadm.php"><div class="botao"><h3>Ranking</h3></div></a>
            <a href="logout.php"><div class="botao"><h3>Sair</h3></div></a>
        </div>
        <div class="colunaesquerdap"><p>.</p></div> 
        <div class="colunaesquerdap"><p>.</p></div>  
        <div class="colunaesquerdap"><p>.</p></div> 
        <div class="colunaesquerdap"><p>.</p></div> 
        <div><p>Versão 1.0</p></div>
    </div>

    <div class="colunadireita">
        <div class="linhasuperior">
            <div class="boxnomeuser">
                <img src="imagens/crown.png" alt="">
                <h5>ADM: <?php echo strtoupper(htmlspecialchars($_SESSION['nome_usuario'])); ?></h5></div>
            <div class="boxpontos">
                <img src="imagens/star.png" alt="" width="20">
                <h3><?php echo number_format($_SESSION['pontos_usuario'], 0); ?></h3>
            </div>
        </div>

        <div class="telabusca">
            <form method="GET" class="busca">
                <input type="text" name="busca" placeholder="Digite sua busca..." value="<?php echo htmlspecialchars($busca); ?>">
                <button type="submit" class="botaosup">Buscar</button>
            </form>

            <div class="resultado">
                <?php if(count($usuarios) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Nível</th>
                                <th>Pontos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['id_usuario']; ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nome_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email_usuario']); ?></td>
                                    <td><?php echo $usuario['nivel_usuario']; ?></td>
                                    <td><?php echo number_format($usuario['pontos_usuario'], 0); ?></td>
                                    <td style="display: flex; gap: 10px;">
                                        <a class="acao-btn alterar" href="alterarusuarioadm.php?id=">Alterar</a>
                                        <a class="acao-btn remover" href="removeruseradm.php?id=">Remover</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum usuário encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
</body>
</html>