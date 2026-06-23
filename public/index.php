<?php
// pagina publica do site (a que aparece antes de fazer login)
// mostra a apresentacao do produto e tem o formulario de contacto
session_start();
require_once __DIR__ . '/../config/config.php';

// atalho para ir buscar um texto configuravel do site (ou usar um valor por defeito)
function website_cfg(array &$cfg, string $chave, string $default): string {
    return htmlspecialchars($cfg[$chave] ?? $default, ENT_QUOTES, 'UTF-8');
}

// vai buscar os textos configuraveis do site a base de dados
$wcfg = [];
$wpdo = null;
try {
    $wpdo = new PDO(
        'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
        MYSQL_USERNAME, MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
    );
    $rows = $wpdo->query("SELECT chave, valor FROM website_config") ?: [];
    foreach ($rows as $r) { $wcfg[$r->chave] = $r->valor; }
} catch (Exception $e) {}

$contacto_msg  = '';
$contacto_tipo = '';

// quando enviam o formulario de contacto, valida e grava a mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['email'], $_POST['mensagem'])) {
    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $mensagem = trim($_POST['mensagem']);

    if ($nome === '' || $email === '' || $mensagem === '') {
        $contacto_msg  = 'Por favor preencha todos os campos obrigatórios.';
        $contacto_tipo = 'erro';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contacto_msg  = 'Por favor introduza um endereço de email válido.';
        $contacto_tipo = 'erro';
    } elseif ($wpdo === null) {
        $contacto_msg  = 'Erro ao enviar a mensagem. Por favor tente novamente.';
        $contacto_tipo = 'erro';
    } else {
        try {
            $stmt = $wpdo->prepare(
                "INSERT INTO mensagens_contacto (nome, email, mensagem, lida, criado_em, atualizado_em)
                 VALUES (?, ?, ?, 0, NOW(), NOW())"
            );
            $stmt->execute([$nome, $email, $mensagem]);
            $contacto_msg  = 'Mensagem enviada com sucesso! Entraremos em contacto brevemente.';
            $contacto_tipo = 'sucesso';
        } catch (Exception $e) {
            $contacto_msg  = 'Erro ao enviar a mensagem. Por favor tente novamente.';
            $contacto_tipo = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MedSolutions — Plataforma de inventário hospitalar com rastreabilidade, contratos, manutenções e alertas em tempo real.">
    <title>MedSolutions — Inventário Hospitalar</title>
    <link rel="shortcut icon" href="assets/images/logo-medsoft.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="assets/css/medsolutions.css?v=<?= @filemtime(__DIR__ . '/assets/css/medsolutions.css') ?>">
</head>
<body>

<div class="mhs-mobile-nav-backdrop" id="mobileNavBackdrop"></div>

<!-- barra do topo (header) -->
<header class="mhs-topbar">
    <div class="mhs-topbar-inner">
        <a href="#" class="mhs-logo-link">
            <img src="assets/images/logo-medsoft.svg" alt="MedSolutions">
            <span>MedSolutions</span>
        </a>

        <nav class="mhs-nav" id="mainNav">
            <a href="#quem-somos"    data-i18n="nav-about">Quem Somos</a>
            <a href="#produto"       data-i18n="nav-product">O Produto</a>
            <a href="#funcionalidades" data-i18n="nav-features">Funcionalidades</a>
            <a href="#setor-saude"   data-i18n="nav-sector">Setor Saúde</a>
            <a href="#contacto"      data-i18n="nav-contact">Contacto</a>
        </nav>

        <div class="mhs-topbar-right">
            <a href="login.php" class="mhs-btn-outline">Entrar na Plataforma</a>
            <button class="mhs-burger" id="burgerBtn" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<!-- secao principal de apresentacao (hero) -->
<div class="mhs-hero-wrap" id="quem-somos">
<section class="mhs-hero">
    <div class="mhs-hero-content">
        <p class="mhs-overline"><i class="fa-solid fa-circle-dot"></i> <span><?= website_cfg($wcfg, 'hero_overline', 'Tecnologia para hospitais') ?></span></p>
        <h1>
            <span><?= website_cfg($wcfg, 'hero_titulo', 'Inventário hospitalar com') ?></span><br>
            <strong><?= website_cfg($wcfg, 'hero_titulo_strong', 'visão clara e operação rápida') ?></strong>
        </h1>
        <p class="mhs-hero-sub">
            <?= website_cfg($wcfg, 'hero_subtitulo', 'Centralize equipamentos, contratos, localizações e documentação técnica num painel desenhado para equipas hospitalares.') ?>
        </p>
        <div class="mhs-hero-actions">
            <a href="#produto" class="mhs-btn-primary" data-i18n="hero-cta1">Conhecer o Produto</a>
            <a href="login.php" class="mhs-btn-ghost">
                <span data-i18n="hero-cta2">Aceder ao Painel</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <div class="mhs-hero-metrics">
            <div class="mhs-hero-metric">
                <strong data-i18n="metric-1-title">Dashboard</strong>
                <span data-i18n="metric-1-sub">KPIs e alertas em tempo real</span>
            </div>
            <div class="mhs-hero-metric">
                <strong data-i18n="metric-2-title">Inventário</strong>
                <span data-i18n="metric-2-sub">Estados, localização e criticidade</span>
            </div>
            <div class="mhs-hero-metric">
                <strong data-i18n="metric-3-title">Contratos</strong>
                <span data-i18n="metric-3-sub">Gestão preventiva de garantias</span>
            </div>
        </div>
    </div>

    <!-- Screenshot da dashboard real -->
    <div class="mhs-hero-visual" aria-hidden="true">
        <div class="mhs-screenshot-wrap">
            <img src="assets/images/dashboard-preview.png?v=<?= @filemtime(__DIR__ . '/assets/images/dashboard-preview.png') ?>" alt="MedSolutions — Painel de Controlo">
        </div>
        <div class="mhs-hero-float mhs-hero-float--tl">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Dados protegidos</span>
        </div>
        <div class="mhs-hero-float mhs-hero-float--br">
            <i class="fa-solid fa-bell"></i>
            <span>Alertas automáticos</span>
        </div>
    </div>
</section>
</div>

<!-- Trust bar -->
<div class="mhs-trust-bar">
    <div class="mhs-trust-inner">
        <span data-i18n="trust-label">Desenvolvido para</span>
        <div class="mhs-trust-items">
            <span><i class="fa-solid fa-hospital"></i> <span data-i18n="trust-1">Hospitais</span></span>
            <span><i class="fa-solid fa-clinic-medical"></i> <span data-i18n="trust-2">Clínicas</span></span>
            <span><i class="fa-solid fa-house-medical"></i> <span data-i18n="trust-3">Centros de Saúde</span></span>
            <span><i class="fa-solid fa-microscope"></i> <span data-i18n="trust-4">Laboratórios</span></span>
        </div>
    </div>
</div>

<!-- secao do produto -->
<section class="mhs-section" id="produto">
    <div class="mhs-section-header">
        <p class="mhs-overline"><i class="fa-solid fa-box-open"></i> <span>O produto</span></p>
        <h2>MedSolutions</h2>
        <p><?= website_cfg($wcfg, 'produto_descricao', 'Plataforma web de controlo de equipamentos médicos com pesquisa avançada, rastreabilidade e apoio ao planeamento de manutenção.') ?></p>
    </div>

    <div class="mhs-grid-3">
        <article class="mhs-info-card">
            <div class="mhs-info-icon"><i class="fa-solid fa-chart-pie"></i></div>
            <h4 data-i18n="prod-c1-title">Visão Global</h4>
            <p data-i18n="prod-c1-desc">Dashboard com indicadores de equipamentos por estado, criticidade e serviço hospitalar.</p>
        </article>
        <article class="mhs-info-card">
            <div class="mhs-info-icon"><i class="fa-solid fa-route"></i></div>
            <h4 data-i18n="prod-c2-title">Rastreabilidade</h4>
            <p data-i18n="prod-c2-desc">Cada equipamento com localização, fornecedor, documentação e histórico completo.</p>
        </article>
        <article class="mhs-info-card">
            <div class="mhs-info-icon"><i class="fa-solid fa-lock"></i></div>
            <h4>Segurança</h4>
            <p>Controlo de acessos por sessão autenticada e encriptação de dados sensíveis em repouso.</p>
        </article>
    </div>
</section>

<!-- secao das funcionalidades -->
<section class="mhs-section--alt" id="funcionalidades">
    <div class="mhs-section-header">
        <p class="mhs-overline"><i class="fa-solid fa-list-check"></i> <span data-i18n="feat-overline">Funcionalidades</span></p>
        <h2 data-i18n="feat-title">Módulos do sistema</h2>
        <p data-i18n="feat-desc">Cada módulo segue o padrão CRUD com validação dupla, indicadores claros e operação orientada a equipas hospitalares.</p>
    </div>

    <div class="mhs-grid-4">
        <article class="mhs-feat-card">
            <div class="mhs-feat-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <h4 data-i18n="feat-1-title">Equipamentos</h4>
            <p data-i18n="feat-1-desc">Código de inventário, estado clínico, criticidade e ficha detalhada completa.</p>
        </article>
        <article class="mhs-feat-card">
            <div class="mhs-feat-icon"><i class="fa-solid fa-location-dot"></i></div>
            <h4 data-i18n="feat-2-title">Localizações</h4>
            <p data-i18n="feat-2-desc">Mapa lógico por edifício, piso, serviço e sala do hospital.</p>
        </article>
        <article class="mhs-feat-card">
            <div class="mhs-feat-icon"><i class="fa-solid fa-truck-field"></i></div>
            <h4 data-i18n="feat-3-title">Fornecedores</h4>
            <p data-i18n="feat-3-desc">Fabricantes, distribuidores e assistência técnica com relação N:N.</p>
        </article>
        <article class="mhs-feat-card">
            <div class="mhs-feat-icon"><i class="fa-solid fa-folder-open"></i></div>
            <h4 data-i18n="feat-4-title">Documentação</h4>
            <p data-i18n="feat-4-desc">Manuais, certificados e contratos com validade e alertas automáticos.</p>
        </article>
        <article class="mhs-feat-card">
            <div class="mhs-feat-icon"><i class="fa-solid fa-file-contract"></i></div>
            <h4 data-i18n="feat-5-title">Garantias</h4>
            <p data-i18n="feat-5-desc">Contratos de manutenção com prazos e alertas de expiração.</p>
        </article>
        <article class="mhs-feat-card">
            <div class="mhs-feat-icon"><i class="fa-solid fa-wrench"></i></div>
            <h4 data-i18n="feat-6-title">Manutenções</h4>
            <p data-i18n="feat-6-desc">Registo de manutenções preventivas e urgências com histórico completo.</p>
        </article>
        <article class="mhs-feat-card">
            <div class="mhs-feat-icon"><i class="fa-solid fa-headset"></i></div>
            <h4 data-i18n="feat-7-title">Assistência Técnica</h4>
            <p data-i18n="feat-7-desc">Contactos de AT com linha de urgência e acesso rápido por equipamento.</p>
        </article>
        <article class="mhs-feat-card">
            <div class="mhs-feat-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
            <h4 data-i18n="feat-8-title">Pesquisa Avançada</h4>
            <p data-i18n="feat-8-desc">Filtros combinados por código, estado, criticidade, fornecedor e mais.</p>
        </article>
    </div>
</section>

<!-- secao do setor da saude -->
<section class="mhs-section" id="setor-saude">
    <div class="mhs-split">
        <div class="mhs-split-text">
            <p class="mhs-overline"><i class="fa-solid fa-hospital"></i> <span>Contexto hospitalar</span></p>
            <h2><?= website_cfg($wcfg, 'setor_titulo', 'Desenhado para o setor da saúde') ?></h2>
            <p><?= website_cfg($wcfg, 'setor_descricao', 'Uma solução pensada para as necessidades reais dos serviços hospitalares, com foco na fiabilidade da informação e na rapidez de operação das equipas técnicas.') ?></p>
            <ul class="mhs-checks">
                <li><i class="fa-solid fa-circle-check"></i> <span><?= website_cfg($wcfg, 'setor_check_1', 'Informação sempre atualizada e consistente') ?></span></li>
                <li><i class="fa-solid fa-circle-check"></i> <span><?= website_cfg($wcfg, 'setor_check_2', 'Apoio à decisão operacional das equipas clínicas') ?></span></li>
                <li><i class="fa-solid fa-circle-check"></i> <span><?= website_cfg($wcfg, 'setor_check_3', 'Classificação por criticidade incluindo suporte de vida') ?></span></li>
                <li><i class="fa-solid fa-circle-check"></i> <span>Notificações automáticas por garantias e manutenções</span></li>
            </ul>
        </div>
        <div class="mhs-split-visual">
            <div class="mhs-sector-stats">
                <div class="mhs-sector-stat">
                    <div class="mhs-sector-stat-icon"><i class="fa-solid fa-stethoscope"></i></div>
                    <div>
                        <strong>Inventário centralizado</strong>
                        <span>Todos os equipamentos num único painel, com histórico completo e rastreabilidade.</span>
                    </div>
                </div>
                <div class="mhs-sector-stat">
                    <div class="mhs-sector-stat-icon"><i class="fa-solid fa-file-contract"></i></div>
                    <div>
                        <strong>Contratos e garantias</strong>
                        <span>Alertas automáticos antes de expiração de contratos, garantias e calibrações.</span>
                    </div>
                </div>
                <div class="mhs-sector-stat">
                    <div class="mhs-sector-stat-icon"><i class="fa-solid fa-wrench"></i></div>
                    <div>
                        <strong>Manutenções planeadas</strong>
                        <span>Registo preventivo e corretivo com associação direta ao equipamento.</span>
                    </div>
                </div>
                <div class="mhs-sector-stat">
                    <div class="mhs-sector-stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <strong>Dados encriptados</strong>
                        <span>Informação sensível protegida por encriptação AES e acessos autenticados.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- secao do formulario de contacto -->
<section class="mhs-section--alt" id="contacto">
    <div class="mhs-section-header">
        <p class="mhs-overline"><i class="fa-solid fa-envelope"></i> <span>Contacto</span></p>
        <h2>Fale connosco</h2>
        <p><?= website_cfg($wcfg, 'contacto_descricao', 'Entre em contacto para agendar uma demonstração ou esclarecer dúvidas sobre o sistema.') ?></p>
    </div>

    <?php if ($contacto_msg !== ''): ?>
    <div class="mhs-form-alert mhs-form-alert--<?= $contacto_tipo === 'sucesso' ? 'success' : 'danger' ?>">
        <i class="fa-solid <?= $contacto_tipo === 'sucesso' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
        <?= htmlspecialchars($contacto_msg) ?>
    </div>
    <?php endif; ?>

    <?php if ($contacto_tipo !== 'sucesso'): ?>
    <form class="mhs-form" method="post" action="index.php#contacto" novalidate>
        <div class="mhs-form-row">
            <div class="mhs-form-group">
                <label for="nome"><span data-i18n="form-name">Nome</span> <span class="mhs-required">*</span></label>
                <div class="mhs-input-wrap">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="nome" name="nome"
                        data-placeholder-pt="O seu nome"
                        data-placeholder-en="Your name"
                        placeholder="O seu nome"
                        value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mhs-form-group">
                <label for="email">Email <span class="mhs-required">*</span></label>
                <div class="mhs-input-wrap">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" id="email" name="email"
                        data-placeholder-pt="email@exemplo.pt"
                        data-placeholder-en="email@example.com"
                        placeholder="email@exemplo.pt"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
        </div>
        <div class="mhs-form-group">
            <label for="mensagem"><span data-i18n="form-message">Mensagem</span> <span class="mhs-required">*</span></label>
            <textarea id="mensagem" name="mensagem" rows="5"
                data-placeholder-pt="Descreva o que pretende..."
                data-placeholder-en="Describe what you need..."
                placeholder="Descreva o que pretende..." required><?= htmlspecialchars($_POST['mensagem'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="mhs-btn-primary mhs-btn-submit">
            <i class="fa-solid fa-paper-plane"></i>
            <span data-i18n="form-submit">Enviar Mensagem</span>
        </button>
    </form>
    <?php endif; ?>
</section>

<!-- rodape (footer) -->
<footer class="mhs-footer">
    <div class="mhs-footer-inner">
        <div class="mhs-footer-brand">
            <div class="mhs-footer-logo">
                <img src="assets/images/logo-medsoft-white.svg" alt="MedSolutions">
            </div>
            <div>
                <strong>MedSolutions</strong>
                <span data-i18n="footer-tagline">Inventário hospitalar inteligente</span>
            </div>
        </div>
        <div class="mhs-footer-cols">
            <div>
                <strong data-i18n="footer-col1">Empresa</strong>
                <p>MedSolutions</p>
                <p><?= website_cfg($wcfg, 'footer_morada', 'Porto, Portugal') ?></p>
            </div>
            <div>
                <strong data-i18n="footer-col2">Contactos</strong>
                <p><?= website_cfg($wcfg, 'footer_email', 'geral@medsolutions.pt') ?></p>
                <p><?= website_cfg($wcfg, 'footer_telefone', '+351 220 600 700') ?></p>
            </div>
            <div>
                <strong data-i18n="footer-col3">Produto</strong>
                <p>MedSolutions v1.0</p>
                <p>&copy; <?= date('Y') ?> MedSolutions</p>
            </div>
        </div>
    </div>
</footer>

<!-- menu para telemovel -->
<div class="mhs-mobile-sheet" id="mobileSheet" aria-hidden="true">
    <a href="#quem-somos" class="mhs-mobile-sheet-link"><i class="fa-solid fa-house"></i><span data-i18n="nav-about-short">Home</span></a>
    <a href="#produto" class="mhs-mobile-sheet-link"><i class="fa-solid fa-box-open"></i><span data-i18n="nav-product">O Produto</span></a>
    <a href="#funcionalidades" class="mhs-mobile-sheet-link"><i class="fa-solid fa-list-check"></i><span data-i18n="nav-features">Funcionalidades</span></a>
    <a href="#setor-saude" class="mhs-mobile-sheet-link"><i class="fa-solid fa-hospital"></i><span data-i18n="nav-sector">Setor Saúde</span></a>
    <a href="#contacto" class="mhs-mobile-sheet-link"><i class="fa-solid fa-envelope"></i><span data-i18n="nav-contact">Contacto</span></a>
    <a href="login.php" class="mhs-mobile-sheet-link"><i class="fa-regular fa-user"></i><span>Entrar</span></a>
</div>

<nav class="mhs-mobile-tabbar" aria-label="Navegação mobile">
    <a href="#quem-somos" class="mhs-mobile-tab"><i class="fa-solid fa-house"></i><span data-i18n="nav-about-short">Home</span></a>
    <a href="#funcionalidades" class="mhs-mobile-tab"><i class="fa-solid fa-list-check"></i><span data-i18n="nav-features-short">Módulos</span></a>
    <button class="mhs-mobile-menu-btn" id="mobileMenuBtn" type="button" aria-label="Menu" aria-expanded="false" aria-controls="mobileSheet">
        <i class="fa-solid fa-bars"></i>
    </button>
    <a href="login.php" class="mhs-mobile-tab"><i class="fa-regular fa-user"></i><span data-i18n="nav-login-short">Entrar</span></a>
    <a href="#contacto" class="mhs-mobile-tab"><i class="fa-solid fa-envelope"></i><span data-i18n="nav-contact-short">Contacto</span></a>
</nav>

<!-- scripts -->
<script>
(function() {
    var mainNav   = document.getElementById('mainNav');
    var burgerBtn = document.getElementById('burgerBtn');
    var mobileMenuBtn = document.getElementById('mobileMenuBtn');
    var mobileNavBackdrop = document.getElementById('mobileNavBackdrop');
    var mobileSheet = document.getElementById('mobileSheet');

    function setSheetOpen(open) {
        if (!mobileSheet) return;
        mobileSheet.classList.toggle('is-open', open);
        mobileSheet.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    function toggleMenu() {
        var open = !document.body.classList.contains('mhs-mobile-nav-open');
        document.body.classList.toggle('mhs-mobile-nav-open', open);
        if (mainNav) mainNav.classList.toggle('mhs-nav--open', open);
        setSheetOpen(open);
    }
    function closeMenu() {
        document.body.classList.remove('mhs-mobile-nav-open');
        if (mainNav) mainNav.classList.remove('mhs-nav--open');
        setSheetOpen(false);
    }

    if (burgerBtn) burgerBtn.addEventListener('click', toggleMenu);
    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMenu);
    if (mobileNavBackdrop) mobileNavBackdrop.addEventListener('click', closeMenu);

    document.querySelectorAll('.mhs-nav a, .mhs-mobile-sheet a').forEach(function(a) {
        a.addEventListener('click', closeMenu);
    });

    window.addEventListener('resize', function() { if (window.innerWidth > 760) closeMenu(); });

    window.addEventListener('scroll', function() {
        var tb = document.querySelector('.mhs-topbar');
        if (tb) tb.classList.toggle('mhs-topbar--scrolled', window.scrollY > 40);
    }, { passive: true });

    /* ── Scroll-reveal: secções/cards aparecem ao entrar no ecrã ── */
    var revealEls = document.querySelectorAll(
        '.mhs-section-header, .mhs-info-card, .mhs-feat-card, .mhs-sector-stat, .mhs-split-text, .mhs-split-visual, .mhs-trust-bar'
    );
    if (revealEls.length && 'IntersectionObserver' in window) {
        document.body.classList.add('reveal-ready');
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
                if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(function(el) {
            el.classList.add('reveal');
            // pequeno efeito escalonado dentro do mesmo grupo
            var sib = el.parentElement ? Array.prototype.indexOf.call(el.parentElement.children, el) : 0;
            el.style.transitionDelay = (Math.min(sib, 6) * 60) + 'ms';
            io.observe(el);
        });
    }

    /* ── Tilt 3D suave no screenshot do hero (só desktop) ── */
    var heroVisual = document.querySelector('.mhs-hero-visual');
    var shot = document.querySelector('.mhs-screenshot-wrap');
    if (heroVisual && shot && window.matchMedia('(min-width:900px)').matches &&
        !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        heroVisual.addEventListener('mousemove', function(ev) {
            var r = heroVisual.getBoundingClientRect();
            var rx = (((ev.clientY - r.top) / r.height) - 0.5) * -6;
            var ry = (((ev.clientX - r.left) / r.width) - 0.5) * 8;
            shot.style.transform = 'perspective(900px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
        });
        heroVisual.addEventListener('mouseleave', function() { shot.style.transform = ''; });
    }
})();
</script>
</body>
</html>
