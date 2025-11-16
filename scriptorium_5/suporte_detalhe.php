<?php
session_start();
include 'conexao.php';

// valida sessão admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel_usuario'] != 2) {
    header("Location: login.php");
    exit;
}

// valida id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Mensagem inválida.";
    exit;
}

$id = (int) $_GET['id'];

// busca mensagem
$sql = "SELECT id_msg, id_usuario, email, assunto, conteudo, data_envio FROM suporte WHERE id_msg = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Mensagem não encontrada.";
    exit;
}

$msg = $result->fetch_assoc();

// pega dados do admin (para mostrar pontos no topo como no painel)
$id_usuario = $_SESSION['id_usuario'];
$sql2 = "SELECT nome_usuario, pontos_usuario FROM usuarios WHERE id_usuario = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id_usuario);
$stmt2->execute();
$result2 = $stmt2->get_result();
$adm = $result2->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scriptorium: Detalhe da Mensagem</title>
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');

* {
    padding: 0px;
    margin: 0px;
    box-sizing: border-box;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    width: 100vw;
    height: 100vh;   /* use height em vez de min-height */
    display: flex;
    justify-content: center;
    align-items: center; /* centraliza verticalmente */
    background-color: rgb(37, 37, 37);
    padding: 0; /* remove o padding-top */
}

main {
    width: 1000px;
    min-height: 550px; /* mesma altura que paineladm */
    background-color: rgb(113, 129, 129);
    display: flex;
    flex-direction: row;
    border: 1px solid rgb(230, 203, 168);
    box-shadow: 0px 0px 5px white;
}

.colunaesquerda {
    background-image: url(imagens/bk_login.png);
    width: 20%;
    height: 100%; /* substituir 100% por 100% do main */
    min-height: 555px; /* mesma altura do main */
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
    min-height: 100%;
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

/* conteúdo principal (igual ao painel) */

.telabusca {
    background-color: rgb(255, 255, 255);
    width: 100%;
    /* altura do painel à direita — não mude muito para não quebrar o layout */
    min-height: 500px;
    padding: 20px;
    border: 1px solid black;
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* mantém o conteúdo no topo da área */
    align-items: stretch;         /* faz o conteúdo ocupar a largura disponível */
    box-sizing: border-box;
}

/* cartão da mensagem com tamanho padronizado */
.caixa-msg {
    background-color: #fff;
    color: #111;
    border-radius: 8px;
    padding: 18px;
    max-width: 820px;
    width: 100%;
    margin: 0 auto;
    box-shadow: none;
    border: 1px solid #ddd;

    /* layout em coluna para controlar o fluxo interno */
    display: flex;
    flex-direction: column;

    /* altura fixa para padronizar todos os cartões */
    height: 420px;      /* ajuste esse valor se quiser mais/menos alto */
    box-sizing: border-box;
}

/* título e meta permanecem com tamanho natural */
.caixa-msg h2 {
    margin: 0 0 8px 0;
    color: #27221a;
    font-size: 18px;
    flex: 0 0 auto;
}

/* meta (de/data/id) - ocupa só o necessário */
.meta {
    display:flex;
    gap:10px;
    color:#666;
    font-size:13px;
    margin-bottom: 12px;
    flex: 0 0 auto;
}

/* área de conteúdo: cresce e vira área com rolagem interna quando necessário */
.conteudo {
    color:#222;
    line-height:1.5;
    white-space: pre-wrap;
    margin-top: 0;
    padding-top: 12px;
    border-top: 1px solid #eee;

    /* controla o crescimento */
    flex: 1 1 auto;      /* ocupa o espaço restante do cartão */
    overflow-y: auto;    /* cria scroll interno quando ultrapassar a altura */
    overflow-x: hidden;
    padding-right: 8px;  /* evita que a barra de rolagem encoste no texto */
    word-break: break-word;
}

/* botoes sempre no fim, não são empurrados pelo conteúdo */
.acoes {
    margin-top: 12px;
    display:flex;
    gap:10px;
    justify-content:flex-start;
    align-items:center;
    flex: 0 0 auto; /* permanece com altura fixa conforme conteúdo dos botões */
}

/* ajuste sutil para que os botões fiquem menores e alinhados */
.acoes .botao, .acoes .botaoselect {
    width: auto;
    height: 32px;
    padding: 6px 12px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
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
                    <h3><?php echo number_format($adm['pontos_usuario'], 0); ?></h3>
                </div>
            </div>

            <div class="telabusca">
                <h4>Detalhe da Mensagem</h4>

                <div class="caixa-msg">
                    <h2><?php echo htmlspecialchars($msg['assunto']); ?></h2>

                    <div class="meta">
                        <div><strong>De:</strong> <?php echo htmlspecialchars($msg['email']); ?></div>
                        <div><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($msg['data_envio'])); ?></div>
                        <div><strong>ID:</strong> <?php echo (int)$msg['id_msg']; ?></div>
                    </div>

                    <div class="conteudo">
                        <?php echo nl2br(htmlspecialchars($msg['conteudo'])); ?>
                    </div>

                    <div class="acoes">
                        <a href="mensagemadm.php?email=<?php echo urlencode($msg['email']); ?>&assunto=<?php echo urlencode('Re: ' . $msg['assunto']); ?>">
                            <div class="botaoselect"><h3>Responder</h3></div>
                        </a>

                        <a href="paineladm.php">
                            <div class="botao"><h3>Voltar</h3></div>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>
