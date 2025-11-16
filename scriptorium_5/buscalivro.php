<?php
session_start();
include 'conexao.php';

// Redireciona para login se não estiver logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$nome_usuario = $_SESSION['nome_usuario'] ?? 'Usuário';
$paginas_por_dia = $_SESSION['paginas_por_dia'] ?? 20;

// ------------------- CANCELAR EMPRÉSTIMO -------------------
if (isset($_POST['cancelar_emprestimo'])) {
    $id_livro = intval($_POST['cancelar_emprestimo']);

    $delete = $conn->prepare("DELETE FROM emprestimos WHERE id_livro = ? AND id_usuario = ?");
    $delete->bind_param("ii", $id_livro, $id_usuario);
    $delete->execute();

    $update = $conn->prepare("UPDATE livros SET status = 0 WHERE id_livro = ?");
    $update->bind_param("i", $id_livro);
    $update->execute();

    $mensagem = "Empréstimo cancelado com sucesso!";
}

// ------------------- EMPRESTAR LIVRO -------------------
if (isset($_POST['confirmar_emprestimo'])) {
    $id_livro = intval($_POST['id_livro']);
    $data_hoje = date('Y-m-d');

    $stmt = $conn->prepare("SELECT titulo_livro, paginas_livro, dificuldade, status FROM livros WHERE id_livro = ?");
    $stmt->bind_param("i", $id_livro);
    $stmt->execute();
    $res = $stmt->get_result();
    $livro = $res->fetch_assoc();

    if ($livro && $livro['status'] == 0) {
        $tempo_dias = ceil(($livro['paginas_livro'] / $paginas_por_dia) * $livro['dificuldade']);
        $data_devolucao = date('Y-m-d', strtotime("+$tempo_dias days"));

        $insert = $conn->prepare("INSERT INTO emprestimos (id_usuario, id_livro, data_emprestimo, data_devolucao) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiss", $id_usuario, $id_livro, $data_hoje, $data_devolucao);
        $insert->execute();

        $update = $conn->prepare("UPDATE livros SET status = 1 WHERE id_livro = ?");
        $update->bind_param("i", $id_livro);
        $update->execute();

        $mensagem = "Livro emprestado com sucesso! Data prevista de devolução: $data_devolucao";
    } else {
        $mensagem = "Livro indisponível no momento.";
    }
}

// ------------------- MARCAR / DESMARCAR COMO LIDO -------------------
if (isset($_POST['marcar_lido'])) {
    $id_livro = intval($_POST['marcar_lido']);
    $data_hoje = date('Y-m-d');

$stmt = $conn->prepare("INSERT INTO livros_lidos (id_usuario, id_livro, data_lido) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $id_usuario, $id_livro);
    $stmt->execute();
}

if (isset($_POST['desmarcar_lido'])) {
    $id_livro = intval($_POST['desmarcar_lido']);

$stmt = $conn->prepare("DELETE FROM livros_lidos WHERE id_usuario = ? AND id_livro = ?");
    $stmt->bind_param("ii", $id_usuario, $id_livro);
    $stmt->execute();
}

// ------------------- BUSCA DE LIVROS -------------------
$busca = $_POST['busca_livro'] ?? '';
$filtro_leitura = $_POST['filtro_leitura'] ?? 'todos';
$filtro_capa = $_POST['filtro_capa'] ?? 'todas';
$idade_usuario = $_POST['idade_usuario'] ?? null;
if($idade_usuario !== null){
    $idade_usuario = intval($idade_usuario);
}

$query = "SELECT l.*, 
            (SELECT 1 FROM livros_lidos ll WHERE ll.id_livro = l.id_livro AND ll.id_usuario = ?) AS ja_lido 
          FROM livros l";

$params = [$id_usuario];
$tipos = "i";

$where = [];
if (!empty($busca)) {
    $where[] = "(l.titulo_livro LIKE CONCAT('%',?,'%') 
                 OR l.autor_livro LIKE CONCAT('%',?,'%')
                 OR l.genero_livro LIKE CONCAT('%',?,'%'))";
    $params[] = $busca;
    $params[] = $busca;
    $params[] = $busca;
    $tipos .= "sss";
}

if ($filtro_capa !== 'todas') {
    $where[] = "l.capa_livro = ?";
    $params[] = $filtro_capa;
    $tipos .= "s";
}

