<?php
session_start();
session_unset(); // remove todas as variáveis de sessão
session_destroy(); // destrói a sessão
header("Location: login.php"); // redireciona para a página de login
exit;
?>