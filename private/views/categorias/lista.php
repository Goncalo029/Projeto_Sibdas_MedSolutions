<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Categorias - Lista';
include __DIR__ . '/../../includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="mhs-page-header mhs-page-header--dashboard">
                    <div>
                        <span class="mhs-page-kicker"><i class="fa-solid fa-tags fa-fw"></i></span>
                        <h1 class="mhs-page-title">Categorias</h1>
                    </div>
                    <div class="mhs-page-actions">
                        <a href='nova.php' class='btn btn-primary'><i class='fa-solid fa-plus me-2'></i>Novo</a>
                    </div>
                </div>

                <div class="card mhs-data-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mhs-datatable mb-0" id="categoriasTable">
                                <thead class="mhs-thead">
                                    <tr><th>Nome</th><th>Descrição</th><th>Ações</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Monitorização</td>
                                        <td>Equipamentos de monitorização de sinais vitais</td>
                                        <td>
                                            <div class="d-flex gap-1 flex-nowrap">
                                                <a href="detalhes.php" class="btn btn-sm btn-outline-secondary" title="Detalhes"><i class="fa-solid fa-eye"></i></a>
                                                <a href="editar.php" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="1" data-delete-name="Monitorização" title="Apagar"><i class="fa-solid fa-trash"></i></button>
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
