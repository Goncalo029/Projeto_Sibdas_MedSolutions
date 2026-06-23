<?php
// carrega funcoes e validacoes e verifica a sessao
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
require_admin(); // apenas admin pode apagar

try {
    // liga a base de dados
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // vai buscar o documento que se quer apagar
    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM documentos WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    // se nao existir, avisa e volta para a lista
    if (!$documento) {
        $_SESSION['error_message'] = 'Documento não encontrado!';
        echo '<script>window.location.href = "lista.php";</script>';
        exit;
    }
} catch (Exception $e) {
    // erro ao ligar ou ao ler da base de dados
    $_SESSION['error_message'] = 'Erro ao carregar documento: ' . $e->getMessage();
    echo '<script>window.location.href = "lista.php";</script>';
    exit;
}

$page_title = 'Documentos - Apagar';
include __DIR__ . '/../../includes/header.php';
?>

    <!-- pagina de confirmacao antes de apagar o documento -->
    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="mhs-page-header mhs-page-header--dashboard">
                    <div>
                        <span class="mhs-page-kicker"><i class="fa-solid fa-trash fa-fw"></i></span>
                        <h1 class="mhs-page-title">Documentos - Apagar</h1>
                    </div>
                </div>

                <div class="card mhs-data-card">
                    <div class="card-body">
                        <!-- aviso de que a accao nao se pode desfazer -->
                        <div class="alert alert-danger mb-4">
                            <strong>Confirmação de remoção</strong><br />
                            Tem a certeza que pretende apagar este documento. Esta ação não pode ser revertida.
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-semibold">Equipamento</h6>
                                        <p class="mb-0">EQ-001 - Monitor multiparamétrico</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-semibold">Tipo de documento</h6>
                                        <p class="mb-0">Manual</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-semibold">Nome</h6>
                                        <p class="mb-0">Manual do utilizador</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-semibold">Data</h6>
                                        <p class="mb-0">12/04/2026</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- formulario que envia o id para o confirmar_apagar.php -->
                        <form method="POST" action="confirmar_apagar.php">
                            <input type="hidden" name="id_enc" value="<?php echo htmlspecialchars($documento['id']); ?>">
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
