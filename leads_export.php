<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['leads_autorizado']) || $_SESSION['leads_autorizado'] !== true) {
    header('Location: leads_login.php');
    exit;
}

require_once __DIR__ . '/ia/env.php';

$caminho_log_relativo = env('IA_LOG_PATH', 'logs/leads_pp.txt');
$caminho_log = __DIR__ . '/' . ltrim($caminho_log_relativo, '/');

$leads = [];
if (file_exists($caminho_log)) {
    $linhas = file($caminho_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        if (preg_match('/^\[(.*?)\]\s*NOME:\s*(.*?)\s*\|\s*EMPRESA:\s*(.*?)\s*\|\s*ORIGEM:\s*(.*?)$/i', $linha, $m)) {
            $leads[] = [
                'data'    => trim($m[1]),
                'nome'    => trim($m[2]),
                'empresa' => trim($m[3]),
                'origem'  => trim($m[4]),
            ];
        }
    }
}

$nome_arquivo = 'leads_prevpecas_' . date('Y-m-d_H-i') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";

$saida = fopen('php://output', 'w');

fputcsv($saida, ['Data/Hora', 'Nome', 'Empresa', 'Origem'], ';');

foreach ($leads as $lead) {
    fputcsv($saida, [
        $lead['data'],
        $lead['nome'],
        $lead['empresa'],
        $lead['origem'],
    ], ';');
}

fclose($saida);
exit;
