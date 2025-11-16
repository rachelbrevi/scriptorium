<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Pega os dados do usuário logado
$id_usuario = $_SESSION['id_usuario'];
$sqlUser = "SELECT nome_usuario, email_usuario, pontos_usuario FROM usuarios WHERE id_usuario = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $id_usuario);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$usuario = $resultUser->fetch_assoc();
$stmtUser->close();

// --- Lógica de envio do formulário ---
$mensagemEnviada = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $assunto = trim($_POST['assunto']);
    $conteudo = trim($_POST['conteudo']);
    $data_envio = date("Y-m-d H:i:s");
    $email = $usuario['email_usuario']; // pega o email correto do usuário

    // Grava no banco
    $sql = "INSERT INTO suporte (id_usuario, email, assunto, conteudo, data_envio) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $id_usuario, $email, $assunto, $conteudo, $data_envio);

    if ($stmt->execute()) {
        $mensagemEnviada = true;
    } else {
        echo "<script>alert('Erro ao enviar mensagem.');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Suporte</title>
    <style>
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

        .botao, .botaoselect {
            width: 100%;
            height: 35px;
            margin: 5px;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0px 0px 5px rgb(59, 50, 35);
            color: white;
        }

        .botao {
            background-color: rgb(39, 30, 18);
            border: 1px solid rgb(182, 182, 181);
        }

        .botao:hover {
            background-color: rgb(65, 50, 30);
            border: 1px solid rgb(255, 255, 255);
        }

        .botaoselect {
            background-color: rgb(39, 30, 18);
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
            margin: 15px auto 0;
        }

        .resultado {
            background-color: rgb(255, 255, 255);
            width: 98%;
            height: 80%;
            margin: 10px;
            display: flex;
            flex-direction: column;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input, textarea {
            border: 2px solid rgb(87, 58, 30);
            border-radius: 5px;
            padding: 5px;
        }

        input {
            height: 25px;
        }

        textarea {
            height: 250px;
            margin-top: 10px;
            margin-bottom: 10px;
            resize: none;
        }

        form button {
            width: 20%;
            height: 35px;
            background-color: rgb(27, 21, 13);
            color: white;
            border-radius: 5px;
            border: 1px solid rgb(182, 182, 181);
            box-shadow: 0px 0px 5px rgb(114, 101, 78);
            margin: 15px auto 0;
            font-weight: bold;
        }

        form button:hover {
            background-color: rgb(65, 50, 30);
            color: azure;
            border: 1px solid rgb(255, 255, 255);
        }

        label {
            color: rgb(87, 58, 30);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <main>
        <div class="colunaesquerda">
            <div class="boxperfil"><img src="imagens/imguser.png" alt="" width="80%"></div>
            <div class="caixabotoes">
                <a href="painelusuario.php"><div class="botao"><h3>Meus Livros</h3></div></a>
                <a href="buscalivro.php"><div class="botao"><h3>Buscar Livros</h3></div></a>
                <a href="ranking.php"><div class="botao"><h3>Ranking</h3></div></a>
                <a href=""><div class="botaoselect"><h3>Suporte</h3></div></a>
                <a href="logout.php"><div class="botao"><h3>Sair</h3></div></a>
            </div>    
            <div class="colunaesquerdap"><p>.</p></div> 
            <div class="colunaesquerdap"><p>.</p></div>           
            <div><p>Versão 1.0</p></div>
        </div>

        <div class="colunadireita">
            <div class="linhasuperior">
                <div class="boxnomeuser"><h5>USUÁRIO: <?php echo strtoupper(htmlspecialchars($_SESSION['nome_usuario'])); ?></h5></h4></div>
                <div class="boxpontos">
                    <img src="imagens/star.png" alt="" width="20">
                    <h3><?php echo number_format($usuario['pontos_usuario'], 0); ?></h3>
                </div>
            </div>

            <div class="telabusca">
                <h3>Suporte</h3>
                <div class="resultado">
                    <?php if ($mensagemEnviada): ?>
                        <p style="color: green; text-align:center; font-weight:bold;">Mensagem enviada com sucesso!</p>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <label for="assunto">Assunto:</label>
                        <input type="text" id="assunto" name="assunto" required>
                        <label for="conteudo">Mensagem:</label>
                        <textarea id="conteudo" name="conteudo" rows="6" required></textarea>
                        <button type="submit">Enviar Mensagem</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
