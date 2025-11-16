-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: bd_scriptorium
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `devolucoes`
--

DROP TABLE IF EXISTS `devolucoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devolucoes` (
  `id_devolucao` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(5) unsigned NOT NULL,
  `id_livro` int(5) unsigned NOT NULL,
  `data_devolucao` date NOT NULL,
  `id_emprestimo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_devolucao`),
  KEY `fk_usuario_dev` (`id_usuario`),
  KEY `fk_livro_dev` (`id_livro`),
  CONSTRAINT `fk_livro_dev` FOREIGN KEY (`id_livro`) REFERENCES `livros` (`id_livro`),
  CONSTRAINT `fk_usuario_dev` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devolucoes`
--

LOCK TABLES `devolucoes` WRITE;
/*!40000 ALTER TABLE `devolucoes` DISABLE KEYS */;
INSERT INTO `devolucoes` VALUES (58,3,3,'2025-09-15',30),(59,3,3,'2025-09-15',30),(60,3,3,'2025-09-15',31),(61,3,3,'2025-09-15',31),(62,3,3,'2025-09-15',31),(63,3,3,'2025-09-15',31),(64,4,9,'2025-11-05',37),(65,4,11,'2025-11-05',38);
/*!40000 ALTER TABLE `devolucoes` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_devolucao BEFORE INSERT ON devolucoes
FOR EACH ROW
BEGIN
    DECLARE v_id_livro_emprestado INT;
    DECLARE v_id_usuario_emprestou INT;
    DECLARE v_status_livro INT;
    DECLARE v_valido BOOLEAN DEFAULT FALSE;
    DECLARE mensagem_log VARCHAR(255);
    -- Busca os dados do empréstimo e do livro para validação
    SELECT e.id_livro, e.id_usuario, l.status
    INTO v_id_livro_emprestado, v_id_usuario_emprestou, v_status_livro
    FROM emprestimos e
    JOIN livros l ON e.id_livro = l.id_livro
    WHERE e.id_emprestimo = NEW.id_emprestimo;
    -- Condição para validar a devolução
    IF v_id_usuario_emprestou = NEW.id_usuario AND v_status_livro = 1 THEN
        SET v_valido = TRUE;
    END IF;
    -- Bloco de validação
    IF v_valido THEN
     -- Ação 1: Altera o status do livro para 0 (disponível)
        UPDATE livros SET status = 0 WHERE id_livro = v_id_livro_emprestado;
	-- Ação 2: Adiciona os 250 pontos ao usuário
		UPDATE usuarios SET pontos_usuario = pontos_usuario + 250 WHERE id_usuario = NEW.id_usuario;
        -- Registra o evento de sucesso
        SET mensagem_log = CONCAT('Devolução bem-sucedida do livro (ID ', v_id_livro_emprestado, ') pelo usuário (ID ', NEW.id_usuario, ').');
        INSERT INTO eventos (relatorio) VALUES (mensagem_log);
    ELSE
        -- Registra o evento de erro
        SET mensagem_log = CONCAT('Alerta: O empréstimo não pode ser registrado. O usuário não corresponde ou o livro não está emprestado. ', NEW.id_livro, '/', NEW.id_usuario,'(Livro/Usuário)');
        INSERT INTO eventos (relatorio) VALUES (mensagem_log);
        -- Lança o erro para cancelar a operação
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = mensagem_log;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `emprestimos`
--

DROP TABLE IF EXISTS `emprestimos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emprestimos` (
  `id_emprestimo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(5) unsigned NOT NULL,
  `id_livro` int(5) unsigned NOT NULL,
  `data_emprestimo` date NOT NULL,
  `data_devolucao` date NOT NULL,
  PRIMARY KEY (`id_emprestimo`),
  KEY `fk_usuario` (`id_usuario`),
  KEY `fk_livro` (`id_livro`),
  CONSTRAINT `fk_livro` FOREIGN KEY (`id_livro`) REFERENCES `livros` (`id_livro`),
  CONSTRAINT `fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emprestimos`
--

