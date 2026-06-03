<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Carrega conteúdo configurável da BD (com fallback para valores padrão)
function website_cfg(array &$cfg, string $chave, string $default): string {
    return htmlspecialchars($cfg[$chave] ?? $default, ENT_QUOTES, 'UTF-8');
}

$wcfg = [];
try {
    $wpdo = new PDO(
        'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
        MYSQL_USERNAME, MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
    );
    $rows = $wpdo->query("SELECT chave, valor FROM website_config") ?: [];
    foreach ($rows as $r) { $wcfg[$r->chave] = $r->valor; }
} catch (Exception $e) { /* tabela ainda não existe — usa defaults */ }

// Processamento do formulário de contacto
$contacto_msg  = '';
$contacto_tipo = '';

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
    } else {
        try {
            $stmt = $wpdo->prepare(
                "INSERT INTO mensagens_contacto (nome, email, mensagem, lida, created_at, updated_at)
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
    <title>MedSolutions &mdash; Inventário Hospitalar</title>
    <link rel="shortcut icon" href="assets/images/logo-medsoft.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

    <div class="mhs-mobile-nav-backdrop" id="mobileNavBackdrop"></div>

    <header class="mhs-topbar">
        <div class="mhs-topbar-inner">
            <a href="#" class="mhs-logo-link">
                <img src="assets/images/logo-medsoft.svg" alt="Logotipo MedSolutions">
                <span>MedSolutions</span>
            </a>

            <nav class="mhs-nav" id="mainNav">
                <a href="#quem-somos">Quem Somos</a>
                <a href="#produto">O Produto</a>
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#setor-saude">Setor Saúde</a>
                <a href="#contacto">Contacto</a>
            </nav>

            <div class="mhs-lang-switch" aria-label="Selecionar idioma">
                <button class="mhs-lang-btn is-active" type="button" data-lang="pt">PT</button>
                <button class="mhs-lang-btn" type="button" data-lang="en">EN</button>
            </div>

            <a href="login.php" class="mhs-btn-outline">Entrar na Plataforma</a>

            <button class="mhs-burger" id="burgerBtn" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <section class="mhs-hero" id="quem-somos">
        <div class="mhs-hero-content">
            <p class="mhs-overline"><?= website_cfg($wcfg, 'hero_overline', 'Tecnologia para hospitais') ?></p>
            <h1><?= website_cfg($wcfg, 'hero_titulo', 'Inventário hospitalar com') ?> <strong><?= website_cfg($wcfg, 'hero_titulo_strong', 'visão clara e operação rápida') ?></strong></h1>
            <p class="mhs-hero-sub"><?= website_cfg($wcfg, 'hero_subtitulo', 'Centralize equipamentos, contratos, localizações e documentação técnica num painel desenhado para equipas hospitalares.') ?></p>
            <div class="mhs-hero-actions">
                <a href="#produto" class="mhs-btn-primary">Conhecer o Produto</a>
                <a href="login.php" class="mhs-btn-ghost">Aceder ao Painel <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="mhs-hero-metrics">
                <div class="mhs-hero-metric">
                    <strong>Dashboard</strong>
                    <span>KPIs e alertas em tempo real</span>
                </div>
                <div class="mhs-hero-metric">
                    <strong>Inventário</strong>
                    <span>Estados, localização e criticidade</span>
                </div>
                <div class="mhs-hero-metric">
                    <strong>Contratos</strong>
                    <span>Gestão preventiva de garantias</span>
                </div>
            </div>
        </div>

    </section>

    <section class="mhs-section" id="produto">
        <div class="mhs-section-header">
            <p class="mhs-overline">O produto</p>
            <h2>MedInventar</h2>
            <p><?= website_cfg($wcfg, 'produto_descricao', 'Plataforma web de controlo de equipamentos médicos com pesquisa avançada, rastreabilidade e apoio ao planeamento de manutenção.') ?></p>
        </div>

        <div class="mhs-grid-3">
            <article class="mhs-info-card">
                <div class="mhs-info-icon"><i class="fa-solid fa-chart-pie"></i></div>
                <h4>Visão Global</h4>
                <p>Dashboard com indicadores de equipamentos por estado, criticidade e serviço hospitalar.</p>
            </article>
            <article class="mhs-info-card">
                <div class="mhs-info-icon"><i class="fa-solid fa-route"></i></div>
                <h4>Rastreabilidade</h4>
                <p>Cada equipamento com localização, fornecedor, documentação e histórico completo.</p>
            </article>
            <article class="mhs-info-card">
                <div class="mhs-info-icon"><i class="fa-solid fa-user-shield"></i></div>
                <h4>Governança</h4>
                <p>Perfis admin e técnico com controlo de acesso por sessão PHP e encriptação AES.</p>
            </article>
        </div>
    </section>

    <section class="mhs-section mhs-section--alt" id="funcionalidades">
        <div class="mhs-section-header">
            <p class="mhs-overline">Funcionalidades</p>
            <h2>Módulos do sistema</h2>
            <p>Cada módulo segue o padrão CRUD com validação dupla, indicadores claros e operação orientada a equipas hospitalares.</p>
        </div>

        <div class="mhs-grid-4">
            <article class="mhs-feat-card">
                <div class="mhs-feat-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <h4>Equipamentos</h4>
                <p>Código de inventário, estado clínico, criticidade e ficha detalhada completa.</p>
            </article>
            <article class="mhs-feat-card">
                <div class="mhs-feat-icon"><i class="fa-solid fa-location-dot"></i></div>
                <h4>Localizações</h4>
                <p>Mapa lógico por edifício, piso, serviço e sala do hospital.</p>
            </article>
            <article class="mhs-feat-card">
                <div class="mhs-feat-icon"><i class="fa-solid fa-truck-field"></i></div>
                <h4>Fornecedores</h4>
                <p>Fabricantes, distribuidores e assistência técnica com relação N:N.</p>
            </article>
            <article class="mhs-feat-card">
                <div class="mhs-feat-icon"><i class="fa-solid fa-folder-open"></i></div>
                <h4>Documentação</h4>
                <p>Manuais, certificados e contratos com validade e alertas.</p>
            </article>
            <article class="mhs-feat-card">
                <div class="mhs-feat-icon"><i class="fa-solid fa-file-contract"></i></div>
                <h4>Garantias</h4>
                <p>Contratos de manutenção com prazos e alertas de expiração.</p>
            </article>
            <article class="mhs-feat-card">
                <div class="mhs-feat-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
                <h4>Pesquisa Avançada</h4>
                <p>Filtros combinados por código, estado, criticidade, fornecedor e mais.</p>
            </article>
            <article class="mhs-feat-card">
                <div class="mhs-feat-icon"><i class="fa-solid fa-tags"></i></div>
                <h4>Categorias</h4>
                <p>Classificação funcional de equipamentos por tipo clínico.</p>
            </article>
            <article class="mhs-feat-card">
                <div class="mhs-feat-icon"><i class="fa-solid fa-gauge-high"></i></div>
                <h4>Dashboard</h4>
                <p>KPIs: totais, ativos, em manutenção, garantias expiradas e visão por serviço.</p>
            </article>
        </div>
    </section>

    <section class="mhs-section" id="setor-saude">
        <div class="mhs-split">
            <div class="mhs-split-text">
                <p class="mhs-overline">Contexto hospitalar</p>
                <h2><?= website_cfg($wcfg, 'setor_titulo', 'Desenhado para o setor da saúde') ?></h2>
                <p><?= website_cfg($wcfg, 'setor_descricao', 'Uma solução pensada para as necessidades reais dos serviços hospitalares, com foco na fiabilidade da informação e na rapidez de operação das equipas técnicas.') ?></p>
                <ul class="mhs-checks">
                    <li><i class="fa-solid fa-circle-check"></i> <?= website_cfg($wcfg, 'setor_check_1', 'Informação sempre atualizada e consistente') ?></li>
                    <li><i class="fa-solid fa-circle-check"></i> <?= website_cfg($wcfg, 'setor_check_2', 'Apoio à decisão operacional das equipas clínicas') ?></li>
                    <li><i class="fa-solid fa-circle-check"></i> <?= website_cfg($wcfg, 'setor_check_3', 'Classificação por criticidade incluindo suporte de vida') ?></li>
                </ul>
            </div>
            <div class="mhs-split-visual">
                <div class="mhs-badge-stack">
                    <span class="mhs-badge mhs-badge--green">Ativo</span>
                    <span class="mhs-badge mhs-badge--amber">Em manutenção</span>
                    <span class="mhs-badge mhs-badge--red">Inativo</span>
                    <span class="mhs-badge mhs-badge--dark">Suporte de vida</span>
                    <span class="mhs-badge mhs-badge--blue">Em calibração</span>
                    <span class="mhs-badge mhs-badge--gray">Abatido</span>
                </div>
            </div>
        </div>
    </section>

    <section class="mhs-section mhs-section--alt" id="contacto">
        <div class="mhs-section-header">
            <p class="mhs-overline">Contacto</p>
            <h2>Fale connosco</h2>
            <p><?= website_cfg($wcfg, 'contacto_descricao', 'Entre em contacto para agendar uma demonstração ou esclarecer dúvidas sobre o sistema.') ?></p>
        </div>

        <?php if ($contacto_msg !== ''): ?>
        <div class="mhs-form-alert mhs-form-alert--<?= $contacto_tipo === 'sucesso' ? 'success' : 'danger' ?>">
            <i class="fa-solid <?= $contacto_tipo === 'sucesso' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i>
            <?= htmlspecialchars($contacto_msg) ?>
        </div>
        <?php endif; ?>

        <?php if ($contacto_tipo !== 'sucesso'): ?>
        <form class="mhs-form" method="post" action="index.php#contacto" novalidate>
            <div class="mhs-form-row">
                <div class="mhs-form-group">
                    <label for="nome">Nome <span class="mhs-required">*</span></label>
                    <input type="text" id="nome" name="nome" placeholder="O seu nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                </div>
                <div class="mhs-form-group">
                    <label for="email">Email <span class="mhs-required">*</span></label>
                    <input type="email" id="email" name="email" placeholder="email@exemplo.pt" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mhs-form-group">
                <label for="mensagem">Mensagem <span class="mhs-required">*</span></label>
                <textarea id="mensagem" name="mensagem" rows="4" placeholder="Descreva o que pretende..." required><?= htmlspecialchars($_POST['mensagem'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="mhs-btn-primary">Enviar Mensagem</button>
        </form>
        <?php endif; ?>
    </section>

    <footer class="mhs-footer">
        <div class="mhs-footer-inner">
            <div class="mhs-footer-brand">
                <img src="assets/images/logo-medsoft-white.svg" alt="MedSolutions">
                <span>MedSolutions</span>
            </div>
            <div class="mhs-footer-cols">
                <div>
                    <strong>Empresa</strong>
                    <p>MedSolutions</p>
                    <p><?= website_cfg($wcfg, 'footer_morada', 'Porto, Portugal') ?></p>
                </div>
                <div>
                    <strong>Contactos</strong>
                    <p><?= website_cfg($wcfg, 'footer_email', 'geral@medsolutions.pt') ?></p>
                    <p><?= website_cfg($wcfg, 'footer_telefone', '+351 220 600 700') ?></p>
                </div>
                <div>
                    <strong>Produto</strong>
                    <p>MedInventar v1.0</p>
                    <p>&copy; 2026 MedSolutions</p>
                </div>
            </div>
        </div>
    </footer>

    <div class="mhs-mobile-sheet" id="mobileSheet" aria-hidden="true">
        <a href="#quem-somos" class="mhs-mobile-sheet-link">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="#produto" class="mhs-mobile-sheet-link">
            <i class="fa-solid fa-box-open"></i>
            <span>Produto</span>
        </a>
        <a href="#funcionalidades" class="mhs-mobile-sheet-link">
            <i class="fa-solid fa-list-check"></i>
            <span>Funcionalidades</span>
        </a>
        <a href="#setor-saude" class="mhs-mobile-sheet-link">
            <i class="fa-solid fa-hospital"></i>
            <span>Setor Saúde</span>
        </a>
        <a href="#contacto" class="mhs-mobile-sheet-link">
            <i class="fa-solid fa-headset"></i>
            <span>Contacto</span>
        </a>
        <a href="login.php" class="mhs-mobile-sheet-link">
            <i class="fa-regular fa-user"></i>
            <span>Entrar</span>
        </a>
        <div class="mhs-mobile-sheet-lang" aria-label="Selecionar idioma">
            <button class="mhs-lang-btn is-active" type="button" data-lang="pt">PT</button>
            <button class="mhs-lang-btn" type="button" data-lang="en">EN</button>
        </div>
    </div>

    <nav class="mhs-mobile-tabbar" aria-label="Navegação mobile">
        <a href="#quem-somos" class="mhs-mobile-tab">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="#funcionalidades" class="mhs-mobile-tab">
            <i class="fa-solid fa-list-check"></i>
            <span>Módulos</span>
        </a>
        <button class="mhs-mobile-menu-btn" id="mobileMenuBtn" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="mobileSheet">
            <i class="fa-solid fa-bars"></i>
        </button>
        <a href="login.php" class="mhs-mobile-tab">
            <i class="fa-regular fa-user"></i>
            <span>Entrar</span>
        </a>
        <a href="#contacto" class="mhs-mobile-tab">
            <i class="fa-solid fa-headset"></i>
            <span>Contactar</span>
        </a>
    </nav>

    <script>
        const mainNav = document.getElementById('mainNav');
        const burgerBtn = document.getElementById('burgerBtn');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileNavBackdrop = document.getElementById('mobileNavBackdrop');
        const mobileSheet = document.getElementById('mobileSheet');
        const langButtons = document.querySelectorAll('.mhs-lang-btn');

        function setMobileSheetState(isOpen) {
            if (!mobileSheet) {
                return;
            }

            mobileSheet.classList.toggle('is-open', isOpen);
            mobileSheet.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            mobileSheet.style.position = 'fixed';
            mobileSheet.style.left = '1rem';
            mobileSheet.style.right = '1rem';
            mobileSheet.style.bottom = '88px';
            mobileSheet.style.zIndex = '1200';
            mobileSheet.style.display = isOpen ? 'grid' : 'none';
            mobileSheet.style.gridTemplateColumns = '1fr 1fr';
            mobileSheet.style.gap = '.7rem';
            mobileSheet.style.padding = '1rem';
            mobileSheet.style.border = '1px solid rgba(255,255,255,.95)';
            mobileSheet.style.borderRadius = '22px';
            mobileSheet.style.background = 'rgba(255,255,255,.98)';
            mobileSheet.style.boxShadow = '0 24px 60px rgba(15,23,42,.22)';
            mobileSheet.style.visibility = isOpen ? 'visible' : 'hidden';
            mobileSheet.style.opacity = isOpen ? '1' : '0';
            mobileSheet.style.pointerEvents = isOpen ? 'auto' : 'none';
        }

        function toggleMenu() {
            const isOpen = !document.body.classList.contains('mhs-mobile-nav-open');
            document.body.classList.toggle('mhs-mobile-nav-open', isOpen);
            mainNav.classList.toggle('mhs-nav--open', isOpen);
            setMobileSheetState(isOpen);
            if (mobileMenuBtn) {
                mobileMenuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
        }

        function closeMenu() {
            document.body.classList.remove('mhs-mobile-nav-open');
            mainNav.classList.remove('mhs-nav--open');
            setMobileSheetState(false);
            if (mobileMenuBtn) {
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            }
        }

        setMobileSheetState(false);

        if (burgerBtn) {
            burgerBtn.addEventListener('click', toggleMenu);
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleMenu);
        }

        mainNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                closeMenu();
            });
        });

        if (mobileSheet) {
            mobileSheet.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    closeMenu();
                });
            });
        }

        if (mobileNavBackdrop) {
            mobileNavBackdrop.addEventListener('click', closeMenu);
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth > 760) {
                closeMenu();
            }
        });

        langButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                langButtons.forEach(function (item) {
                    item.classList.remove('is-active');
                });
                button.classList.add('is-active');
                document.documentElement.lang = button.dataset.lang === 'en' ? 'en' : 'pt';
            });
        });
    </script>
</body>
</html>
