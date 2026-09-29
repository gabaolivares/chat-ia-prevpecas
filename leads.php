<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['leads_autorizado']) || $_SESSION['leads_autorizado'] !== true) {
    header('Location: leads_login.php');
    exit;
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: leads_login.php');
    exit;
}

require_once __DIR__ . '/ia/env.php';

$caminho_log_relativo = env('IA_LOG_PATH', 'logs/leads_prevpecas.txt');
$caminho_log = __DIR__ . '/' . ltrim($caminho_log_relativo, '/');

$leads = [];
if (file_exists($caminho_log)) {
    $linhas = file($caminho_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $linhas = array_reverse($linhas);

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

$busca = trim($_GET['busca'] ?? '');
$filtro_origem = trim($_GET['origem'] ?? '');

if ($busca !== '') {
    $busca_lower = mb_strtolower($busca);
    $leads = array_filter($leads, function($l) use ($busca_lower) {
        return mb_strpos(mb_strtolower($l['nome']), $busca_lower) !== false
            || mb_strpos(mb_strtolower($l['empresa']), $busca_lower) !== false
            || mb_strpos(mb_strtolower($l['origem']), $busca_lower) !== false;
    });
}

if ($filtro_origem !== '') {
    $leads = array_filter($leads, function($l) use ($filtro_origem) {
        return mb_strtolower($l['origem']) === mb_strtolower($filtro_origem);
    });
}

$total_leads = count($leads);

$origens_unicas = [];
if (file_exists($caminho_log)) {
    $todas = file($caminho_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($todas as $linha) {
        if (preg_match('/ORIGEM:\s*(.*?)$/i', $linha, $m)) {
            $origem = trim($m[1]);
            if ($origem !== '') {
                $origens_unicas[$origem] = true;
            }
        }
    }
}
$origens_unicas = array_keys($origens_unicas);
sort($origens_unicas);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Leads — Prev-Peças</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --prev-primaria: #0a3d62;
            --prev-primaria-clara: #1e5f8c;
            --prev-destaque: #e55039;
            --prev-destaque-clara: #f4715c;
            --prev-bg-janela: #ffffff;
            --prev-bg-chat: #f4f6f9;
            --prev-texto-escuro: #2f3640;
            --prev-borda: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background: var(--prev-bg-chat);
            color: var(--prev-texto-escuro);
            min-height: 100vh;
            padding: 30px;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 250px;
            background: linear-gradient(135deg, var(--prev-primaria) 0%, var(--prev-primaria-clara) 100%);
            z-index: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
            color: #fff;
        }

        .header-text h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-text h1 span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(229, 80, 57, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.3);
            font-size: 20px;
            box-shadow: 0 6px 15px rgba(229, 80, 57, 0.4);
        }
        .header-text p {
            color: rgba(255, 255, 255, 0.75);
            font-size: 13px;
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .btn-csv {
            background: var(--prev-destaque);
            color: #fff;
            box-shadow: 0 6px 15px rgba(229, 80, 57, 0.4);
        }
        .btn-csv:hover {
            background: var(--prev-destaque-clara);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(229, 80, 57, 0.5);
        }

        .btn-sair {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .btn-sair:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: var(--prev-bg-janela);
            border-radius: 14px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(10, 61, 98, 0.08);
            transition: all 0.3s ease;
            border: 1px solid var(--prev-borda);
        }
        .stat-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 5px; height: 100%;
            background: linear-gradient(180deg, var(--prev-primaria-clara), var(--prev-primaria));
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(10, 61, 98, 0.15);
        }
        .stat-card h4 {
            color: #7f8c8d;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .stat-card .valor {
            color: var(--prev-primaria);
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .filtros {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            background: var(--prev-bg-janela);
            padding: 18px;
            border-radius: 14px;
            box-shadow: 0 6px 15px rgba(10, 61, 98, 0.06);
            border: 1px solid var(--prev-borda);
        }
        .filtros input,
        .filtros select {
            flex: 1;
            min-width: 200px;
            padding: 12px 16px;
            background: var(--prev-bg-chat);
            border: 2px solid var(--prev-borda);
            border-radius: 10px;
            color: var(--prev-texto-escuro);
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }
        .filtros input:focus,
        .filtros select:focus {
            border-color: var(--prev-primaria-clara);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(30, 95, 140, 0.1);
        }
        .filtros button {
            padding: 12px 26px;
            background: var(--prev-primaria);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 15px rgba(10, 61, 98, 0.25);
        }
        .filtros button:hover {
            background: var(--prev-primaria-clara);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(10, 61, 98, 0.35);
        }

        .table-wrap {
            background: var(--prev-bg-janela);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(10, 61, 98, 0.08);
            border: 1px solid var(--prev-borda);
        }

        table { width: 100%; border-collapse: collapse; }
        th {
            background: linear-gradient(135deg, var(--prev-primaria) 0%, var(--prev-primaria-clara) 100%);
            color: #fff;
            padding: 16px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }
        td {
            padding: 15px 16px;
            font-size: 14px;
            color: var(--prev-texto-escuro);
            border-bottom: 1px solid var(--prev-borda);
        }
        tbody tr { transition: 0.2s; }
        tbody tr:hover { background: rgba(30, 95, 140, 0.04); }
        tbody tr:last-child td { border-bottom: none; }

        .origem-tag {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            background: rgba(229, 80, 57, 0.1);
            color: var(--prev-destaque);
            font-size: 12px;
            font-weight: 700;
            border: 1px solid rgba(229, 80, 57, 0.25);
        }

        .vazio {
            text-align: center;
            padding: 70px 20px;
            color: #7f8c8d;
        }
        .vazio .icon { font-size: 56px; margin-bottom: 18px; opacity: 0.4; }
        .vazio h3 { color: var(--prev-primaria); font-size: 19px; margin-bottom: 10px; font-weight: 700; }
        .vazio p { font-size: 13px; color: #95a5a6; }

        @media (max-width: 700px) {
            body { padding: 15px; }
            body::before { height: 320px; }
            header { flex-direction: column; align-items: flex-start; }
            .header-text h1 { font-size: 20px; }
            .header-text h1 span { width: 34px; height: 34px; font-size: 16px; }
            th, td { padding: 10px 8px; font-size: 12px; }
            .stat-card .valor { font-size: 22px; }
            .filtros { padding: 14px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <header>
            <div class="header-text">
                <h1><span>📊</span> Painel de Leads</h1>
                <p>Leads capturados pela IA PrevPeças</p>
            </div>
            <div class="header-actions">
                <a href="leads_export.php" class="btn btn-csv">⬇️ Baixar CSV</a>
                <a href="?logout=1" class="btn btn-sair">🚪 Sair</a>
            </div>
        </header>

        <div class="stats">
            <div class="stat-card">
                <h4>Total de Leads</h4>
                <div class="valor"><?= count($leads) ?></div>
            </div>
            <div class="stat-card">
                <h4>Origem Principal</h4>
                <div class="valor" style="font-size: 18px;">
                    <?= !empty($origens_unicas) ? htmlspecialchars($origens_unicas[0]) : '—' ?>
                </div>
            </div>
            <div class="stat-card">
                <h4>Último Lead</h4>
                <div class="valor" style="font-size: 18px;">
                    <?= !empty($leads) ? htmlspecialchars(reset($leads)['nome']) : '—' ?>
                </div>
            </div>
        </div>

        <form method="GET" class="filtros">
            <input type="text" name="busca" placeholder="🔍 Buscar por nome, empresa ou origem..." value="<?= htmlspecialchars($busca) ?>">
            <select name="origem">
                <option value="">Todas as origens</option>
                <?php foreach ($origens_unicas as $o): ?>
                    <option value="<?= htmlspecialchars($o) ?>" <?= $filtro_origem === $o ? 'selected' : '' ?>>
                        <?= htmlspecialchars($o) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrar</button>
        </form>

        <div class="table-wrap">
            <?php if ($total_leads > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Nome</th>
                            <th>Empresa</th>
                            <th>Origem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td style="white-space: nowrap; color: #7f8c8d; font-size: 13px;">
                                    <?= htmlspecialchars($lead['data']) ?>
                                </td>
                                <td><strong style="color: var(--prev-primaria);"><?= htmlspecialchars($lead['nome']) ?></strong></td>
                                <td><?= htmlspecialchars($lead['empresa']) ?></td>
                                <td><span class="origem-tag"><?= htmlspecialchars($lead['origem']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="vazio">
                    <div class="icon">📭</div>
                    <h3>Nenhum lead capturado ainda</h3>
                    <p>Quando um visitante fornecer Nome, Empresa e Origem no chat, aparecerá aqui.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>