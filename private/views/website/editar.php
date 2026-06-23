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

$img_dest = __DIR__ . '/../../../public/assets/images/dashboard-preview.png';

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

    // Upload opcional da imagem do hero (substitui dashboard-preview.png)
    $img_erro = '';
    if (!empty($_FILES['hero_imagem']['name'])) {
        if (($_FILES['hero_imagem']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tmp  = $_FILES['hero_imagem']['tmp_name'];
            $info = @getimagesize($tmp);
            $tipo = $info[2] ?? null;
            if ($tipo && in_array($tipo, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)
                && $_FILES['hero_imagem']['size'] <= 8 * 1024 * 1024) {
                if ($tipo === IMAGETYPE_PNG) {
                    @move_uploaded_file($tmp, $img_dest);
                } else {
                    // converter JPG/WEBP para PNG (o site usa dashboard-preview.png)
                    $src = $tipo === IMAGETYPE_JPEG ? @imagecreatefromjpeg($tmp) : @imagecreatefromwebp($tmp);
                    if ($src) { @imagepng($src, $img_dest); }
                }
            } else {
                $img_erro = 'A imagem tem de ser PNG ou JPG (até 8 MB).';
            }
        } else {
            $img_erro = 'Falha ao carregar a imagem.';
        }
    }

    mhs_historico('website', null, 'Conteúdo do website público', 'editar');
    if ($img_erro) {
        $_SESSION['error_message'] = 'Textos guardados, mas a imagem não foi atualizada: ' . $img_erro;
    } else {
        $_SESSION['success_message'] = 'Conteúdo do website atualizado com sucesso.';
    }
    header('Location: editar.php');
    exit;
}

// Carregar valores atuais
$rows = $pdo->query("SELECT chave, valor FROM website_config")->fetchAll();
$cfg = [];
foreach ($rows as $r) {
    $cfg[$r->chave] = $r->valor;
}
$img_ver = @filemtime($img_dest) ?: time();

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
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Abrir Website
        </a>
    </div>
</div>

