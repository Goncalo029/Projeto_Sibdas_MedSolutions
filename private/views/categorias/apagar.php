<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categoria) {
        $_SESSION['error_message'] = 'Categoria não encontrada!';
        echo '<script>window.location.href = "lista.php";</script>';
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Erro ao carregar categoria: ' . $e->getMessage();
    echo '<script>window.location.href = "lista.php";</script>';
    exit;
}

$page_title = 'Categorias - Apagar';
include __DIR__ . '/../../includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="mhs-page-header mhs-page-header--dashboard">
                    <div>
                        <span class="mhs-page-kicker"><i class="fa-solid fa-trash fa-fw"></i></span>
                        <h1 class="mhs-page-title">Categorias - Apagar</h1>
                    </div>
                </div>

                <div class="card mhs-data-card">
                    <div class="card-body">
                        <div class="alert alert-danger mb-4">
                            <strong>Confirmação de remoção</strong><br />
                            Tem a certeza que pretende apagar esta categoria. Esta ação não pode ser revertida.
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-semibold">Nome</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($categoria['nome']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-semibold">Descrição</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($categoria['descricao'] ?? ''); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="confirmar_apagar.php">
                            <input type="hidden" name="id_enc" value="<?php echo htmlspecialchars($categoria['id']); ?>">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash me-2"></i>Apagar</button>
                                <a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-times me-2"></i>Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
