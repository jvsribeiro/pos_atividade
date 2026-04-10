<?php
declare(strict_types=1);

$host = '127.0.0.1';
$database = 'agenda_contatos';
$user = 'agenda_user';
$password = 'agenda123';
$telefoneRegex = '/^\(\d{2}\)\s\d\s\d{4}-\d{4}$/';

$mensagemErro = '';
$mensagemSucesso = '';
$contatos = [];

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $exception) {
    $pdo = null;
    $mensagemErro = 'Nao foi possivel conectar ao banco de dados. Confirme se o MySQL do container foi inicializado.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));

    if ($nome === '') {
        $mensagemErro = 'Informe o nome do contato.';
    } elseif (!preg_match($telefoneRegex, $telefone)) {
        $mensagemErro = 'Telefone invalido. Use o formato (xx) x xxxx-xxxx.';
    } else {
        $statement = $pdo->prepare(
            'INSERT INTO contatos (nome, telefone) VALUES (:nome, :telefone)'
        );
        $statement->execute([
            ':nome' => $nome,
            ':telefone' => $telefone,
        ]);

        $mensagemSucesso = 'Contato cadastrado com sucesso.';
    }
}

if ($pdo instanceof PDO) {
    $contatos = $pdo->query(
        'SELECT id, nome, telefone, criado_em FROM contatos ORDER BY id DESC'
    )->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Contatos</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f4f0;
            --panel: #ffffff;
            --line: #d8d2c4;
            --text: #1f2933;
            --accent: #155e63;
            --accent-strong: #0f4c50;
            --success: #146c43;
            --danger: #a61b1b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #ebe7de 0%, var(--bg) 100%);
            color: var(--text);
        }

        .container {
            max-width: 920px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 12px 35px rgba(31, 41, 51, 0.08);
        }

        h1, h2 {
            margin-top: 0;
        }

        p {
            line-height: 1.5;
        }

        form {
            display: grid;
            gap: 16px;
            margin-top: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #b8c3c8;
            border-radius: 12px;
            font-size: 1rem;
        }

        button {
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            background: var(--accent);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            background: var(--accent-strong);
        }

        .alert {
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 12px;
            font-weight: 600;
        }

        .alert-success {
            background: #e9f7ef;
            color: var(--success);
        }

        .alert-error {
            background: #fdeaea;
            color: var(--danger);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }

        th {
            background: #f6f9fa;
        }

        .section {
            margin-top: 28px;
        }

        .muted {
            color: #52606d;
            font-size: 0.95rem;
        }

        @media (max-width: 640px) {
            .card {
                padding: 20px;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                border-bottom: 1px solid var(--line);
                padding: 10px 0;
            }

            td {
                border: 0;
                padding: 8px 0;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <section class="card">
            <h1>Agenda de Contatos</h1>
            <p>Aplicacao PHP executando em Apache com persistencia aplicada apenas ao MySQL.</p>
            <p class="muted">Formato obrigatorio do telefone: (xx) x xxxx-xxxx</p>

            <?php if ($mensagemSucesso !== ''): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if ($mensagemErro !== ''): ?>
                <div class="alert alert-error"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <div>
                    <label for="nome">Nome</label>
                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        maxlength="120"
                        required
                        placeholder="Digite o nome do contato"
                    >
                </div>

                <div>
                    <label for="telefone">Telefone</label>
                    <input
                        type="text"
                        id="telefone"
                        name="telefone"
                        inputmode="numeric"
                        maxlength="16"
                        required
                        pattern="^\(\d{2}\)\s\d\s\d{4}-\d{4}$"
                        placeholder="(11) 9 9999-9999"
                    >
                </div>

                <button type="submit">Cadastrar contato</button>
            </form>

            <section class="section">
                <h2>Contatos cadastrados</h2>
                <?php if (count($contatos) === 0): ?>
                    <p class="muted">Nenhum contato cadastrado ate o momento.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Criado em</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contatos as $contato): ?>
                                <tr>
                                    <td><?= (int) $contato['id']; ?></td>
                                    <td><?= htmlspecialchars($contato['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($contato['telefone'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($contato['criado_em'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </section>
    </main>

    <script>
        const telefoneInput = document.getElementById('telefone');

        telefoneInput.addEventListener('input', (event) => {
            const numeros = event.target.value.replace(/\D/g, '').slice(0, 11);
            let formatado = '';

            if (numeros.length > 0) {
                formatado += `(${numeros.slice(0, 2)}`;
            }

            if (numeros.length >= 3) {
                formatado += `) ${numeros.slice(2, 3)}`;
            }

            if (numeros.length >= 4) {
                formatado += ` ${numeros.slice(3, 7)}`;
            }

            if (numeros.length >= 8) {
                formatado += `-${numeros.slice(7, 11)}`;
            }

            event.target.value = formatado;
        });
    </script>
</body>
</html>
