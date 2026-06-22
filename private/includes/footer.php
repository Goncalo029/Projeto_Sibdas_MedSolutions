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
                    <input type="hidden" name="return_qs" id="mhsDeleteReturn" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash me-1"></i> Apagar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ligação perdida -->
<div id="mhsConnModal" class="mhs-conn-modal">
  <div class="mhs-conn-backdrop"></div>
  <div class="mhs-conn-dialog">
    <div class="mhs-conn-head">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
      <span>Erro de ligação</span>
    </div>
    <div class="mhs-conn-body">
      <p>Ocorreu um erro inesperado. Por favor, tente novamente.</p>
      <small>Se o problema persistir, verifique a sua ligação à rede ou contacte o administrador do sistema.</small>
    </div>
    <div class="mhs-conn-foot">
      <button type="button" onclick="mhsConnBack()">Voltar</button>
      <button type="button" class="mhs-conn-btn-primary" onclick="mhsConnRetry()">Tentar novamente</button>
    </div>
  </div>
</div>

<?php $mhs_js_version = filemtime(__DIR__ . '/../assets/js/1220673.js'); ?>
<script src="<?= BASE_URL ?>/private/assets/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/private/assets/js/1220673.js?v=<?= $mhs_js_version ?>"></script>

</body>
</html>
