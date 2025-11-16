<?php
session_start();
include 'conexao.php';

// Verifica se o admin está logado
if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel_usuario'] != 2) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$livro = [];

// Quando o admin busca um livro pelo ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM livros WHERE id_livro = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $livro = $result->fetch_assoc();
}

// Quando o admin envia o formulário para atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_livro'];
    $titulo = $_POST['titulo_livro'];
    $autor = $_POST['autor_livro'];
    $genero = $_POST['genero_livro'];
    $paginas = $_POST['paginas_livro'];
    $idade = $_POST['idade_minima'];
    $capa = $_POST['capa_livro'];
    $isbn = $_POST['isbn_livro'];
    $obs = $_POST['observacao'];
    $dificuldade = $_POST['dificuldade'];
    $formatacao = $_POST['formatacao'];

    // Corrigido bind_param: tipos corretos
    $sql = "UPDATE livros 
            SET titulo_livro=?, autor_livro=?, genero_livro=?, paginas_livro=?, idade_minima=?, capa_livro=?, isbn_livro=?, observacao=?, dificuldade=?, formatacao=? 
            WHERE id_livro=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssiisssssi", // i -> paginas, i -> idade_minima, s -> capa, s -> isbn, s -> observacao, s -> dificuldade, s -> formatacao, i -> id
        $titulo, $autor, $genero, $paginas, $idade, $capa, $isbn, $obs, $dificuldade, $formatacao, $id
    );

    if ($stmt->execute()) {
        $mensagem = "✅ Livro atualizado com sucesso!";
    } else {
        $mensagem = "❌ Erro ao atualizar o livro.";
    }

    // Atualiza os dados do livro no formulário
    $livro['titulo_livro'] = $titulo;
    $livro['autor_livro'] = $autor;
    $livro['genero_livro'] = $genero;
    $livro['paginas_livro'] = $paginas;
    $livro['idade_minima'] = $idade;
    $livro['capa_livro'] = $capa;
    $livro['isbn_livro'] = $isbn;
    $livro['observacao'] = $obs;
    $livro['dificuldade'] = $dificuldade;
    $livro['formatacao'] = $formatacao;
}
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="stylesheet" href="alterar.css">-->
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Alterar Livro</title>
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
    width: 50px;
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
    width: 432px;
    background-color: #C6B2A9;
    border: 2px solid rgb(59, 50, 35);
    box-shadow: 0px 0px 2px rgb(104, 88, 61);
}

.autorinput {
    width: 680px;
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
    width: 60px; 
    height: 60px; 
    border-radius: 50%;
    background-color: rgb(54, 42, 26);
    color: azure;
    border: 1px solid rgb(255, 255, 255);
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
    margin-top: 45px;
    display: flex;
    justify-content: center;
    padding-top: 9px;
}

.telaconteudo button:hover {
    width: 60px; 
    height: 60px; 
    border-radius: 50%;
    background-color: rgb(70, 55, 36);
    color: azure;
    border: 1px solid rgb(255, 255, 255);
    box-shadow: 0px 0px 5px rgb(59, 50, 35);
    margin-top: 45px;
    display: flex;
    justify-content: center;
    padding-top: 9px;
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
    margin-right: 32px;
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
                <h5>ADM: <?php echo strtoupper(htmlspecialchars($_SESSION['nome_usuario'])); ?></h5></div>
            <div class="boxpontos">
                <img src="imagens/star.png" alt="" width="20">
                <h3><?php echo number_format($_SESSION['pontos_usuario'], 0); ?></h3>
            </div>
        </div>

        <div class="tela">
            <div class="telasuperior">
                <div><h2>Alterar Livro</h2></div>
                <div><a href="paineladm.php"><h3 class="botaovoltar">Voltar</h3></a></div>
            </div>

            <div class="telaconteudo">
                <?php if ($mensagem): ?>
                    <p style="color:green;"><?php echo $mensagem; ?></p>
                <?php endif; ?>

                <form method="POST">
                    <div class="boxalinhamento">
                        <div class="box2">
                            <label class="labelcss2">ID</label>
                            <input type="number" name="id_livro" class="tituloinput" value="<?php echo $livro['id_livro'] ?? ''; ?>" required>
                        </div>
                        <div class="box1">
                            <label class="labelcss">Título</label>
                            <input type="text" name="titulo_livro" class="tituloinput" value="<?php echo $livro['titulo_livro'] ?? ''; ?>" required>
                        </div>
                        <div class="box1">
                            <label class="labelcss">Gênero</label>
                            <input type="text" name="genero_livro" value="<?php echo $livro['genero_livro'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="box1">
                        <label class="labelcss">Autor</label>
                        <input type="text" name="autor_livro" class="autorinput" value="<?php echo $livro['autor_livro'] ?? ''; ?>">
                    </div>

                    <div class="boxalinhamento">
                        <div class="box1">
                            <label class="labelcss">Páginas</label>
                            <input type="number" name="paginas_livro" value="<?php echo $livro['paginas_livro'] ?? ''; ?>">
                        </div>
                        <div class="box1">
                            <label class="labelcss">Idade Min.</label>
                            <input type="number" name="idade_minima" value="<?php echo $livro['idade_minima'] ?? ''; ?>">
                        </div>
                        <div class="box1">
                            <label class="labelcss">Capa</label>
                            <input type="text" name="capa_livro" value="<?php echo $livro['capa_livro'] ?? ''; ?>">
                        </div>
                        <div class="box1">
                            <label class="labelcss">ISBN</label>
                            <input type="text" name="isbn_livro" value="<?php echo $livro['isbn_livro'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="boxalinhamento">
                        <div class="box1">
                            <label class="labelcss">Dificuldade</label>
                            <select name="dificuldade" class="botaoselect">
                                <?php
                                $opcoesDificuldade = ['fácil','intermediário','difícil','muito difícil'];
                                foreach($opcoesDificuldade as $op) {
                                    $sel = ($livro['dificuldade'] ?? '') === $op ? 'selected' : '';
                                    echo "<option value='$op' $sel>$op</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="box1">
                            <label class="labelcss">Formatação</label>
                            <select name="formatacao" class="botaoselect">
                                <?php
                                $opcoesFormatacao = ['ruim','ok','boa','ótima'];
                                foreach($opcoesFormatacao as $op) {
                                    $sel = ($livro['formatacao'] ?? '') === $op ? 'selected' : '';
                                    echo "<option value='$op' $sel>$op</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="boxalinhamento">
                        <div class="box1">
                            <label class="labelcsstxt">Observações</label>
                            <textarea name="observacao"><?php echo $livro['observacao'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit"><h1>></h1></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>