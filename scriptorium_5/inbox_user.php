<?php
session_start();
include 'conexao.php'; 
date_default_timezone_set('America/Sao_Paulo'); 

// --- Verifica se há usuário logado --- //
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php"); 
    exit;
}

$id_usuario_logado = $_SESSION['id_usuario'];
$pontos_usuario = $_SESSION['pontos_usuario'] ?? 0;
$email_usuario_logado = $_SESSION['email_usuario'] ?? '';

// --- Garante que temos o email do usuário --- //
if (empty($email_usuario_logado)) {
    $sql_email = "SELECT email_usuario, pontos_usuario FROM usuarios WHERE id_usuario = ?";
    $stmt_email = $conn->prepare($sql_email);
    if ($stmt_email === false) die("Erro ao preparar consulta de email: " . $conn->error);
    
    $stmt_email->bind_param("i", $id_usuario_logado);
    $stmt_email->execute();
    $resultado_email = $stmt_email->get_result();
    
    if ($resultado_email->num_rows > 0) {
        $dados_email = $resultado_email->fetch_assoc();
        $email_usuario_logado = $dados_email['email_usuario'];
        $_SESSION['email_usuario'] = $email_usuario_logado; 
        $_SESSION['pontos_usuario'] = $dados_email['pontos_usuario'];
    } else {
        session_destroy();
        header("Location: login.php");
        exit;
    }
    $stmt_email->close();
}

// --- Busca mensagens enviadas pelo ADMIN para este usuário --- //
$sql_inbox = "
    SELECT 
        id_msg_adm, 
        email_remetente, 
        assunto, 
        conteudo, 
        data_envio 
    FROM 
        mensagens 
    WHERE 
        email_destinatario = ?
    ORDER BY 
        data_envio DESC
";

$stmt_inbox = $conn->prepare($sql_inbox);
if ($stmt_inbox === false) die("Erro ao preparar consulta de mensagens: " . $conn->error);

$stmt_inbox->bind_param("s", $email_usuario_logado);
$stmt_inbox->execute();
$resultado_consulta = $stmt_inbox->get_result(); 
$mensagens_array = $resultado_consulta->fetch_all(MYSQLI_ASSOC); 

$stmt_inbox->close();
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
    padding-left: 10px;
    padding-right: 10px;
    border: 1px solid black;
    display: flex;
    flex-direction: column;

    overflow: auto;
}

.resultado {
    background-color: rgb(255, 255, 255);
    width: 98%;
    height: 80%;
    margin: 10px;
    display: flex;
    flex-direction: column;

    /* Adicionado para exibir as mensagens rolando */
    overflow-y: auto; 
    padding-right: 5px;
    scrollbar-width: thin; /* Firefox */
}

/* Scrollbar personalizada (opcional) */
.resultado::-webkit-scrollbar {
    width: 8px;
}

.resultado::-webkit-scrollbar-thumb {
    background-color: rgb(39, 30, 18);
    border-radius: 4px;
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

.telabusca h2 {
    display: flex;
    justify-content: center;
    text-align: center;
    margin-top: 10px;
}

.boxmsg {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    margin-left: 10px;
    margin-right: 5px;
    margin-top: 10px;
    align-items: center;
    text-align: center;
}

.boxmsg h3 {
    width: 150px;
    height: 35px;
    background-color: rgb(27, 21, 13);
    color: white;
    border-radius: 5px;
    border: 1px solid rgb(182, 182, 181);
    font-weight: bold;
    display: flex;
    justify-content: center;
    align-items: center;
    border-image: linear-gradient(to right, rgb(255, 162, 23), rgb(223, 209, 11)) 1;
}

.boxmsg h3:hover {
    width: 150px;
    height: 35px;
    background-color: rgb(65, 50, 30);
    color: white;
    border-radius: 5px;
    border: 1px solid rgb(182, 182, 181);
    font-weight: bold;
    display: flex;
    justify-content: center;
    align-items: center;
    border-image: linear-gradient(to right, rgb(255, 162, 23), rgb(223, 209, 11)) 1;
}

.boxmsg h3 a {
    text-decoration: none;
    color: white;
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
            <div><p>Versão 1.0</p></div>
        </div>

        <div class="colunadireita">
            <div class="linhasuperior">
                <div class="boxnomeuser"><h5>USUÁRIO: <?php echo strtoupper(htmlspecialchars($_SESSION['nome_usuario'])); ?></h5></div>
                <div class="boxpontos">
                    <img src="imagens/star.png" alt="" width="20">
                    <h3><?php echo number_format($pontos_usuario, 0); ?></h3>
                </div>
            </div>

            <div class="telabusca">
                <div class="boxmsg">
                    <h2>MENSAGENS RECEBIDAS</h2>
                </div>

                <div class="resultado">              
    <div class="caixa-de-entrada-lista">
        <?php 
        if (!empty($mensagens_array)) {
            foreach ($mensagens_array as $mensagem) {
    $data_formatada = date('d/m/Y H:i:s', strtotime($mensagem['data_envio']));

    echo '<div class="mensagem" style="
        border: 1px solid #ccc; 
        padding: 15px; 
        margin-bottom: 12px; 
        border-radius: 6px; 
        background: #fff;
        overflow: hidden;
    ">';

    // meta (data/remetente)
    echo '<div class="data-hora" style="font-size: 0.9em; color: #666; margin-bottom: 8px;">';
    echo 'Enviada em: ' . $data_formatada . ' | Remetente: ' . htmlspecialchars($mensagem['email_remetente']);
    echo '</div>';

    // assunto e conteúdo
    echo '<h3 style="margin: 6px 0; color: #332212;">' . htmlspecialchars($mensagem['assunto']) . '</h3>';
    echo '<p style="margin: 6px 0 0 0; color:#222;">' . nl2br(htmlspecialchars($mensagem['conteudo'])) . '</p>'; 

    // contêiner de botões — flexbox horizontal, centralizado
    echo '<div style="
        margin-top: 15px; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        gap: 10px;
        flex-wrap: wrap;
    ">';

    // botão Responder
    echo '<button type="button" onclick="window.location.href=\'suporte.php\'" style="
        background-color: rgb(27, 21, 13);
        color: white;
        border: 1px solid rgb(182, 182, 181);
        border-radius: 5px;
        padding: 7px 15px;
        cursor: pointer;
        box-shadow: 0px 0px 5px rgb(114, 101, 78);
        font-weight: 600;
        white-space: nowrap;
    ">Responder</button>';

    // botão Excluir
    echo '<button type="button" onclick="
        if (confirm(\'Deseja mesmo excluir esta mensagem?\')) {
            this.closest(\'.mensagem\').remove();
        }
    " style="
        background-color: rgb(27, 21, 13);
        color: white;
        border: 1px solid rgb(182, 182, 181);
        border-radius: 5px;
        padding: 7px 15px;
        cursor: pointer;
        box-shadow: 0px 0px 5px rgb(114, 101, 78);
        font-weight: 600;
        white-space: nowrap;
    ">Excluir</button>';

    echo '</div>'; // fim container botões
    echo '</div>'; // fim mensagem
}
        } else {
            echo '<p>Você não tem nenhuma mensagem na sua caixa de entrada.</p>';
        }
        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>