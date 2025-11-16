<?php

require('conexao.php');

// Define uma variável para armazenar todo o conteúdo do relatório em texto
$relatorio_txt = "RELATÓRIO DE MOVIMENTAÇÃO DE LIVROS - SCRIPTORIUM\n\n";
$relatorio_txt .= "Gerado em: " . date("d/m/Y H:i:s") . "\n";
$relatorio_txt .= "========================================================\n\n";

// ==============================================
// 2. FUNÇÕES DE CONSULTA E BUSCA DOS DADOS
// ==============================================

/**
 * Função para buscar os dados de Empréstimos ou Devoluções.
 * @param mysqli $conn A conexão com o banco de dados.
 * @param string $tabela Nome da tabela ('emprestimos' ou 'devolucoes').
 * @return array Um array associativo com os resultados.
 */
function buscar_dados($conn, $tabela) {
    $data_coluna = ($tabela == 'emprestimos') ? 'data_emprestimo' : 'data_devolucao';

    $sql = "
        SELECT
            t.$data_coluna AS data_evento,
            u.nome_usuario,
            l.titulo_livro
        FROM
            $tabela t
        JOIN
            usuarios u ON t.id_usuario = u.id_usuario
        JOIN
            livros l ON t.id_livro = l.id_livro
        ORDER BY
            data_evento DESC
    ";

    $resultado = $conn->query($sql);

    $dados = [];
    if ($resultado && $resultado->num_rows > 0) {
        while ($linha = $resultado->fetch_assoc()) {
            $linha['tipo_evento'] = ($tabela == 'emprestimos') ? 'Empréstimo' : 'Devolução';
            $dados[] = $linha;
        }
    }
    return $dados;
}

// Supõe-se que a variável $conn foi criada no 'conexao.php'
if (!isset($conn)) {
    // Apenas um fallback para garantir a conexão (AJUSTE SE NECESSÁRIO)
    $conn = new mysqli("localhost", "seu_usuario", "sua_senha", "bd_scriptorium");
    if ($conn->connect_error) {
        $relatorio_txt .= "ERRO: Falha na conexão com o banco de dados.\n";
        goto output; 
    }
}

$dados_emprestimos = buscar_dados($conn, 'emprestimos');
$dados_devolucoes = buscar_dados($conn, 'devolucoes');

// Combina e ordena os arrays
$dados_combinados = array_merge($dados_emprestimos, $dados_devolucoes);

usort($dados_combinados, function($a, $b) {
    return strtotime($b['data_evento']) - strtotime($a['data_evento']);
});

// **NOVO BLOCO: Lista de Meses em Português**
$meses_pt = array(
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
);

// Array para agrupar por mês
$dados_por_mes = [];

foreach ($dados_combinados as $registro) {
    $timestamp = strtotime($registro['data_evento']);
    $chave_mes = date('Y-m', $timestamp);
    
    // Obtém o número do mês (1 a 12)
    $num_mes = date('n', $timestamp);
    
    // Constrói o nome do mês usando o array de lookup
    $nome_mes = $meses_pt[$num_mes] . ' de ' . date('Y', $timestamp);
    
    if (!isset($dados_por_mes[$chave_mes])) {
        $dados_por_mes[$chave_mes] = [
            'nome_mes' => $nome_mes,
            'registros' => []
        ];
    }
    $dados_por_mes[$chave_mes]['registros'][] = $registro;
}


// ==============================================
// 3. MONTAGEM DO CONTEÚDO TXT
// ==============================================

if (empty($dados_combinados)) {
    $relatorio_txt .= "Nenhuma movimentação de livros registrada no banco de dados.\n";
} else {
    
    // Cabeçalhos da Tabela para TXT (alinhamento manual)
    $relatorio_txt .= str_pad("DATA", 15) . 
                     str_pad("TIPO", 15) . 
                     str_pad("USUÁRIO", 40) . 
                     "LIVRO\n";
    $relatorio_txt .= "-------------------------------------------------------------------------------------------------------\n";

    // Itera sobre os dados agrupados por mês
    foreach ($dados_por_mes as $chave_mes => $grupo_mes) {
        
        // Título do Mês
        $relatorio_txt .= "\n### " . strtoupper($grupo_mes['nome_mes']) . " ###\n\n";

        foreach ($grupo_mes['registros'] as $registro) {
            
            // Formata a data para o padrão brasileiro DD/MM/AAAA
            $data_formatada = date('d/m/Y', strtotime($registro['data_evento']));
            
            // Usa str_pad para formatar e alinhar o texto
            $linha = str_pad($data_formatada, 15) . 
                     str_pad($registro['tipo_evento'], 15) . 
                     str_pad($registro['nome_usuario'], 40) . 
                     $registro['titulo_livro'] . "\n";
                     
            $relatorio_txt .= $linha;
        }
    }
}

// Fecha a conexão (Importante!)
$conn->close();

// ==============================================
// 4. SAÍDA DO ARQUIVO TXT (DOWNLOAD FORÇADO)
// ==============================================
output:
// Define os cabeçalhos HTTP para forçar o download como um arquivo .txt
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="Relatorio_Movimentacao_Scriptorium_' . date('Ymd') . '.txt"');

// Imprime o conteúdo do relatório. O PHP fará o output/download automaticamente.
echo $relatorio_txt;
exit; 
?>