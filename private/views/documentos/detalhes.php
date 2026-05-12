<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Documentos - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <h2 class="fw-bold mb-0"><i class="fa-solid fa-file-lines me-2"></i>Manual do utilizador</h2>
                    <div class="d-flex gap-2">
                        <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i> Editar</a>
                        <a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Voltar</a>
                    </div>
                </div>
                <hr>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-file-lines me-1"></i>Informação do documento</div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-5">Equipamento</dt>
                                    <dd class="col-7">EQ-001 - Monitor</dd>
                                    <dt class="col-5">Tipo</dt>
                                    <dd class="col-7">Manual</dd>
                                    <dt class="col-5">Data</dt>
                                    <dd class="col-7">12/04/2026</dd>
                                    <dt class="col-5">Estado</dt>
                                    <dd class="col-7"><span class="badge bg-success">Ativo</span></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-download me-1"></i>Download</div>
                            <div class="card-body">
                                <a href="#" class="btn btn-outline-primary w-100"><i class="fa-solid fa-file-pdf me-2"></i>Download PDF</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
