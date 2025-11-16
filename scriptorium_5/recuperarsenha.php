<?php

require 'conexao.php';

$mensagem_feedback = '';
$sucesso_acao = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email_digitado = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    $texto_padrao = "[SOLICITAÇÃO] Recuperação de senha.";
    $id_padrao = 0; 
    $assunto_vazio = ""; 
    
    $sql = "INSERT INTO suporte (id_usuario, email, assunto, conteudo, data_envio) VALUES (?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    

    $stmt->bind_param("isss", $id_padrao, $email_digitado, $assunto_vazio, $texto_padrao);
    
    $stmt->execute();
    
    $stmt->close(); 
    
    $mensagem_feedback = "Ação processada com sucesso!";
    $sucesso_acao = true;
}
?>
<!DOCTYPE html>
<html lang="PT-BR">

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="imagens/favicon.png" type="image/x-icon">
    <title>Scriptorium: Recuperar Senha</title>
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
    justify-content: center;
    text-align: center;
    color: rgba(34, 22, 12);
    box-shadow: 0px 0px 5px rgb(255, 255, 255);
}

.box_cadastro h2 {
    color: rgb(255, 255, 255);
    text-shadow: 0px 0px 10px rgb(56, 25, 10);
    margin: 10px;
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

form a {
    text-decoration: none;
}
    </style>
</head>
<body>
    <main>
        <div class="box_cadastro">
            <h2>RECUPERAR SENHA</h2>
            
            <?php if (!empty($mensagem_feedback)): ?>
                <div id="feedback-mensagem" style="
                    margin: 10px;
                    padding: 8px; 
                    border: 1px solid <?php echo $sucesso_acao ? 'green' : 'red'; ?>; 
                    color: <?php echo $sucesso_acao ? 'green' : 'red'; ?>;
                    font-size: 0.9em;
                    background-color: <?php echo $sucesso_acao ? '#e6ffe6' : '#ffe6e6'; ?>;
                    border-radius: 5px;">
                    <?php echo $mensagem_feedback; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="post">
                <label>E-mail</label>
                <input type="email" name="email" required>

                <button type="submit">SOLICITAR</button>
                <p style="text-align: center; margin-top: 10px;"><a href="login.php" style="color: rgba(34, 22, 12);">Voltar para o Login</a></p>
            </form>
        </div>
    </main>
</body>
</html>