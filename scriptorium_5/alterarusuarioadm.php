<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel_usuario'] != 2) {
    header("Location: login.php");
    exit;
}

// ATUALIZAR USUÁRIO //

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_user'];
    $nivel = $_POST['nivel_user'];
    $nome = $_POST['nome_user'];
    $senha = $_POST['senha_user'];
    $nascimento = $_POST['nascimento_user'];
    $email = $_POST['email_user'];
    $pontos = $_POST['pontos_user'];


$sql = "UPDATE usuarios SET nivel_usuario=?, nome_usuario=?, senha_usuario=?, nascimento_usuario=?, email_usuario=?, pontos_usuario=? WHERE id_usuario=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("issssii", $nivel, $nome, $senha, $nascimento, $email, $pontos, $id);

if ($stmt->execute()) {
    $mensagem = "✅ Usuário atualizado com sucesso!";
} else {
    $mensagem = "❌ Erro ao atualizar o Usuário.";
}

}
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="stylesheet" href="alterar.css">-->
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Alterar Usuário</title>
    <style>
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

.botoesgestao {
    display: flex;
    flex-direction: row;
    justify-content: space-around;
}

.botoesgestao a {
    text-decoration: none;
    color: rgb(255, 255, 255);
}

.botaosup {
    width: 175px;
    height: 35px;
    margin: 5px;
    background-color: rgb(39, 30, 18);
    color: rgb(255, 255, 255);
    border-radius: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid rgb(223, 223, 223);
    box-shadow: 0px 0px 5px rgb(117, 117, 117);
}

.botaosup:hover {
    width: 175px;
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

.botaosupselect {
    width: 175px;
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

.tela {
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 10px;
    background-color: white;
}

.telasuperior {
    height: 10%;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
}

.telasuperior h2 {
    color: rgb(54, 42, 26);
}

.telasuperior a {
    text-decoration: none;
}

.acoes {
    display: flex;
    flex-direction: row;
    margin: 10px;
    align-items: center;
}

input {
    margin: 10px;
}

button {
    width: 100px;
    height: 30px;
}

.botaovoltar {
    width: 100px;
    background-color: rgb(54, 42, 26);
    color: azure;
    border-radius: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid rgb(255, 255, 255);
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
}

.botaovoltar:hover {
    width: 100px;
    background-color: rgb(65, 50, 30);
    color: azure;
    border-radius: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid rgb(255, 255, 255);
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
}

.telaconteudo {
    height: 90%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    border: 1px solid black;
    background-color: white;
}

.boxalinhamento {
    display: flex;
    flex-direction: row;
}

.box1 {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: left;
}

.box2 input {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: left;
    width: 120px;
    background-color: #C6B2A9;
    border: 2px solid rgb(59, 50, 35);
    box-shadow: 0px 0px 2px rgb(104, 88, 61);
}

input {
    background-color: #C6B2A9;
    border: 2px solid rgb(59, 50, 35);
    box-shadow: 0px 0px 2px rgb(104, 88, 61);
}

.tituloinput {
    width: 550px;
    background-color: #C6B2A9;
    border: 2px solid rgb(59, 50, 35);
    box-shadow: 0px 0px 2px rgb(104, 88, 61);
}

textarea {
    width: 602px;
    height: 100px;
    margin-top: 10px;
    margin-right: 25px;
    margin-left: 10px;
    resize: none;
    background-color: #C6B2A9;
    border: 2px solid rgb(59, 50, 35);
    box-shadow: 0px 0px 3px rgb(104, 88, 61);
}

.telaconteudo button {
    width: 100px; 
    height: 30px; 
    margin: auto;
    border-radius: 5px;
    background-color: rgb(54, 42, 26);
    color: azure;
    border: 1px solid rgb(255, 255, 255);
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
    margin-top: 45px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
}

.telaconteudo button:hover {
    width: 100px; 
    height: 30px; 
    margin: auto;
    border-radius: 5px;
    background-color: rgb(70, 55, 36);
    color: azure;
    border: 1px solid rgb(255, 255, 255);
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
    margin-top: 45px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.labelcss {
    color: rgb(54, 42, 26);
    font-size: 15px;
    font-weight: bold;
    margin-left: 10px;
}

.labelcss2{
    color: rgb(54, 42, 26);
    font-size: 15px;
    font-weight: bold;
    margin-right: 103px;
}

.labelcsstxt {
    color: rgb(54, 42, 26);
    font-size: 15px;
    font-weight: bold;
}

   select {
       background-color:#C6B2A9 !important; 
    border:2px solid rgb(59,50,35) !important; 
    box-shadow:0px 0px 2px rgb(104,88,61) !important; 
    color: rgb(54,42,26) !important; 
    height:35px !important; 
    padding:0 10px !important; 
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important; 
    margin:10px !important;
    width:200px !important;
    border-radius:3px !important;
    -webkit-appearance:none !important;
    -moz-appearance:none !important;
    appearance:none !important;
}

.msgupdate {
    margin-bottom: 60px;
}

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

        <div class="tela">
            <div class="telasuperior">
                <div><h2>Alterar Usuário</h2></div>
                <div><a href="usuariosadm.php"><h3 class="botaovoltar">Voltar</h3></a></div>
            </div>

            <div class="telaconteudo">
                <?php if ($mensagem): ?><p class="msgupdate" style="color:green;"><?php echo $mensagem; ?></p><?php endif; ?>

                <form method="POST">
                    <div class="boxalinhamento">
                        <div class="box2">
                            <label class="labelcss2">ID</label>
                            <input type="number" name="id_user" class="tituloinput" value="" required>
                        </div>
                        <div class="box1">
                            <label class="labelcss">E-mail</label>
                            <input type="text" name="email_user" class="tituloinput" value="" required>
                        </div>
                    </div>
                    <div class="boxalinhamento">
                    <div class="box1">
                            <label class="labelcss">Nome</label>
                            <input type="text" name="nome_user" class="tituloinput" value="" required>
                    </div>
                    <div class="box1">
                            <label class="labelcss">Data Nascimento</label>
                            <input type="date" name="nascimento_user" value="">
                    </div>
                    </div>
                    <div class="boxalinhamento">
                        <div class="box1">
                            <label class="labelcss">Senha</label>
                            <input type="number" name="senha_user" value="">
                        </div>
                        <div class="box1">
                            <label class="labelcss">Nível</label>
                            <input type="text" name="nivel_user" value="">
                        </div>
                        <div class="box1">
                            <label class="labelcss">Pontos</label>
                            <input type="text" name="pontos_user" value="">
                        </div>
                    </div>
                    <button type="submit" class="botaoatualizar">Atualizar</button>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>