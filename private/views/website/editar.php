<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if (($_SESSION['profile'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/private/home.php');
    exit;
}

$pdo = mhs_pdo();

// Cria a tabela se não existir e semeia os valores por omissão
$pdo->exec("CREATE TABLE IF NOT EXISTS `website_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `chave` varchar(100) NOT NULL,
    `valor` text DEFAULT NULL,
    `atualizado_em` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$defaults = [
    ['hero_overline',       'Tecnologia para hospitais'],
    ['hero_titulo',         'Inventário hospitalar com'],
    ['hero_titulo_strong',  'visão clara e operação rápida'],
    ['hero_subtitulo',      'Centralize equipamentos, contratos, localizações e documentação técnica num painel desenhado para equipas hospitalares.'],
    ['produto_descricao',   'Plataforma web de controlo de equipamentos médicos com pesquisa avançada, rastreabilidade e apoio ao planeamento de manutenção.'],
    ['setor_titulo',        'Desenhado para o setor da saúde'],
    ['setor_descricao',     'Uma solução pensada para as necessidades reais dos serviços hospitalares, com foco na fiabilidade da informação e na rapidez de operação das equipas técnicas.'],
    ['setor_check_1',       'Informação sempre atualizada e consistente'],
    ['setor_check_2',       'Apoio à decisão operacional das equipas clínicas'],
    ['setor_check_3',       'Classificação por criticidade incluindo suporte de vida'],
    ['contacto_descricao',  'Entre em contacto para agendar uma demonstração ou esclarecer dúvidas sobre o sistema.'],
    ['footer_morada',       'Porto, Portugal'],
    ['footer_email',        'geral@medsolutions.pt'],
    ['footer_telefone',     '+351 220 600 700'],
];

$ins = $pdo->prepare("INSERT IGNORE INTO website_config (chave, valor, atualizado_em) VALUES (?, ?, NOW())");
foreach ($defaults as $d) {
    $ins->execute($d);
}

// Guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = [
        'hero_overline', 'hero_titulo', 'hero_titulo_strong', 'hero_subtitulo',
        'produto_descricao',
        'setor_titulo', 'setor_descricao', 'setor_check_1', 'setor_check_2', 'setor_check_3',
        'contacto_descricao',
        'footer_morada', 'footer_email', 'footer_telefone',
    ];
    $upd = $pdo->prepare("UPDATE website_config SET valor=?, atualizado_em=NOW() WHERE chave=?");
    foreach ($campos as $chave) {
        $upd->execute([trim($_POST[$chave] ?? ''), $chave]);
    }
    mhs_historico('website', null, 'Conteúdo do website público', 'editar');
    $_SESSION['success_message'] = 'Conteúdo do website atualizado com sucesso.';
    header('Location: editar.php');
    exit;
}

// Carregar valores atuais
$rows = $pdo->query("SELECT chave, valor FROM website_config")->fetchAll();
$cfg = [];
foreach ($rows as $r) {
    $cfg[$r->chave] = $r->valor;
}

$page_title = 'Website Público - Editar Conteúdo';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-globe fa-fw"></i></span>
        <h1 class="mhs-page-title">Conteúdo do Website Público</h1>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/public/index.php" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ver Website
        </a>
    </div>
</div>

<form method="POST" action="">

    <!-- HERO -->
    <div class="card mhs-data-card mb-4">
        <div class="card-header fw-bold bg-primary text-white">
            <i class="fa-solid fa-star me-1"></i>Secção Hero (Topo da página)
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Overline <small class="text-muted">(texto acima do título)</small></label>
                    <input type="text" name="hero_overline" class="form-control" value="<?= esc($cfg['hero_overline'] ?? '') ?>" maxlength="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Título — parte normal</label>
                    <input type="text" name="hero_titulo" class="form-control" value="<?= esc($cfg['hero_titulo'] ?? '') ?>" maxlength="120">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Título — parte em destaque <small class="text-muted">(negrito/cor)</small></label>
                    <input type="text" name="hero_titulo_strong" class="form-control" value="<?= esc($cfg['hero_titulo_strong'] ?? '') ?>" maxlength="120">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Subtítulo</label>
                    <textarea name="hero_subtitulo" class="form-control" rows="2" maxlength="300"><?= esc($cfg['hero_subtitulo'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUTO -->
    <div class="card mhs-data-card mb-4">
        <div class="card-header fw-bold bg-primary text-white">
            <i class="fa-solid fa-box-open me-1"></i>Secção O Produto
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">Descrição da secção</label>
                <textarea name="produto_descricao" class="form-control" rows="3" maxlength="400"><?= esc($cfg['produto_descricao'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- SETOR SAÚDE -->
    <div class="card mhs-data-card mb-4">
        <div class="card-header fw-bold bg-primary text-white">
            <i class="fa-solid fa-hospital me-1"></i>Secção Setor Saúde
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Título</label>
                    <input type="text" name="setor_titulo" class="form-control" value="<?= esc($cfg['setor_titulo'] ?? '') ?>" maxlength="120">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Descrição</label>
                    <textarea name="setor_descricao" class="form-control" rows="3" maxlength="400"><?= esc($cfg['setor_descricao'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ponto 1 da lista</label>
                    <input type="text" name="setor_check_1" class="form-control" value="<?= esc($cfg['setor_check_1'] ?? '') ?>" maxlength="150">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ponto 2 da lista</label>
                    <input type="text" name="setor_check_2" class="form-control" value="<?= esc($cfg['setor_check_2'] ?? '') ?>" maxlength="150">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ponto 3 da lista</label>
                    <input type="text" name="setor_check_3" class="form-control" value="<?= esc($cfg['setor_check_3'] ?? '') ?>" maxlength="150">
                </div>
            </div>
        </div>
    </div>

    <!-- CONTACTO -->
    <div class="card mhs-data-card mb-4">
        <div class="card-header fw-bold bg-primary text-white">
            <i class="fa-solid fa-headset me-1"></i>Secção Contacto
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">Descrição da secção</label>
                <textarea name="contacto_descricao" class="form-control" rows="2" maxlength="300"><?= esc($cfg['contacto_descricao'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- RODAPÉ -->
    <div class="card mhs-data-card mb-4">
        <div class="card-header fw-bold bg-primary text-white">
            <i class="fa-solid fa-shoe-prints me-1"></i>Rodapé
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Morada</label>
                    <input type="text" name="footer_morada" class="form-control" value="<?= esc($cfg['footer_morada'] ?? '') ?>" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="footer_email" class="form-control" value="<?= esc($cfg['footer_email'] ?? '') ?>" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Telefone</label>
                    <input type="text" name="footer_telefone" class="form-control" value="<?= esc($cfg['footer_telefone'] ?? '') ?>" maxlength="30">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-5">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
        </button>
        <a href="<?= BASE_URL ?>/public/index.php" target="_blank" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Pré-visualizar Website
        </a>
    </div>

</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
