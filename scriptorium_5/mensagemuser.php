<?php
session_start();
include 'conexao.php'; // Inclui a conexão com o banco de dados

// ==============================================
// 1. VERIFICAÇÃO DE LOGIN E OBTENÇÃO DOS DADOS DO REMETENTE (USUÁRIO)
// ==============================================

// Garante que o usuário esteja logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php"); 
    exit;
}

$id_usuario_logado = $_SESSION['id_usuario'];
$email_remetente = ''; 
$pontos_usuario = 0;

// Consulta para buscar o email, nome e pontos do usuário logado
$sql_usuario = "SELECT nome_usuario, pontos_usuario, email_usuario FROM usuarios WHERE id_usuario = ?";
$stmt_usuario = $conn->prepare($sql_usuario);

if ($stmt_usuario === false) {
    die("Erro SQL: Falha na preparação para buscar dados do usuário. " . $conn->error);
}

$stmt_usuario->bind_param("i", $id_usuario_logado);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();

if ($resultado_usuario->num_rows > 0) {
    $usuario_data = $resultado_usuario->fetch_assoc();
    
    // Define variáveis essenciais para o HTML e o restante do script
    $_SESSION['nome_usuario'] = $usuario_data['nome_usuario']; 
    $_SESSION['email_usuario'] = $usuario_data['email_usuario']; 
    $email_remetente = $usuario_data['email_usuario'];
    $pontos_usuario = $usuario_data['pontos_usuario']; // Para o H3 dos pontos
    
} else {
    session_destroy();
    header("Location: login.php");
    exit;
}
$stmt_usuario->close();

$status_envio = ''; 
date_default_timezone_set('America/Sao_Paulo'); 

$email_remetente = $usuario_data['email_usuario'];
$pontos_usuario = $usuario_data['pontos_usuario'];

$mensagem_status = ''; 

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Coleta os dados do formulário
    $email_destinatario = htmlspecialchars($_POST['emailuser']); // Email do destinatário digitado
    $assunto            = htmlspecialchars($_POST['assunto']);
    $conteudo           = htmlspecialchars($_POST['conteudo']);
    
    // VERIFICAÇÃO 1: Impede que o usuário envie mensagem para si mesmo
    if ($email_remetente === $email_destinatario) {
         $status_envio = '<p style="color: orange; font-weight: bold;">Erro: Você não pode enviar uma mensagem para si mesmo.</p>';
         goto fim_processamento;
    }
    
    // VERIFICAÇÃO 2: Valida se o destinatário existe na tabela 'usuarios'
    $sql_busca_email = "SELECT id_usuario FROM usuarios WHERE email_usuario = ?";
    $stmt_busca = $conn->prepare($sql_busca_email);
    
    if ($stmt_busca === false) {
        $status_envio = '<p style="color: red; font-weight: bold;">Erro SQL: Falha na validação de e-mail. ' . $conn->error . '</p>';
        goto fim_processamento;
    }
    
    $stmt_busca->bind_param("s", $email_destinatario);
    $stmt_busca->execute();
    $resultado_busca = $stmt_busca->get_result();
    
    if ($resultado_busca->num_rows > 0) {
        
        // DESTINATÁRIO VÁLIDO: Insere a mensagem na tabela 'mensagens'
        $sql_insert = "
            INSERT INTO mensagens 
            (email_remetente, email_destinatario, assunto, conteudo) 
            VALUES (?, ?, ?, ?)
        ";
        $stmt_insert = $conn->prepare($sql_insert);
        
        if ($stmt_insert === false) {
            $status_envio = '<p style="color: red; font-weight: bold;">Erro de SQL (Inserção): ' . $conn->error . '</p>';
            goto fim_processamento;
        }

        // Parâmetros: (Email do Remetente, Email do Destinatário, Assunto, Conteúdo)
        $stmt_insert->bind_param(
            "ssss", 
            $email_remetente, 
            $email_destinatario, 
            $assunto, 
            $conteudo
        );

        if ($stmt_insert->execute()) {
            $status_envio = '<p style="color: green; font-weight: bold;">Mensagem enviada com sucesso para ' . htmlspecialchars($email_destinatario) . '.</p>';
        } else {
            $status_envio = '<p style="color: red; font-weight: bold;">Erro ao registrar a mensagem: ' . $stmt_insert->error . '</p>';
        }
        
        $stmt_insert->close();
        
    } else {
        $status_envio = '<p style="color: orange; font-weight: bold;">Erro: O e-mail destinatário fornecido não foi encontrado no sistema.</p>';
    }
    
    fim_processamento:
    if (isset($stmt_busca)) {
        $stmt_busca->close();
    }
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
                <a href="suporte.php"><div class="botao"><h3>Suporte</h3></div></a>
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
                    <h3><?php echo number_format($pontos_usuario, 0); ?></h3>
                </div>
            </div>
            <div class="telabusca">
                <?php echo  $status_envio; ?>
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
