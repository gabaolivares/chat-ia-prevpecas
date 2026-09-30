<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$SENHA_PAINEL = 'sua_senha_aqui';

$erro = '';

if (isset($_SESSION['leads_autorizado']) && $_SESSION['leads_autorizado'] === true) {
    header('Location: leads.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_digitada = $_POST['senha'] ?? '';

    if (hash_equals($SENHA_PAINEL, $senha_digitada)) {
        session_regenerate_id(true);
        $_SESSION['leads_autorizado'] = true;
        header('Location: leads.php');
        exit;
    } else {
        $erro = 'Senha incorreta. Tente novamente.';
    }
}
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
            background: linear-gradient(135deg, var(--prev-primaria) 0%, var(--prev-primaria-clara) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: -150px; right: -150px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(229, 80, 57, 0.2), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: "";
            position: absolute;
            bottom: -150px; left: -150px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-box {
            background: var(--prev-bg-janela);
            border-radius: 20px;
            padding: 50px 45px;
            width: 100%;
            max-width: 430px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 1;
            border-top: 5px solid var(--prev-destaque);
        }

        .login-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--prev-destaque-clara), var(--prev-destaque));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 22px;
            box-shadow: 0 8px 20px rgba(229, 80, 57, 0.35);
            color: #fff;
        }

        h1 {
            color: var(--prev-primaria);
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        p.sub {
            color: #7f8c8d;
            text-align: center;
            font-size: 13px;
            margin-bottom: 32px;
            font-weight: 500;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 22px;
        }

        .input-group label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--prev-primaria);
            font-weight: 700;
        }

        .input-group input {
            padding: 15px 18px;
            font-size: 15px;
            background: var(--prev-bg-chat);
            border: 2px solid var(--prev-borda);
            border-radius: 10px;
            color: var(--prev-texto-escuro);
            outline: none;
            transition: all 0.3s ease;
        }
        .input-group input:focus {
            border-color: var(--prev-primaria-clara);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(30, 95, 140, 0.12);
        }
        .input-group input::placeholder { color: #b0b8bf; }

        .erro {
            background: rgba(229, 80, 57, 0.1);
            border: 1px solid var(--prev-destaque);
            color: var(--prev-destaque);
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .btn-entrar {
            width: 100%;
            padding: 16px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #fff;
            background: linear-gradient(135deg, var(--prev-primaria-clara), var(--prev-primaria));
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(10, 61, 98, 0.35);
            position: relative;
            overflow: hidden;
        }
        .btn-entrar::after {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        .btn-entrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(10, 61, 98, 0.5);
        }
        .btn-entrar:hover::after { left: 100%; }
    </style>
</head>
<body>

    <div class="login-box">
        <div class="login-icon">🔐</div>
        <h1>Painel de Leads</h1>
        <p class="sub">Acesso restrito — Prev-Peças</p>

        <?php if ($erro !== ''): ?>
            <div class="erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Senha de Acesso</label>
                <input type="password" name="senha" placeholder="Digite a senha do painel" required autofocus>
            </div>

            <button type="submit" class="btn-entrar">Entrar no Painel</button>
        </form>
    </div>

</body>
</html>
