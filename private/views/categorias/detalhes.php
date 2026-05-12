<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Categorias - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <h2 class="fw-bold mb-0"><i class="fa-solid fa-tags me-2"></i>Monitorização</h2>
                    <div class="d-flex gap-2">
                        <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i> Editar</a>
                        <a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Voltar</a>
                    </div>
                </div>
                <hr>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-tags me-1"></i>Informação geral</div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-5">Nome</dt>
                                    <dd class="col-7">Monitorização</dd>
                                    <dt class="col-5">Descrição</dt>
                                    <dd class="col-7">Categoria usada para equipamentos de monitorização clínica.</dd>
                                    <dt class="col-5">Estado</dt>
                                    <dd class="col-7"><span class="badge bg-success">Ativa</span></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-stethoscope me-1"></i>Equipamentos nesta categoria</div>
                            <div class="card-body">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr><th>Código</th><th>Designação</th><th>Estado</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>EQ-001</td><td>Monitor multiparamétrico</td><td>Ativo</td></tr>
                                        <tr><td>EQ-004</td><td>Monitor fetal</td><td>Em calibração</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
