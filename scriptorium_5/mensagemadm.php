<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel_usuario'] != 2) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$admin = $_SESSION['pontos_usuario'];

$sql = "SELECT nome_usuario, pontos_usuario, email_usuario FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$adm = $result->fetch_assoc();

$email_remetente = $adm['email_usuario']; 

$sql_eventos = "SELECT id_evento, relatorio FROM eventos ORDER BY id_evento DESC";
$stmt_eventos = $conn->prepare($sql_eventos);
$stmt_eventos->execute();
$resultados_eventos = $stmt_eventos->get_result();

$sql_mensagens = "SELECT id_msg, id_usuario, email, assunto, conteudo, data_envio FROM suporte ORDER BY id_msg DESC";
$stmt_mensagens = $conn->prepare($sql_mensagens);
$stmt_mensagens->execute();
$resultados_mensagens = $stmt_mensagens->get_result();

$mensagem_status = ''; 
date_default_timezone_set('America/Sao_Paulo'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email_destinatario = htmlspecialchars($_POST['emailuser']);
    $assunto            = htmlspecialchars($_POST['assunto']);
    $conteudo           = htmlspecialchars($_POST['conteudo']);
    
    $sql_busca_email = "SELECT id_usuario FROM usuarios WHERE email_usuario = ?";
    $stmt_busca = $conn->prepare($sql_busca_email);
    
    if ($stmt_busca === false) {
        $mensagem_status = '<p style="color: red; font-weight: bold;">Erro de SQL (Validação): ' . $conn->error . '</p>';
        goto fim_envio;
    }
    
    $stmt_busca->bind_param("s", $email_destinatario);
    $stmt_busca->execute();
    $resultado_busca = $stmt_busca->get_result();
    
    if ($resultado_busca->num_rows > 0) {
        
        $sql_insert = "
            INSERT INTO mensagens 
            (email_remetente, email_destinatario, assunto, conteudo) 
            VALUES (?, ?, ?, ?)
        ";
        $stmt_insert = $conn->prepare($sql_insert);
        
        if ($stmt_insert === false) {
            $mensagem_status = '<p style="color: red; font-weight: bold;">Erro de SQL (Inserção): ' . $conn->error . '</p>';
            goto fim_envio;
        }

        $stmt_insert->bind_param(
            "ssss", 
            $email_remetente, 
            $email_destinatario, 
            $assunto, 
            $conteudo
        );

        if ($stmt_insert->execute()) {
            $mensagem_status = '<p style="color: green; font-weight: bold;">Mensagem enviada com sucesso e registrada.</p>';
        } else {
            $mensagem_status = '<p style="color: red; font-weight: bold;">Erro ao registrar a mensagem: ' . $stmt_insert->error . '</p>';
        }
        
        $stmt_insert->close();
        
    } else {
        $mensagem_status = '<p style="color: orange; font-weight: bold;">Erro: Usuário com o e-mail fornecido não encontrado. A mensagem não foi enviada.</p>';
    }
    
    fim_envio:
    if (isset($stmt_busca)) {
        $stmt_busca->close();
    }
}
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="stylesheet" href="paineladm.css">-->
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Administrador</title>
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
    color: rgb(255, 255, 255);
}

.eventosusuarios {
    background-color: white;
    width: 98%;
    height: 200px;
    margin: 10px;
    padding-left: 10px;
    display: flex;
    border: 2px solid rgb(39, 30, 18);
    overflow-X: auto;
    overflow-y: auto;
    max-height: 300px;
    scrollbar-color: #dac7bfff rgb(39, 30, 18);

}

.resultado {
    background-color: rgb(255, 255, 255);
    width: 98%;
    height: 200px;
    padding-left: 10px;
    margin: 10px;
    border: 2px solid rgb(39, 30, 18);
    overflow-X: auto;
    overflow-y: auto;
    scrollbar-color: #dac7bfff rgb(39, 30, 18);
}

.botoesgestao {
    display: flex;
    flex-direction: row;
    justify-content: space-between;;
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

hr {
    margin: 10px;
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
                <div class="colunaesquerdap"><p>.</p></div> 
                <a href="paineladm.php"><div class="botaoselect"><h3>Painel</h3></div></a>
                <a href="buscalivroadm.php"><div class="botao"><h3>Livros</h3></div></a>
                <a href="usuariosadm.php"><div class="botao"><h3>Usuários</h3></div></a>
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
                    <h5>ADM: <?php echo strtoupper(htmlspecialchars($_SESSION['nome_usuario'])); ?></h5>
                </div>
                <div class="boxpontos">
                    <img src="imagens/star.png" alt="" width="20">
                    <h3><?php echo number_format($admin, 0); ?></h3>
                </div>
            </div>

            <div class="telabusca">
                <?php echo $mensagem_status; ?>
                <form method="POST" action="">
                    <label for="emailuser">E-mail:</label>
                    <input type="text" id="emailuser" name="emailuser" required>
                    <label for="assunto">Assunto:</label>
                    <input type="text" id="assunto" name="assunto" required>
                    <label for="conteudo">Mensagem:</label>
                    <textarea id="conteudo" name="conteudo" rows="6" required></textarea>
                    <button type="submit">Enviar Mensagem</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>