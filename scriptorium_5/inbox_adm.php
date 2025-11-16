<?php
session_start();
include 'conexao.php'; // Inclui a conexão com o banco de dados

// ==============================================
// 1. VERIFICAÇÃO DE LOGIN E DADOS DO ADM
// ==============================================

// Verifica se o usuário está logado E se é Admin (nível 2)
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nivel_usuario']) || $_SESSION['nivel_usuario'] != 2) {
    header("Location: login.php");
    exit;
}

$id_usuario_logado = $_SESSION['id_usuario'];
$adm = []; // Inicializa o array para os dados do ADM
$pontos_adm = 0;
$email_adm = 'administrador@scriptorium.com'; // Valor de fallback

// Consulta para buscar o nome, pontos e email do ADM logado
$sql_adm = "SELECT nome_usuario, pontos_usuario, email_usuario FROM usuarios WHERE id_usuario = ?";
$stmt_adm = $conn->prepare($sql_adm);

if ($stmt_adm === false) {
    die("Erro SQL: Falha na preparação para buscar dados do ADM. " . $conn->error);
}

$stmt_adm->bind_param("i", $id_usuario_logado);
$stmt_adm->execute();
$resultado_adm = $stmt_adm->get_result();

if ($resultado_adm->num_rows > 0) {
    $adm = $resultado_adm->fetch_assoc();
    // Define variáveis essenciais
    $_SESSION['nome_usuario'] = $adm['nome_usuario']; 
    $email_adm = $adm['email_usuario'];
    $pontos_adm = $adm['pontos_usuario'];
} else {
    // Se o ID da sessão for inválido, desloga por segurança
    session_destroy();
    header("Location: login.php");
    exit;
}
$stmt_adm->close();

// E-mail de suporte fixo (para mensagens de suporte enviadas por usuários)
$email_suporte_fixo = 'suporte@scriptorium.com'; 


// ==============================================
// 2. BUSCA DE MENSAGENS PARA O INBOX DO ADM
// ==============================================
$mensagens_inbox = [];

// Seleciona mensagens onde o destinatário é o email do ADM logado OU o email de suporte fixo
$sql_mensagens = "
    SELECT id_msg_adm, email_remetente, assunto, conteudo, data_envio 
    FROM mensagens 
    WHERE email_destinatario = ? OR email_destinatario = ?
    ORDER BY data_envio DESC
";

$stmt_mensagens = $conn->prepare($sql_mensagens);

if ($stmt_mensagens === false) {
    die("Erro SQL: Falha na preparação para buscar mensagens. " . $conn->error);
}

// Binda: (Email do ADM, Email de Suporte Fixo)
$stmt_mensagens->bind_param("ss", $email_adm, $email_suporte_fixo);
$stmt_mensagens->execute();
$resultados_mensagens = $stmt_mensagens->get_result();

if ($resultados_mensagens->num_rows > 0) {
    while ($row = $resultados_mensagens->fetch_assoc()) {
        $mensagens_inbox[] = $row;
    }
}

$stmt_mensagens->close();
$conn->close();
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
}

.telabusca h3 {
    color: rgba(58, 36, 16, 1);
    text-align: center;
    display: flex;
    align-items: center;
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
    height: 400px;
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
    font-weight: bold;
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

.div_relatorio {
    display: flex;
    justify-content: space-between;
    margin-right: 6px;
    }


.div_relatorio button {
    width: 140px;
    padding: 0px;
    margin-left: 10px;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    background-color: rgb(39, 30, 18);
    border: 1px solid;
    border-radius: 5px;
    border-image: linear-gradient(to right, rgb(255, 162, 23), rgb(223, 209, 11)) 1;
}

.div_relatorio button:hover {
    width: 140px;
    padding: 0px;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    background-color: transparent;
    border-radius: 5px;
    transition: 0.5s;
    background-color: rgb(65, 50, 30);
}

.div_relatorio img {
    width: 12px;
    height: 15px;
    margin: 0px;
}

.div_relatorio a {
    width: 110px;
    height: 25px;
    margin: 0px;
    text-decoration: none;
    color: rgba(255, 255, 255, 1);
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    transition: 0.5s;
    font-size: 12px;
}

.div_relatorio a:hover {
    width: 110px;
    height: 25px;
    margin: 0px;
    text-decoration: none;
    color: rgba(255, 255, 255, 1);
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
}

.boxemail img {
    width: 35px;
    margin-left: 475px;
}
    </style>
</head>
<body>
    <main>
        <div class="colunaesquerda">
            <div class="boxperfil"><img src="imagens/imguser.png" alt="" width="80%"></div>
            <div class="caixabotoes">
                <div class="colunaesquerdap"><p>.</p></div> 
                <a href=""><div class="botaoselect"><h3>Painel</h3></div></a>
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
                    <h3><?php echo number_format($adm['pontos_usuario'], 0); ?></h3>
                </div>
            </div>
            <div class="telabusca">
                <div class="botoesgestao">
                    <h3>MENSAGENS</h3>
                    <a href="mensagemadm.php" class="botaosup">ENVIAR MSG</a>
                </div>
                
                <hr>

                <div class="resultado"> 
                    <?php if (count($mensagens_inbox) > 0): ?>
                        <?php foreach ($mensagens_inbox as $mensagem): ?>
                            <div class="mensagem-item" style="border-bottom: 1px dashed #ccc; padding: 10px 0;">
                                <p style="font-weight: bold; color: #362a17ff;">
                                    De: <?php echo htmlspecialchars($mensagem['email_remetente']); ?> | 
                                    Assunto: <?php echo htmlspecialchars($mensagem['assunto']); ?>
                                </p>
                                <p style="font-size: 0.9em; color: #555;">
                                    Enviada em: <?php echo date('d/m/Y H:i:s', strtotime($mensagem['data_envio'])); ?>
                                </p>
                                <p style="margin-top: 5px; margin-bottom: 10px;">
                                    <?php 
                                    // Limita o conteúdo para a visualização inicial
                                    echo nl2br(htmlspecialchars(substr($mensagem['conteudo'], 0, 150))) . '...'; 
                                    ?>
                                    </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 10px; color: #888;">Nenhuma mensagem na caixa de entrada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>