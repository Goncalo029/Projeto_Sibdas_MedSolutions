<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Documentos - Novo';
include __DIR__ . '/../../includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="mhs-page-header mhs-page-header--dashboard">
                    <div>
                        <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
                        <h1 class="mhs-page-title">Documentos - Novo</h1>
                    </div>
                </div>

                <div class="card mhs-data-card">
                    <div class="card-body">
                        <form method="POST" action="lista.php" style="max-width:600px">
                            <div class="mb-3">
                                <label for="id_equipamento" class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                                <select id="id_equipamento" name="id_equipamento" class="form-select" required>
                                    <option value="">Selecione um equipamento...</option>
                                    <option value="1">EQ-001 - Monitor multiparamétrico</option>
                                    <option value="2">EQ-002 - Ventilador</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tipo_documento" class="form-label fw-semibold">Tipo de documento <span class="text-danger">*</span></label>
                                <select id="tipo_documento" name="tipo_documento" class="form-select" required>
                                    <option value="">Selecione um tipo...</option>
                                    <option value="Manual">Manual</option>
                                    <option value="Certificado">Certificado</option>
                                    <option value="Contrato">Contrato</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nome" class="form-label fw-semibold">Nome do documento</label>
                                <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex.: Manual do utilizador" />
                            </div>
                            <div class="mb-3">
                                <label for="data_upload" class="form-label fw-semibold">Data de upload</label>
                                <input type="date" id="data_upload" name="data_upload" class="form-control mhs-datepicker" />
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>
                                <a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-times me-2"></i>Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
