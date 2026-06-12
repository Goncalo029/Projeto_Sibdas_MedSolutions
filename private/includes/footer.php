    </div><!-- /.mhs-main-content -->

<!-- Modal de confirmacao de apagar -->
<div class="modal fade" id="mhsDeleteModal" tabindex="-1" aria-labelledby="mhsDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="mhsDeleteModalLabel"><i class="fa-solid fa-trash me-2"></i>Confirmar Apagar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="mhsDeleteForm" method="POST" action="">
                <div class="modal-body">
                    <p>Tem a certeza que deseja apagar <strong id="mhsDeleteName"></strong>?</p>
                    <p class="text-muted small mb-0">Esta acao nao pode ser revertida.</p>
                    <input type="hidden" name="id_enc" id="mhsDeleteId" value="">
                    <input type="hidden" name="_csrf_token" id="mhsDeleteCsrf" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash me-1"></i> Apagar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $mhs_js_version = filemtime(__DIR__ . '/../assets/js/1220673.js'); ?>
<script src="<?= BASE_URL ?>/private/assets/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/private/assets/js/1220673.js?v=<?= $mhs_js_version ?>"></script>

</body>
</html>
