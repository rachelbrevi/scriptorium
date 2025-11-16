<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Ajuste: removemos 'perfil' e usamos 'nivel_usuario'
    $stmt = $conn->prepare("SELECT id_usuario, nome_usuario, pontos_usuario, nivel_usuario FROM usuarios WHERE email_usuario = ? AND senha_usuario = ?");
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
        $_SESSION['pontos_usuario'] = $usuario['pontos_usuario'];
        $_SESSION['nivel_usuario'] = $usuario['nivel_usuario'];

        // Redirecionamento baseado no nível do usuário
        if ($usuario['nivel_usuario'] == 2) { // admin
            header("Location: paineladm.php");
            exit;
        } else { // usuário normal
            header("Location: painelusuario.php");
            exit;
        }
    } else {
        $erro = "Email ou senha incorretos!";
    }
}

?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="stylesheet" href="login.css">-->
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');

* {
    padding: 0px;
    margin: 0px;
    box-sizing: border-box;
    font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
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
    width: 750px;
    height: 420px;
    background-image: url(imagens/bk_book.jpg);
    background-size: cover;
    display: flex;
    flex-direction: row;
    border: 4px solid rgb(37, 37, 37);
    outline: 2px dotted rgb(230, 203, 168);
}

.esquerda {
    flex: 3;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
}

.box_nome {
    background-color: rgba(34, 22, 12, 0.884);
    height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    font-size: 15px;
    color: azure;
    align-items: center;
    padding: 5px;
}

.box_nome h1 {
    font-family: "Gloock", serif;
    font-weight: 400;
    font-style: normal;
}

.box_nome p {
    margin: 10px;
    font-size: 12px;
}

.direita {
    flex: 2;
    background-image: url(imagens/bk_login.png);
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    color: rgba(34, 22, 12);
}

form {
    display: flex;
    flex-direction: column;
    text-align: left;
    padding: 20px;
}

form input {
    padding: 5px;
    height: 30px;
    border-radius: 5px;
    border-color: rgb(56, 25, 10);
    border-style: solid;
}

form input:focus {
    outline: none;
}

form button {
    margin: 10px;
    width: 90%;
    height: 30px;
    color: white;
    border-radius: 5px;
    border-color: rgb(56, 25, 10);
    border-style: solid;
    background-color: rgba(34, 22, 12);
}

.login_topo {
    color: rgb(255, 255, 255);
    text-shadow: 0px 0px 10px rgb(56, 25, 10);
}

.direita a {
    color: rgb(255, 255, 255);
    text-shadow: 0px 0px 5px black;
    text-decoration: none;
}
    </style>
</head>
<body>
<main>
    <div class="esquerda">
        <div class="box_nome">
            <img src="imagens/linha.png" alt="" width="25%">
            <h1>Scriptorium</h1>
            <img src="imagens/linha_i.png" alt="" width="25%">
            <p>Um leitor vive mil vidas antes de morrer. O homem que nunca lê vive apenas uma. — George R. R. Martin</p>
        </div>
    </div>
    <div class="direita">
        <h1 class="login_topo">LOGIN</h1>
        <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
        <form method="POST">
            <label>e-mail</label>
            <input type="email" name="email" required>
            <label>senha</label>
            <input type="password" name="senha" required>
            <button type="submit">ENTRAR</button>
        </form>
        <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se!</a></p>
        <p><a href="recuperarsenha.php">Esqueci minha senha.</a></p>
    </div>
</main>
</body>
</html>