LOCK TABLES `emprestimos` WRITE;
/*!40000 ALTER TABLE `emprestimos` DISABLE KEYS */;
INSERT INTO `emprestimos` VALUES (30,3,3,'2025-09-15','2025-10-25'),(31,3,3,'2025-09-15','2025-10-25'),(32,3,3,'2025-09-15','2025-10-25'),(33,3,3,'2025-09-15','2025-10-25'),(34,3,3,'2025-09-15','2025-10-25'),(35,3,3,'2025-09-15','2025-10-25'),(36,4,3,'2025-11-06','2025-11-28'),(37,4,9,'2025-11-06','2025-11-24'),(38,4,11,'2025-11-06','2025-11-26'),(39,4,25,'2025-11-06','2026-01-01');
/*!40000 ALTER TABLE `emprestimos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_emprestimo BEFORE INSERT ON emprestimos
FOR EACH ROW
BEGIN
    DECLARE v_status_livro INT;
    DECLARE mensagem_log VARCHAR(255);
    -- Busca o status atual do livro
    SELECT status INTO v_status_livro FROM livros WHERE id_livro = NEW.id_livro;
    -- Validação: O livro deve estar disponível (status = 0)
    IF v_status_livro = 0 THEN
	-- **Ação necessária: Altera o status do livro para 1 (emprestado)**
        UPDATE livros SET status = 1 WHERE id_livro = NEW.id_livro;
        -- Registra o evento de sucesso
        SET mensagem_log = CONCAT('Empréstimo: O Livro (ID ', NEW.id_livro, ') foi emprestado pelo usuário (ID ', NEW.id_usuario, ').');
        INSERT INTO eventos (relatorio) VALUES (mensagem_log);
        
    ELSE
        -- Registra o evento de erro
        SET mensagem_log = CONCAT('Alerta: Livro já emprestado (', NEW.id_livro, NEW.id_usuario, ').');
        INSERT INTO eventos (relatorio) VALUES (mensagem_log);
        -- Lança o erro para cancelar a operação
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = mensagem_log;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `eventos`
--

DROP TABLE IF EXISTS `eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `relatorio` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (133,'Empréstimo: O Livro (ID 3) foi emprestado pelo usuário (ID 3).'),(138,'Devolução bem-sucedida do livro (ID 3) pelo usuário (ID 3).'),(141,'Empréstimo: O Livro (ID 3) foi emprestado pelo usuário (ID 3).'),(142,'Devolução bem-sucedida do livro (ID 3) pelo usuário (ID 3).'),(146,'Empréstimo: O Livro (ID 3) foi emprestado pelo usuário (ID 3).'),(147,'Devolução bem-sucedida do livro (ID 3) pelo usuário (ID 3).'),(149,'Empréstimo: O Livro (ID 3) foi emprestado pelo usuário (ID 3).'),(154,'Devolução bem-sucedida do livro (ID 3) pelo usuário (ID 3).'),(158,'Empréstimo: O Livro (ID 3) foi emprestado pelo usuário (ID 3).'),(159,'Devolução bem-sucedida do livro (ID 3) pelo usuário (ID 3).'),(160,'Empréstimo: O Livro (ID 3) foi emprestado pelo usuário (ID 3).'),(164,'Devolução bem-sucedida do livro (ID 3) pelo usuário (ID 3).'),(167,'Professora Tatiana se juntou ao Scriptorium.'),(168,'Professora Argeli se juntou ao Scriptorium.'),(169,'Professor Bruno se juntou ao Scriptorium.'),(170,'O usuário ana_pereira teve suas informações atualizadas.'),(171,'O usuário carlos_souza teve suas informações atualizadas.'),(172,'O livro Percy Jacksons Greek Heroes teve suas informações atualizadas.'),(175,'O usuário luana_martins teve suas informações atualizadas.'),(176,'O usuário gustavo_almeida foi retirado do Scriptorium.'),(177,'O livro A Song of Ice and Fire foi inserido no Scriptorium.'),(178,'O livro A Song of Ice and Fire foi excluído do Scriptorium.'),(179,'O livro O heroi perdido teve suas informações atualizadas.'),(180,'Alberto se juntou ao Scriptorium.'),(181,'O livro Percy Jacksons Greek Heroes teve suas informações atualizadas.'),(182,'Empréstimo: O Livro (ID 3) foi emprestado pelo usuário (ID 4).'),(183,'O livro O sangue do olimpo teve suas informações atualizadas.'),(184,'Empréstimo: O Livro (ID 9) foi emprestado pelo usuário (ID 4).'),(185,'O livro A marca de Atena teve suas informações atualizadas.'),(186,'Empréstimo: O Livro (ID 11) foi emprestado pelo usuário (ID 4).'),(187,'O livro O sangue do olimpo teve suas informações atualizadas.'),(188,'O usuário Ana Pereira teve suas informações atualizadas.'),(189,'Devolução bem-sucedida do livro (ID 9) pelo usuário (ID 4).'),(190,'O livro A marca de Atena teve suas informações atualizadas.'),(191,'O usuário Ana Pereira teve suas informações atualizadas.'),(192,'Devolução bem-sucedida do livro (ID 11) pelo usuário (ID 4).'),(193,'O livro A Song of Ice and Fire teve suas informações atualizadas.'),(194,'Empréstimo: O Livro (ID 25) foi emprestado pelo usuário (ID 4).');
/*!40000 ALTER TABLE `eventos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livros`
--

DROP TABLE IF EXISTS `livros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `livros` (
  `id_livro` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `isbn_livro` varchar(15) DEFAULT NULL,
  `titulo_livro` varchar(100) NOT NULL,
  `genero_livro` varchar(50) NOT NULL,
  `dificuldade` enum('fácil','intermediário','difícil','muito difícil') NOT NULL DEFAULT 'fácil',
  `formatacao` enum('ruim','ok','boa','ótima') NOT NULL DEFAULT 'ok',
  `autor_livro` varchar(100) NOT NULL,
  `paginas_livro` int(5) unsigned NOT NULL,
  `capa_livro` varchar(10) NOT NULL,
  `idade_minima` int(2) unsigned NOT NULL,
  `pontos_livro` int(5) unsigned NOT NULL,
  `status` tinyint(1) DEFAULT 0,
  `observacao` text DEFAULT NULL,
  PRIMARY KEY (`id_livro`),
  UNIQUE KEY `uk_isbn` (`isbn_livro`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livros`
--

LOCK TABLES `livros` WRITE;
/*!40000 ALTER TABLE `livros` DISABLE KEYS */;
INSERT INTO `livros` VALUES (3,'9781484776438','Percy Jacksons Greek Heroes','','fácil','ok','Rick Riordan',517,'Normal',6,250,1,'Atualizado!'),(4,'9788598078892','Percy Jackson e os Olimpianos - Os arquivos do semideus','','fácil','ok','Rick Riordan',170,'Dura',6,250,0,'Atualizado!'),(5,'9788580573176','Os diarios do semideus','','fácil','ok','Rick Riordan',290,'Dura',6,250,0,NULL),(6,'9788580572476','Percy Jackson e os Olimpianos - Guia Definitivo','','fácil','ok','Rick Riordan',150,'Dura',6,250,0,NULL),(7,'9788537806043','Os tres mosqueteiros','','fácil','ok','Alexandre Dumas',790,'Dura',14,250,0,NULL),(8,'9788537808276','O conde de Monte Cristo','','fácil','ok','Alexandre Dumas',1664,'Dura',14,250,0,NULL),(9,'9788580575958','O sangue do olimpo','','fácil','ok','Rick Riordan',432,'Normal',8,250,0,NULL),(10,'9788580574210','A casa de Hades','','fácil','ok','Rick Riordan',500,'Normal',8,250,0,NULL),(11,'9788580573107','A marca de Atena','','fácil','ok','Rick Riordan',480,'Normal',8,250,0,NULL),(12,'9788580571806','O filho de Netuno','','fácil','ok','Rick Riordan',432,'Normal',8,250,0,NULL),(13,'9788580570083','O Heroi Perdido','','fácil','ok','Rick Riordan',440,'Normal',8,250,0,''),(14,'9788598078397','O ladrao de raios','','fácil','ok','Rick Riordan',400,'Normal',6,250,0,NULL),(15,'9788598078441','O mar de monstros','','fácil','ok','Rick Riordan',300,'Normal',6,250,0,NULL),(16,'9788598078588','A maldição do Tita','','fácil','ok','Rick Riordan',330,'Normal',6,250,0,NULL),(17,'9788598078700','A batalha do labirinto','','fácil','ok','Rick Riordan',400,'Normal',6,250,0,NULL),(18,'9788598078908','O ultimo olimpiano','','fácil','ok','Rick Riordan',385,'Normal',6,250,0,NULL),(19,'9786555606492','O calice dos deuses','','fácil','ok','Rick Riordan',270,'Normal',6,250,0,NULL),(20,'9788551009437','A furia da deusa triplice','','fácil','ok','Rick Riordan',320,'Normal',6,250,0,NULL),(21,'9788580576337','Percy Jackson e os Deuses Gregos','','fácil','ok','Rick Riordan',335,'Dura',6,250,0,NULL),(25,'9788558773107','A Song of Ice and Fire','TESTE','difícil','ok','George R. R. Martin',1000,'DURA',16,0,1,'TESTE');
/*!40000 ALTER TABLE `livros` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_bookinsert AFTER INSERT ON livros
FOR EACH ROW
BEGIN
    DECLARE mensagem_log VARCHAR(255);
    SET mensagem_log = CONCAT('O livro ', NEW.titulo_livro, ' foi inserido no Scriptorium.');
    
    INSERT INTO eventos (relatorio) VALUES (mensagem_log);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_bookupdate AFTER UPDATE ON livros
FOR EACH ROW
BEGIN
    DECLARE mensagem_log VARCHAR(255);
    SET mensagem_log = CONCAT('O livro ', OLD.titulo_livro, ' teve suas informações atualizadas.');
    
    INSERT INTO eventos (relatorio) VALUES (mensagem_log);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_bookdelete AFTER DELETE ON livros
FOR EACH ROW
BEGIN
    DECLARE mensagem_log VARCHAR(255);
    SET mensagem_log = CONCAT('O livro ', OLD.titulo_livro, ' foi excluído do Scriptorium.');
    
    INSERT INTO eventos (relatorio) VALUES (mensagem_log);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `livros_lidos`
--

DROP TABLE IF EXISTS `livros_lidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `livros_lidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_livro` int(11) NOT NULL,
  `data_lido` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_usuario` (`id_usuario`,`id_livro`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livros_lidos`
--

LOCK TABLES `livros_lidos` WRITE;
/*!40000 ALTER TABLE `livros_lidos` DISABLE KEYS */;
INSERT INTO `livros_lidos` VALUES (2,4,9,'2025-11-05'),(3,4,11,'2025-11-05');
/*!40000 ALTER TABLE `livros_lidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensagens`
--

DROP TABLE IF EXISTS `mensagens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensagens` (
  `id_msg_adm` int(11) NOT NULL AUTO_INCREMENT,
  `email_remetente` varchar(100) NOT NULL COMMENT 'Email do Administrador que enviou a mensagem',
  `email_destinatario` varchar(100) NOT NULL COMMENT 'Email do Usuário que recebeu a mensagem',
  `assunto` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_msg_adm`),
  KEY `idx_email_destinatario` (`email_destinatario`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensagens`
--

LOCK TABLES `mensagens` WRITE;
/*!40000 ALTER TABLE `mensagens` DISABLE KEYS */;
INSERT INTO `mensagens` VALUES (6,'leonardo@email.com','ana.pereira@email.com','Livro Solicitado em Estoque','Olá Ana, o exemplar do livro \"1984\" que você havia reservado já está disponível para retirada na biblioteca. Você tem 48 horas para retirá-lo.','2025-11-09 14:18:47'),(7,'leonardo@email.com','ana.pereira@email.com','Seu prazo de empréstimo vence na próxima semana','Lembramos que o prazo de devolução do seu livro atual expira em 7 dias. Para evitar multas, por favor, entregue ou renove o empréstimo online. Atenciosamente, Leonardo.','2025-11-09 14:18:47'),(8,'leonardo@email.com','ana.pereira@email.com','Dúvida Resolvida: Funcionamento do Sistema','Oi Ana, sua dúvida sobre como usar a função de renovação foi respondida. Você pode renovar pelo painel principal, clicando no ícone do relógio ao lado do título. Se precisar de mais ajuda, estamos à disposição.','2025-11-09 14:18:47'),(9,'leonardo@email.com','ana.pereira@email.com','Convite: Clube de Leitura de Novembro','Convidamos você para nosso próximo clube de leitura. O tema será literatura clássica europeia. A reunião será na quarta-feira, às 19h. Aguardamos sua presença!','2025-11-09 14:18:47'),(18,'ana.pereira@email.com','leonardo@email.com','Problemas para Renovar Empréstimo','Olá Leonardo, estou tentando renovar o livro \"Dom Casmurro\" pelo painel, mas o sistema está dando um erro. Poderia verificar se o meu prazo está correto?','2025-11-09 17:20:46'),(19,'ana.pereira@email.com','leonardo@email.com','Sugestão de Compra de Título','Gostaria de sugerir que a biblioteca adquirisse o livro \"O Hobbit\". É um clássico que faria muito sucesso!','2025-11-09 17:20:46'),(20,'ana.pereira@email.com','rachel@email.com','Re: Clube de Leitura de Novembro','Oi Rachel, você conseguiu terminar a leitura do livro para o clube desta semana? Gostei muito do que li até agora e estou ansiosa pela discussão.','2025-11-09 17:21:03'),(21,'ana.pereira@email.com','rachel@email.com','Livro emprestado que você queria','Rachel, consegui pegar na biblioteca o livro \"Cem Anos de Solidão\" que você estava procurando. Te aviso assim que eu terminar!','2025-11-09 17:21:03'),(22,'ana.pereira@email.com','rachel@email.com','Gostou do Livro de Fantasia?','Oi Rachel! Terminei aquele livro de fantasia que te emprestei. O que você achou do final? Achei que o desenvolvimento do personagem principal foi um pouco apressado.','2025-11-09 17:21:25'),(23,'ana.pereira@email.com','rachel@email.com','Evento de Poesia na Biblioteca','A biblioteca vai realizar um evento de leitura de poesia na próxima terça-feira. Você está livre? Seria ótimo se pudéssemos ir juntas!','2025-11-09 17:21:25'),(24,'ana.pereira@email.com','rachel@email.com','Preciso de Ajuda com um Autor','Você se lembra do nome daquele autor de ficção histórica que mencionaste na semana passada? Esqueci de anotar e estou a tentar procurá-lo.','2025-11-09 17:21:25');
/*!40000 ALTER TABLE `mensagens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `ranking_vw`
--

DROP TABLE IF EXISTS `ranking_vw`;
/*!50001 DROP VIEW IF EXISTS `ranking_vw`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `ranking_vw` AS SELECT 
 1 AS `Usuário`,
 1 AS `Pontos`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `suporte`
--

DROP TABLE IF EXISTS `suporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suporte` (
  `id_msg` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_msg`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suporte`
--

LOCK TABLES `suporte` WRITE;
/*!40000 ALTER TABLE `suporte` DISABLE KEYS */;
INSERT INTO `suporte` VALUES (1,3,'leo@email.com','Dúvida','Gostaria de saber quando...','2025-08-30 00:43:11'),(2,2,'sidinei@email.com','Senha','Estou com problema na...','2025-08-30 00:44:41'),(3,1,'rachel@email.com','Pontos','Meus pontos estão...','2025-08-30 00:45:05'),(4,24,'alberto@email.com','Dúvida','Sou novo e preciso de informações...','2025-11-06 04:46:16'),(5,4,'ana.pereira@email.com','Sugestão','Estava pensando que seria interessante...','2025-11-06 04:46:45'),(6,12,'carolina.ferreira@email.com','Pedido','Gostaria de saber quando o livro...','2025-11-06 04:47:34'),(7,14,'isabela.lima@email.com','Melhorias','Notei que o Scriptorium está...','2025-11-06 04:48:53'),(8,11,'bruno.nunes@email.com','Disponibilidade','Faz algum tempo que o livro...','2025-11-06 04:50:08'),(9,0,'alberto@email.com','','[SOLICITAÇÃO] Recuperação de senha.','2025-11-06 01:55:26'),(10,0,'ana.pereira@email.com','','[SOLICITAÇÃO] Recuperação de senha.','2025-11-06 01:56:54');
/*!40000 ALTER TABLE `suporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `nivel_usuario` int(2) DEFAULT 1,
  `nome_usuario` varchar(80) NOT NULL,
  `senha_usuario` varchar(15) NOT NULL,
  `nascimento_usuario` date NOT NULL,
  `email_usuario` varchar(100) NOT NULL,
  `pontos_usuario` int(5) DEFAULT 500,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_email` (`email_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,2,'Rachel','12345','1995-03-15','rachel@email.com',25000),(2,2,'Sidinei','12345','1998-07-22','sidinei@email.com',2500),(3,2,'Leonardo','12345','2005-11-08','leonardo@email.com',4500),(4,1,'Ana Pereira','12345','2005-01-01','ana.pereira@email.com',2000),(5,1,'Carlos Souza','12345','1995-01-01','carlos.souza@email.com',850),(6,1,'Luana Martins','12345','2005-01-01','luana.martins@email.com',1500),(7,1,'rafael_gomes','12345','2008-06-18','rafael.gomes@email.com',250),(8,1,'fernanda_costa','12345','1997-02-12','fernanda.costa@email.com',250),(9,1,'lucas_rocha','12345','2000-05-05','lucas.rocha@email.com',250),(10,1,'julia_oliveira','12345','2012-08-20','julia.oliveira@email.com',250),(11,1,'bruno_nunes','12345','1985-04-03','bruno.nunes@email.com',250),(12,1,'carolina_ferreira','12345','1996-10-28','carolina.ferreira@email.com',250),(13,1,'diego_carvalho','12345','2015-12-01','diego.carvalho@email.com',250),(14,1,'isabela_lima','12345','2003-03-17','isabela.lima@email.com',250),(15,1,'guilherme_rodrigues','12345','1999-07-09','guilherme.rodrigues@email.com',250),(16,1,'aline_pereira','12345','2014-01-20','aline.pereira@email.com',250),(17,1,'thiago_silva','12345','1991-06-14','thiago.silva@email.com',250),(18,1,'beatriz_gomes','12345','2009-08-01','beatriz.gomes@email.com',250),(20,1,'camila_rocha','12345','2011-09-16','camila.rocha@email.com',250),(21,1,'Professora Tatiana','12345','1910-07-20','proftatiana@email.com',250),(22,1,'Professora Argeli','12345','1990-07-20','profargelis@email.com',500),(23,1,'Professor Bruno','12345','1990-07-20','profbruno@email.com',500),(24,1,'Alberto','12345','1980-01-01','alberto@email.com',500);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_novo_user AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    DECLARE mensagem_log VARCHAR(255);
    SET mensagem_log = CONCAT(NEW.nome_usuario, ' se juntou ao Scriptorium.');
    INSERT INTO eventos (relatorio)
    VALUES (mensagem_log);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_userupdate AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    DECLARE mensagem_log VARCHAR(255);
    SET mensagem_log = CONCAT('O usuário ', OLD.nome_usuario, ' teve suas informações atualizadas.');
    
    INSERT INTO eventos (relatorio) VALUES (mensagem_log);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_userdelete AFTER DELETE ON usuarios
FOR EACH ROW
BEGIN
    DECLARE mensagem_log VARCHAR(255);
    SET mensagem_log = CONCAT('O usuário ', OLD.nome_usuario, ' foi retirado do Scriptorium.');
    
    INSERT INTO eventos (relatorio) VALUES (mensagem_log);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping events for database 'bd_scriptorium'
--

--
-- Dumping routines for database 'bd_scriptorium'
--

--
-- Final view structure for view `ranking_vw`
--

/*!50001 DROP VIEW IF EXISTS `ranking_vw`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `ranking_vw` AS select `usuarios`.`nome_usuario` AS `Usuário`,`usuarios`.`pontos_usuario` AS `Pontos` from `usuarios` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-09 14:28:24
