<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
    exit;
}

// --------------------------------------------------------------------
// RECOLHA
// --------------------------------------------------------------------
$email = trim($_POST['text_username'] ?? '');
$password = trim($_POST['text_password'] ?? '');

// --------------------------------------------------------------------
// VALIDAÇÃO
// --------------------------------------------------------------------
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

if (!empty($validation_errors)) {
    $_SESSION['validation_errors'] = $validation_errors;
    echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
    exit;
}

// --------------------------------------------------------------------
// AUTENTICAÇÃO
// --------------------------------------------------------------------
try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar se a tabela agents existe
    $tableExistsStmt = $pdo->query("
        SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '" . MYSQL_DATABASE . "' 
        AND TABLE_NAME = 'agents'
    ");
    $tableExists = $tableExistsStmt->fetchColumn() > 0;

    if (!$tableExists) {
        $_SESSION['server_error'] = 'Sistema de autenticação não configurado.';
        echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
        exit;
    }

    // Procurar utilizador (agents) - usando AES_DECRYPT na BD
    $stmt = $pdo->prepare("
        SELECT id, name, passwrd, profile
        FROM agents
        WHERE AES_DECRYPT(name, :chave) = :email
        LIMIT 1
    ");
    $stmt->execute([
        ':chave' => MYSQL_AES_KEY,
        ':email' => $email
    ]);
    $agent = $stmt->fetch(PDO::FETCH_OBJ);

    // Validar password (usar password_verify se estiver com hash, ou comparação direta se plaintext)
    $password_valid = false;
    if ($agent) {
        // Se a password está armazenada com hash (recomendado)
        if (password_verify($password, $agent->passwrd)) {
            $password_valid = true;
        }
        // Fallback: comparação direta (usar apenas em desenvolvimento)
        elseif ($password === $agent->passwrd) {
            $password_valid = true;
        }
    }

    if (!$agent || !$password_valid) {
        $_SESSION['server_error'] = 'Credenciais inválidas. Tente novamente.';
        echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
        exit;
    }

    // Atualizar último login
    $pdo->prepare("UPDATE agents SET last_login = NOW() WHERE id = ?")
        ->execute([$agent->id]);

    // Guardar sessão
    $_SESSION['user_id'] = $agent->id;
    $_SESSION['user_email'] = $email;
    $_SESSION['profile'] = $agent->profile;
    $_SESSION['logged_in'] = true;

    header('Location: ' . BASE_URL . '/private/home.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['server_error'] = 'Erro ao ligar à base de dados: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/public/login.php";</script>';
    exit;
}
?>