if($idade_usuario){
    $where[] = "l.idade_minima <= ?";
    $params[] = $idade_usuario;
    $tipos .= "i";
}

if ($where) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$stmt = $conn->prepare($query);
$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<link rel="shortcut icon" href="imagens/favicon.png" type="image">
<title>Scriptorium: Buscar Livros</title>
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

.colunaesquerdap { color: rgb(202, 181, 154); }

.colunaesquerda img {
    border:3px dotted rgb(156, 133, 103);
    border-radius: 65px;
    box-shadow: 0px 0px 5px rgb(100, 70, 46);
}

.boxperfil { margin-left: 9px; }

.caixabotoes {
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    align-items: center;
    text-align: center;
}

.caixabotoes a { width: 100%; text-decoration: none; }

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

.busca {
    background-color: rgb(255, 255, 255);
    width: 98%;
    height: 20%;
    margin: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.busca button {
    margin-left: 10px;
    background-color: white;
    border: none;
    cursor: pointer;

}

input {
    width: 500px;
    height: 30px;
    margin: 10px;
    border-radius: 20px;
    padding: 15px;
}

.resultado {
    background-color: white;
    width: 98%;
    height: 350px;
    margin: 10px;
    border: 1px solid rgb(197,197,197);
    overflow-y: auto;
    scrollbar-color: #dac7bfff rgb(39, 30, 18);
}

select {
    background-color: rgb(39, 30, 18);
    color: white;
    font-weight: bold;
}

select option {
    font-weight: bold;
}

.resultado button {
    width: 100px;
    margin-left: 10px;
    margin-bottom: 5px;
    padding: 5px 10px;
    font-size: 0.9rem;
    cursor: pointer;
    background-color: rgb(39, 30, 18);
    color: #fff;
    border-radius: 5px;
    border: 1px solid rgb(182, 182, 181);
}

.resultado button:hover {
    width: 100px;
    margin-left: 10px;
    margin-bottom: 5px;
    padding: 5px 10px;
    font-size: 0.9rem;
    cursor: pointer;
    background-color: rgba(63, 49, 30, 1);
    color: #fff;
    border-radius: 5px;
    border: 1px solid rgb(182, 182, 181);
}

.barra-pesquisa {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
}

.barra-pesquisa input[type="text"] {
    flex: 1;             
    height: 40px;        
    border-radius: 10px;
    padding: 0 15px;
    font-size: 1rem;
}

.filtros-empilhados {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filtros-empilhados select {
    width: 130px;       /* menor e discreto */
    height: 28px;
    border-radius: 6px;
    padding: 2px 5px;
    background-color: rgb(39,30,18);
    color: white;
    border: 1px solid #ccc;
    font-size: 0.85rem;
}

.barra-pesquisa button {
    height: 30px;       
    width: 30px;        
    border: none;
    cursor: pointer;
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}

/* Tabela */
table { width:100%; border-collapse:collapse; font-size:14px; }
th, td { border:1px solid #ccc; padding:8px; text-align:left; }
th { background-color: rgb(39,30,18); color:white; }
.resultado tr:nth-child(odd) { background-color: #C6B2A9; }
.resultado tr:nth-child(even) { background-color: #f2f2f2; }
</style>
</head>
<body>
<main>
    <!-- COLUNA ESQUERDA -->
    <div class="colunaesquerda">
        <div class="boxperfil"><img src="imagens/imguser.png" alt="" width="80%"></div>
        <div class="caixabotoes">
            <a href="painelusuario.php"><div class="botao"><h3>Meus Livros</h3></div></a>
            <a href="buscalivro.php"><div class="botaoselect"><h3>Buscar Livros</h3></div></a>
            <a href="ranking.php"><div class="botao"><h3>Ranking</h3></div></a>
            <a href="suporte.php"><div class="botao"><h3>Suporte</h3></div></a>
            <a href="logout.php"><div class="botao"><h3>Sair</h3></div></a>
        </div>
        <div class="colunaesquerdap"><p>.</p></div> 
        <div class="colunaesquerdap"><p>.</p></div>           
        <div><p>Versão 1.0</p></div>
    </div>

    <!-- COLUNA DIREITA -->
    <div class="colunadireita">
        <div class="linhasuperior">
            <div class="boxnomeuser"><h5>USUÁRIO: <?php echo strtoupper(htmlspecialchars($_SESSION['nome_usuario'])); ?></h5></div>
            <div class="boxpontos">
                <img src="imagens/star.png" alt="" width="20">
                <h3><?php echo number_format($_SESSION['pontos_usuario'], 0); ?></h3>
            </div>
        </div>

        <div class="telabusca">
            <form method="post">
              <div class="barra-pesquisa">
    <input type="text" name="busca_livro" placeholder="Digite título, autor ou gênero..." value="<?= htmlspecialchars($busca) ?>">
    <!-- Barra menor para idade -->
    <input type="number" name="idade_usuario" placeholder="Idade" value="<?= htmlspecialchars($_POST['idade_usuario'] ?? '') ?>" style="width:60px; height:28px; padding:2px 2px; margin-left:5px; border-radius:6px;">


    <div class="filtros-empilhados">
        <select name="filtro_capa">
            <option value="todas" <?= ($_POST['filtro_capa'] ?? 'todas')=='todas'?'selected':'' ?>>Todas as capas</option>
            <option value="normal" <?= ($_POST['filtro_capa'] ?? '')=='normal'?'selected':'' ?>>Normal</option>
            <option value="dura" <?= ($_POST['filtro_capa'] ?? '')=='dura'?'selected':'' ?>>Dura</option>
        </select>

        <select name="filtro_leitura">
            <option value="todos" <?= ($filtro_leitura=='todos')?'selected':'' ?>>Todos os livros</option>
            <option value="lidos" <?= ($filtro_leitura=='lidos')?'selected':'' ?>>Já lidos</option>
            <option value="nao_lidos" <?= ($filtro_leitura=='nao_lidos')?'selected':'' ?>>Não lidos</option>
        </select>
    </div>

    <button type="submit" name="buscar">
        <img src="imagens/buscarimg.png" alt="Buscar" style="width:30px; height:30px;">
    </button>
</div>

            </form>

            <div class="resultado">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Gênero</th>
                        <th>Dificuldade</th>
                        <th>Autor</th>
                        <th>Páginas</th>
                        <th>Idade Mínima</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php
                        // Aplica filtro de leitura
                        $ja_lido = $row['ja_lido'] ?? 0;
                        if($filtro_leitura=='lidos' && !$ja_lido) continue;
                        if($filtro_leitura=='nao_lidos' && $ja_lido) continue;

                        // Verifica se o usuário já pegou o livro
                        $stmt2 = $conn->prepare("SELECT * FROM emprestimos WHERE id_livro=? AND id_usuario=?");
                        $stmt2->bind_param("ii", $row['id_livro'], $id_usuario);
                        $stmt2->execute();
                        $res2 = $stmt2->get_result();
                        $emprestimo_ativo = $res2->num_rows>0 && $row['status']==1;
                        ?>
                        <tr>
                            <td><?= $row['id_livro'] ?></td>
                            <td><?= $row['titulo_livro'] ?></td>
                            <td><?= $row['genero_livro'] ?? '-' ?></td>
                            <td><?= $row['dificuldade'] ?></td>
                            <td><?= $row['autor_livro'] ?></td>
                            <td><?= $row['paginas_livro'] ?></td>
                            <td><?= $row['idade_minima'] ?></td>
                            <td><?= $row['status']==0?'Disponível':'Emprestado' ?></td>
                            <td>
                                <?php if($row['status']==0 && !$emprestimo_ativo): ?>
                                    <a href="emprestar_livro.php?id_livro=<?= $row['id_livro'] ?>" target="_blank">
                                        <button>Emprestar</button>
                                    </a>
                                <?php elseif($emprestimo_ativo): ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Deseja realmente cancelar este empréstimo?');">
                                        <input type="hidden" name="cancelar_emprestimo" value="<?= $row['id_livro'] ?>">
                                        <button type="submit">Cancelar</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>

                                <?php if(!$ja_lido): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="marcar_lido" value="<?= $row['id_livro'] ?>">
                                        <button type="submit"> Marcar lido </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Deseja realmente desmarcar este livro como lido?');">
                                        <input type="hidden" name="desmarcar_lido" value="<?= $row['id_livro'] ?>">
                                        <button type="submit">Desmarcar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>