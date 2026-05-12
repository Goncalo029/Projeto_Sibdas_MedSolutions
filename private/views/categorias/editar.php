<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Categorias - Editar';
include __DIR__ . '/../../includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="mhs-page-header mhs-page-header--dashboard">
                    <div>
                        <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
                        <h1 class="mhs-page-title">Categorias - Editar</h1>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-tags me-1"></i>Informação da categoria</div>
                    <div class="card-body">
                        <form method="POST" action="lista.php" style="max-width:600px">
                            <div class="mb-3">
                                <label for="nome" class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                                <input type="text" id="nome" name="nome" class="form-control" value="Equipamento médico" required maxlength="100" />
                            </div>
                            <div class="mb-3">
                                <label for="descricao" class="form-label fw-semibold">Descrição</label>
                                <textarea id="descricao" name="descricao" class="form-control" placeholder="Descrição da categoria..." rows="4" maxlength="500">Categoria para equipamentos médicos</textarea>
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
