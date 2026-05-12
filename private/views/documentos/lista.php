<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Documentos - Lista';
include __DIR__ . '/../../includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="mhs-page-header mhs-page-header--dashboard">
                    <div>
                        <span class="mhs-page-kicker"><i class="fa-solid fa-file-lines fa-fw"></i></span>
                        <h1 class="mhs-page-title">Documentos</h1>
                    </div>
                    <div class="mhs-page-actions">
                        <a href='novo.php' class='btn btn-primary'><i class='fa-solid fa-plus me-2'></i>Novo</a>
                    </div>
                </div>

                <div class="card mhs-data-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mhs-datatable mb-0" id="documentosTable">
                                <thead class="mhs-thead">
                                    <tr><th>Equipamento</th><th>Tipo</th><th>Nome</th><th>Data</th><th>Validade</th><th>PDF</th><th>Ações</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Item 1</td>
                                        <td>Descrição 1</td>
                                        <td>Documento 1</td>
                                        <td>12/04/2026</td>
                                        <td>12/04/2027</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-file-pdf"></i></a>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 flex-nowrap">
                                                <a href="detalhes.php" class="btn btn-sm btn-outline-secondary" title="Detalhes"><i class="fa-solid fa-eye"></i></a>
                                                <a href="editar.php" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="1" data-delete-name="Documento 1" title="Apagar"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
