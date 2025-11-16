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
$sql = "SELECT nome_usuario, pontos_usuario FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// =====================
// Meus Livros (emprestados e ainda não devolvidos)
// =====================
$sqlMeusLivros = "
    SELECT e.id_emprestimo, e.data_emprestimo, e.data_devolucao, l.titulo_livro, l.autor_livro
    FROM emprestimos e
    JOIN livros l ON e.id_livro = l.id_livro
    LEFT JOIN devolucoes d ON e.id_emprestimo = d.id_emprestimo
    WHERE e.id_usuario = ? AND d.id_devolucao IS NULL
";
$stmtLivros = $conn->prepare($sqlMeusLivros);
$stmtLivros->bind_param("i", $id_usuario);
$stmtLivros->execute();
$resultLivros = $stmtLivros->get_result();
$meusLivros = $resultLivros->fetch_all(MYSQLI_ASSOC);

// =====================
// Livros Lidos (marcados manualmente)
// =====================
$sqlLidos = "
    SELECT l.titulo_livro, l.autor_livro, l.genero_livro, ll.data_lido
    FROM livros_lidos ll
    JOIN livros l ON ll.id_livro = l.id_livro
    WHERE ll.id_usuario = ?
    ORDER BY ll.data_lido DESC
";
$stmtLidos = $conn->prepare($sqlLidos);
$stmtLidos->bind_param("i", $id_usuario);
$stmtLidos->execute();
$resultLidos = $stmtLidos->get_result();
$livrosLidos = $resultLidos->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="stylesheet" href="painelusuario.css">-->
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Meus Livros</title>
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
    background-color: white;
    width: 80%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
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

.telalivros {
    background-color: rgb(255, 255, 255);
    width: 100%;
    height: 90%;
    padding: 10px;
    border: 1px solid black;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.telalivros h3 {
    color: rgb(39, 30, 18);
}

.meuslivros {
    background-color: rgb(255, 255, 255);
    width: 98%;
    height: 40%;
    margin: 10px;
    border: 1px solid rgb(197, 197, 197);
    overflow: auto;
}

.historico {
    background-color: rgb(255, 255, 255);
    width: 98%;
    height: 40%;
    margin: 10px;
    border: 1px solid rgb(197, 197, 197);
}


.botao-lido {
    margin-left: 10px;
    padding: 2px 5px;
    font-size: 0.9rem;
    cursor: pointer;
}


.meuslivros, .livroslidos {
    background-color: #fff;
    width: 98%;
    height: 38%;
    margin: 10px;
    border: 1px solid #c5c5c5;
    overflow-y: auto; /* barra de rolagem automática */
    padding: 10px;
    border: 1px solid rgb(39, 30, 18);
    scrollbar-color: #dac7bfff rgb(39, 30, 18);
}

.boxemail {
    display: flex;
    flex-direction: row;
    justify-content: right;
    align-items: center;
    padding-right: 10px;
    padding-top: 10px;
}

.boxemail a {
    width: 140px;
    height: 28px;
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
    text-decoration: none;
    font-weight: bold;
}

.meuslivros h3, .livroslidos h3 {
    margin-bottom: 10px;
}

.botao-lido {
    margin-left: 10px;
    padding: 5px 10px;
    font-size: 0.9rem;
    cursor: pointer;
    background-color: rgb(39, 30, 18);
    color: #fff;
    border-radius: 5px;
    border: 1px solid rgb(182, 182, 181);
}

.botao-lido:hover {
    background-color: rgb(65, 50, 30);
}

.meuslivros p {
    height: 35px;
    margin: 10px;
    display: flex;
    flex-direction: row;
    justify-content: right;
    align-items: center;
    text-align: center;
}

.meuslivros a {
    text-decoration: none;
}

.meuslivros span {
    color: rgba(238, 149, 16, 1);;
    margin: 5px;
}

</style>
</head>
<body>
<main>
<div class="colunaesquerda">
    <div class="boxperfil"><img src="imagens/imguser.png" alt="" width="80%"></div>
    <div class="caixabotoes">
        <a href=""><div class="botaoselect"><h3>Meus Livros</h3></div></a>
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
        <div class="boxnomeuser"><h5>USUÁRIO: <?php echo strtoupper(htmlspecialchars($_SESSION['nome_usuario'])); ?></h5></div>
        <div class="boxpontos">
            <img src="imagens/star.png" alt="" width="20">
            <h3><?php echo number_format($usuario['pontos_usuario'], 0); ?></h3>
        </div>
    </div>
    <div class="boxemail">
        <a class='botao-lido' href="inbox_user.php">MENSAGENS</a>
    </div>

    <!-- LIVROS EMPRESTADOS -->
    <div class="meuslivros">
        <h3>📚 Livros Emprestados</h3>
        <?php
if (!empty($meusLivros)) {
    foreach ($meusLivros as $livro) {
        echo "<p>
                <strong>{$livro['titulo_livro']}</strong>&nbsp - {$livro['autor_livro']} | Devolução prevista: <strong><span class='datadev'>{$livro['data_devolucao']}</strong></span>
                <a href='devolver.php?id_emprestimo={$livro['id_emprestimo']}' class='botao-lido'>Devolver</a></p>";
    }
} else {
    echo "<p>Nenhum livro emprestado no momento.</p>";
}
?>
    </div>

    <!-- LIVROS LIDOS -->
    <div class="livroslidos">
        <h3>📖 Livros Lidos</h3>
        <?php
        if (!empty($livrosLidos)) {
            foreach ($livrosLidos as $lido) {
             echo "<p><strong>{$lido['titulo_livro']}</strong> - {$lido['autor_livro']}&nbsp&#x2713</p>";
            }
        } else {
            echo "<p>Nenhum livro marcado como lido ainda.</p>";
        }
        ?>
    </div>
</div>
</main>
</body>
</html>