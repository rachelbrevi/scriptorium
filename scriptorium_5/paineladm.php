<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel_usuario'] != 2) {
    header("Location: login.php");
    exit;
}

// Pega dados do administrador logado
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT nome_usuario, pontos_usuario FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$adm = $result->fetch_assoc();

$sql_eventos = "SELECT id_evento, relatorio FROM eventos ORDER BY id_evento DESC";
$stmt_eventos = $conn->prepare($sql_eventos);
$stmt_eventos->execute();
$resultados_eventos = $stmt_eventos->get_result();

$sql_mensagens = "SELECT id_msg, id_usuario, email, assunto, conteudo, data_envio FROM suporte ORDER BY id_msg DESC";
$stmt_mensagens = $conn->prepare($sql_mensagens);
$stmt_mensagens->execute();
$resultados_mensagens = $stmt_mensagens->get_result();
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Administrador</title>
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
            overflow-x: auto;
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
            overflow-x: auto;
            overflow-y: auto;
            scrollbar-color: #dac7bfff rgb(39, 30, 18);
        }

        .botoesgestao {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
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
            background-color: rgb(65, 50, 30);
            color: azure;
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
            align-items: center;
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
            color: rgba(255, 255, 255, 1);
        }

        .boxadmemail {
            display: flex;
            flex-direction: row;
        }

      
        .resultado table {
            width: 100%;
            border-collapse: collapse;
        }

        .resultado th, .resultado td {
            padding: 2px 6px;
            font-size: 13px;
            border-bottom: 1px solid #e3e3e3;

        }

        .resultado th {
            background-color: #27221a;
            color: #fff;
        }

        .resultado td .botao h3 {
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .resultado td .botao {
            padding: 2px 6px;
        }
        

.resultado td a {
    text-decoration: none;
    display: inline-block; 
    padding-right: 5px;    
}

.resultado td .botao {
    padding: 2px 10px; 
    box-sizing: border-box;
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
                <a href="adicionar.php"><div class="botaosup"><h3>Adicionar</h3></div></a>
                <a href="alterar.php"><div class="botaosup"><h3>Alterar</h3></div></a>
                <a href="remover.php"><div class="botaosup"><h3>Remover</h3></div></a>
            </div>
            <hr>
            <div class="div_relatorio">
                <h4>Eventos</h4>
                <button>
                    <a href="gerar_relatorio.php" target="_blank" class="btn btn-primary">Baixar Relatório</a>
                    <img src="imagens\download_icon.png" alt="">
                </button>
            </div>
            <div class="eventosusuarios">
                <?php if ($resultados_eventos->num_rows > 0): ?>
                    <ul style="list-style: none; padding: 0; margin: 0; width: 100%;">
                        <?php foreach ($resultados_eventos as $resevent): ?>
                            <li style="border-bottom: 1px solid #e3e3e3; padding: 8px 0; font-size: 14px; color: #333;">
                                <span style="color: #888; font-size: 13px;">#<?php echo htmlspecialchars($resevent['id_evento']); ?> — </span>
                                <?php echo htmlspecialchars($resevent['relatorio']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="padding: 10px; text-align: center; color: #777;">Nenhum evento registrado.</p>
                <?php endif; ?>
            </div>

            <div class="div_relatorio">
                <h4>Mensagens ao Suporte</h4>
                <div class="boxadmemail"></div>
            </div>
            <div class="resultado">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Remetente</th>
                            <th>Assunto</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados_mensagens as $resmsg): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($resmsg['data_envio'])); ?></td>
                                <td><?php echo htmlspecialchars($resmsg['email']); ?></td>
                                <td><?php echo htmlspecialchars($resmsg['assunto']); ?></td>
                                <td style="text-align:center;">
                                    <a href="suporte_detalhe.php?id=<?php echo (int)$resmsg['id_msg']; ?>">
                                        <div class="botao"><h3>Ver</h3></div>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</main>
</body>
</html>