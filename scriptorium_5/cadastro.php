<?php
include 'conexao.php'; // conexão com o BD

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pegar valores do formulário
    $nome = $_POST['nome'];
    $nascimento = $_POST['nascimento'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Preparar insert
    $stmt = $conn->prepare("INSERT INTO usuarios (nome_usuario, nascimento_usuario, email_usuario, senha_usuario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $nascimento, $email, $senha);

    if ($stmt->execute()) {
        echo "<script>alert('Cadastro realizado com sucesso!'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Erro no cadastro: ".$stmt->error."');</script>";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="stylesheet" href="cadastro.css">-->
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Cadastro</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');

* {
    padding: 0;
    margin: 0;
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
    background-image: url(imagens/bk_book_2.png);
    background-size: cover;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border: 4px solid rgb(37, 37, 37);
    outline: 2px dotted rgb(230, 203, 168);
}

.box_cadastro {
    width: 40%;
    height: 100%;
    background-image: url(imagens/bk_login.png);
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    text-align: center;
    color: rgba(34, 22, 12);
    box-shadow: 0px 0px 5px rgb(255, 255, 255);
}

.box_cadastro h1 {
    color: rgb(255, 255, 255);
    text-shadow: 0px 0px 10px rgb(56, 25, 10);
}

form {
    display: flex;
    flex-direction: column;
    text-align: left;
    padding: 10px;
}

form input {
    padding: 5px;
    height: 30px;
    border-radius: 5px;
    border: 2px solid rgb(56, 25, 10);
    margin-bottom: 10px;
}

form button {
    margin-top: 10px;
    width: 100%;
    height: 35px;
    color: white;
    border-radius: 5px;
    border: 2px solid rgb(56, 25, 10);
    background-color: rgba(34, 22, 12);
    cursor: pointer;
}

form button:hover {
    background-color: rgb(54, 42, 26);
}
    </style>
</head>
<body>
    <main>
        <div class="box_cadastro">
            <h1>CRIAR CONTA</h1>
            <form action="" method="post">
                <label>Nome Completo</label>
                <input type="text" name="nome" required>

                <label>Data de Nascimento</label>
                <input type="date" name="nascimento" required>

                <label>E-mail</label>
                <input type="email" name="email" required>

                <label>Senha</label>
                <input type="password" name="senha" required>

                <button type="submit">CRIAR</button>
            </form>
        </div>
    </main>
</body>
</html>