<?php
/**
 * Processamento do login
 * Recebe os dados do formulário de login, valida as credenciais
 * e cria a sessão do utilizador caso estejam corretas.
 */

require_once __DIR__ . '/includes/funcoes.php';

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Só aceitar pedidos POST (não permitir acesso direto via URL)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
    exit;
}

// ─── Recolher dados do formulário ────────────────────────────────────────────
$email    = trim($_POST['text_username'] ?? '');
$password = trim($_POST['text_password'] ?? '');

// ─── Validação básica dos campos ─────────────────────────────────────────────
$validation_errors = [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $validation_errors[] = 'O email deve ser válido.';
}
if (strlen($email) < 5 || strlen($email) > 100) {
    $validation_errors[] = 'O email deve ter entre 5 e 100 caracteres.';
}
if (strlen($password) < 6 || strlen($password) > 50) {
    $validation_errors[] = 'A password deve ter entre 6 e 50 caracteres.';
}

// Se existirem erros de validação, redirecionar para o login com os erros
if (!empty($validation_errors)) {
    $_SESSION['validation_errors'] = $validation_errors;
    echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
    exit;
}

// ─── Autenticação na base de dados ───────────────────────────────────────────
try {
    // Criar ligação à base de dados
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar se a tabela de utilizadores existe
    $tableExistsStmt = $pdo->query("
        SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '" . MYSQL_DATABASE . "'
        AND TABLE_NAME = 'utilizadores'
    ");
    $tableExists = $tableExistsStmt->fetchColumn() > 0;

    if (!$tableExists) {
        $_SESSION['server_error'] = 'Sistema de autenticação não configurado.';
        echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
        exit;
    }

    // Procurar o utilizador pelo email (o email está encriptado na BD com AES)
    $stmt = $pdo->prepare("
        SELECT id, nome, senha, perfil AS profile
        FROM utilizadores
        WHERE AES_DECRYPT(nome, :chave) = :email
        LIMIT 1
    ");
    $stmt->execute([
        ':chave' => MYSQL_AES_KEY,
        ':email' => $email
    ]);
    $agent = $stmt->fetch(PDO::FETCH_OBJ);

    // Verificar se a password está correta usando password_verify (hash bcrypt)
    $password_valid = false;
    if ($agent) {
        if (password_verify($password, $agent->senha)) {
            $password_valid = true;
        }
        // Compatibilidade com passwords em texto simples (apenas em desenvolvimento)
        elseif ($password === $agent->senha) {
            $password_valid = true;
        }
    }

    // ─── Registo de tentativa de autenticação ────────────────────────────────
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';

    if (!$agent || !$password_valid) {
        // Guardar na base de dados a tentativa falhada
        $pdo->prepare(
            "INSERT INTO historico_alteracoes (entidade, entidade_id, entidade_nome, acao, detalhe, utilizador, criado_em)
             VALUES ('autenticacao', NULL, ?, 'login_falha', ?, ?, NOW())"
        )->execute([
            $email,
            'Tentativa de login falhada — IP: ' . $ip,
            $email
        ]);

        $_SESSION['server_error'] = 'Credenciais inválidas. Tente novamente.';
        echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
        exit;
    }

    //  Login bem-sucedido

    // Atualizar data/hora do último acesso do utilizador
    $pdo->prepare("UPDATE utilizadores SET ultimo_acesso = NOW() WHERE id = ?")
        ->execute([$agent->id]);

    // Guardar na base de dados que o utilizador fez login com sucesso
    $pdo->prepare(
        "INSERT INTO historico_alteracoes (entidade, entidade_id, entidade_nome, acao, detalhe, utilizador, criado_em)
         VALUES ('autenticacao', ?, ?, 'login', ?, ?, NOW())"
    )->execute([
        $agent->id,
        $email,
        'Login bem-sucedido — IP: ' . $ip,
        $email
    ]);

    // Guardar os dados do utilizador na sessão
    $_SESSION['user_id']    = $agent->id;
    $_SESSION['user_email'] = $email;
    $_SESSION['profile']    = $agent->profile;
    $_SESSION['logged_in']  = true;

    // Redirecionar para o painel principal
    header('Location: ' . BASE_URL . '/private/home.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['server_error'] = 'Erro ao ligar à base de dados: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
    exit;
}
?>