<form method="POST" action="" enctype="multipart/form-data">

    <!-- IMAGEM DO HERO -->
    <div class="mhs-web-card">
        <div class="mhs-web-card-head">
            <span class="mhs-web-card-ico"><i class="fa-solid fa-image"></i></span>
            <div>
                <h3>Imagem do painel</h3>
                <span class="where"><i class="fa-solid fa-arrow-right-long"></i> Imagem grande no topo do site, ao lado do título</span>
            </div>
        </div>
        <div class="mhs-web-card-body">
            <div class="row g-4 align-items-center">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mudar imagem</label>
                    <input type="file" name="hero_imagem" id="heroImg" class="form-control" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG ou JPG, formato <strong>horizontal</strong> (largura ~1600px), até 8 MB. Deixa vazio para manter a atual.</div>
                </div>
                <div class="col-md-6">
                    <div class="mhs-web-img-frame">
                        <span class="tag" id="heroImgTag">Imagem atual</span>
                        <img id="heroImgPreview" src="<?= BASE_URL ?>/public/assets/images/dashboard-preview.png?v=<?= $img_ver ?>" alt="Pré-visualização da imagem do hero">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HERO (textos) -->
    <div class="mhs-web-card">
        <div class="mhs-web-card-head">
            <span class="mhs-web-card-ico"><i class="fa-solid fa-star"></i></span>
            <div>
                <h3>Topo da página</h3>
                <span class="where"><i class="fa-solid fa-arrow-right-long"></i> Banner azul no início, com o título principal</span>
            </div>
        </div>
        <div class="mhs-web-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Etiqueta pequena <small class="text-muted">(acima do título)</small></label>
                    <input type="text" name="hero_overline" class="form-control" value="<?= esc($cfg['hero_overline'] ?? '') ?>" maxlength="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Título — parte normal</label>
                    <input type="text" name="hero_titulo" class="form-control" value="<?= esc($cfg['hero_titulo'] ?? '') ?>" maxlength="120">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Título — parte em destaque <small class="text-muted">(cor cyan)</small></label>
                    <input type="text" name="hero_titulo_strong" class="form-control" value="<?= esc($cfg['hero_titulo_strong'] ?? '') ?>" maxlength="120">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Subtítulo <small class="text-muted">(parágrafo por baixo do título)</small></label>
                    <textarea name="hero_subtitulo" class="form-control" rows="2" maxlength="300"><?= esc($cfg['hero_subtitulo'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUTO -->
    <div class="mhs-web-card">
        <div class="mhs-web-card-head">
            <span class="mhs-web-card-ico"><i class="fa-solid fa-box-open"></i></span>
            <div>
                <h3>Secção "O Produto"</h3>
                <span class="where"><i class="fa-solid fa-arrow-right-long"></i> Texto introdutório dos 3 cartões do produto</span>
            </div>
        </div>
        <div class="mhs-web-card-body">
            <label class="form-label fw-semibold">Descrição da secção</label>
            <textarea name="produto_descricao" class="form-control" rows="3" maxlength="400"><?= esc($cfg['produto_descricao'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- SETOR SAÚDE -->
    <div class="mhs-web-card">
        <div class="mhs-web-card-head">
            <span class="mhs-web-card-ico"><i class="fa-solid fa-hospital"></i></span>
            <div>
                <h3>Secção "Setor Saúde"</h3>
                <span class="where"><i class="fa-solid fa-arrow-right-long"></i> Bloco com título, descrição e lista de pontos</span>
            </div>
        </div>
        <div class="mhs-web-card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Título</label>
                    <input type="text" name="setor_titulo" class="form-control" value="<?= esc($cfg['setor_titulo'] ?? '') ?>" maxlength="120">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Descrição</label>
                    <textarea name="setor_descricao" class="form-control" rows="3" maxlength="400"><?= esc($cfg['setor_descricao'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fa-solid fa-check text-success me-1"></i>Ponto 1</label>
                    <input type="text" name="setor_check_1" class="form-control" value="<?= esc($cfg['setor_check_1'] ?? '') ?>" maxlength="150">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fa-solid fa-check text-success me-1"></i>Ponto 2</label>
                    <input type="text" name="setor_check_2" class="form-control" value="<?= esc($cfg['setor_check_2'] ?? '') ?>" maxlength="150">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fa-solid fa-check text-success me-1"></i>Ponto 3</label>
                    <input type="text" name="setor_check_3" class="form-control" value="<?= esc($cfg['setor_check_3'] ?? '') ?>" maxlength="150">
                </div>
            </div>
        </div>
    </div>

    <!-- CONTACTO -->
    <div class="mhs-web-card">
        <div class="mhs-web-card-head">
            <span class="mhs-web-card-ico"><i class="fa-solid fa-headset"></i></span>
            <div>
                <h3>Secção "Contacto"</h3>
                <span class="where"><i class="fa-solid fa-arrow-right-long"></i> Texto acima do formulário de contacto</span>
            </div>
        </div>
        <div class="mhs-web-card-body">
            <label class="form-label fw-semibold">Descrição da secção</label>
            <textarea name="contacto_descricao" class="form-control" rows="2" maxlength="300"><?= esc($cfg['contacto_descricao'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- RODAPÉ -->
    <div class="mhs-web-card">
        <div class="mhs-web-card-head">
            <span class="mhs-web-card-ico"><i class="fa-solid fa-shoe-prints"></i></span>
            <div>
                <h3>Rodapé</h3>
                <span class="where"><i class="fa-solid fa-arrow-right-long"></i> Contactos no fundo do site</span>
            </div>
        </div>
        <div class="mhs-web-card-body">
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

    <div class="mhs-web-save">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
        </button>
        <a href="<?= BASE_URL ?>/public/index.php" target="_blank" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Pré-visualizar
        </a>
    </div>

</form>

<!-- JS da pré-visualização da imagem movido para private/assets/js/1220673.js -->

<?php include __DIR__ . '/../../includes/footer.php'; ?>
