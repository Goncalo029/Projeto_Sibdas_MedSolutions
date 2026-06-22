// MedInventar - funcoes.js
// Validacoes client-side e helpers visuais

// ============================================================
// Avatar dropdown
// ============================================================
function toggleAvatarMenu(e) {
    e.stopPropagation();
    var dropdown = document.getElementById('avatarDropdown');
    var menu = document.getElementById('avatarMenu');
    var isOpen = dropdown.classList.contains('open');
    closeNotifMenu();
    dropdown.classList.toggle('open', !isOpen);
    menu.classList.toggle('visible', !isOpen);
}

// ============================================================
// Notifications dropdown
// ============================================================
function toggleNotifMenu(e) {
    e.stopPropagation();
    var dropdown = document.getElementById('notifDropdown');
    var menu = document.getElementById('notifMenu');
    if (!dropdown || !menu) return;
    var isOpen = menu.classList.contains('visible');
    closeAvatarMenu();
    dropdown.classList.toggle('open', !isOpen);
    menu.classList.toggle('visible', !isOpen);
}

function closeNotifMenu() {
    var dropdown = document.getElementById('notifDropdown');
    var menu = document.getElementById('notifMenu');
    if (dropdown) dropdown.classList.remove('open');
    if (menu) menu.classList.remove('visible');
}

function closeAvatarMenu() {
    var dropdown = document.getElementById('avatarDropdown');
    var menu = document.getElementById('avatarMenu');
    if (dropdown) dropdown.classList.remove('open');
    if (menu) menu.classList.remove('visible');
}

document.addEventListener('click', function () {
    closeAvatarMenu();
    closeNotifMenu();
});

// ============================================================
// DataTables - inicializacao padrao PT
// ============================================================
$(document).ready(function () {
    if ($('.mhs-datatable').length) {
        $('.mhs-datatable').each(function () {
            const $table = $(this);
            const tableId = $table.attr('id');
            const customSearchInputs = tableId ? document.querySelectorAll('[data-table-search="' + tableId + '"]') : [];
            const dataTable = $table.DataTable({
                language: {
                    search: '',
                    searchPlaceholder: 'Pesquisar',
                    lengthMenu: '_MENU_ registos',
                    info: 'A mostrar _START_ a _END_ de _TOTAL_ registos',
                    infoEmpty: 'Sem registos',
                    infoFiltered: '(filtrado de _MAX_ total)',
                    zeroRecords: 'Nenhum registo encontrado',
                    emptyTable: 'Sem dados disponiveis',
                    paginate: { first: 'Primeiro', last: 'Ultimo', next: 'Seguinte', previous: 'Anterior' }
                },
                pageLength: 15
            });

            if (customSearchInputs.length) {
                $table.closest('.dataTables_wrapper').addClass('mhs-datatable--custom-search');
                customSearchInputs.forEach(function (input) {
                    input.addEventListener('input', function () {
                        dataTable.search(this.value).draw();
                    });
                });
            }

            // Vinculação de campos de pesquisa por tabela (novos filtros)
            const searchFieldId = tableId ? tableId.replace('Table', 'Search') : null;
            if (searchFieldId) {
                const $searchField = document.getElementById(searchFieldId);
                if ($searchField) {
                    $searchField.addEventListener('input', function () {
                        dataTable.search(this.value).draw();
                    });
                }
            }
        });
    }
});

// ============================================================
// Flatpickr - inicializacao padrao para campos de data
// ============================================================
document.querySelectorAll('.mhs-datepicker').forEach(function (el) {
    var opts = {
        dateFormat: 'Y-m-d',
        allowInput: true,
        appendTo: document.body,
        locale: { firstDayOfWeek: 1 }
    };
    var maxD = el.getAttribute('data-maxdate');
    var minD = el.getAttribute('data-mindate');
    if (maxD) opts.maxDate = maxD === 'today' ? new Date() : maxD;
    if (minD) opts.minDate = minD === 'today' ? new Date() : minD;
    flatpickr(el, opts);
});

// ============================================================
// Toast automatico - fecha apos 4s
// ============================================================
document.querySelectorAll('.toast.show').forEach(function (toastEl) {
    setTimeout(function () {
        var toast = bootstrap.Toast.getOrCreateInstance(toastEl);
        toast.hide();
    }, 4000);
});

// ============================================================
// Modal de confirmacao de apagar (botoes data-delete-*)
// ============================================================
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-delete-id]');
    if (!btn) return;
    e.preventDefault();
    var deleteId = btn.getAttribute('data-delete-id');
    var deleteName = btn.getAttribute('data-delete-name') || '';
    var deleteUrl = btn.getAttribute('data-delete-url') || 'confirmar_apagar.php';
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var modal = document.getElementById('mhsDeleteModal');
    if (!modal) return;
    document.getElementById('mhsDeleteForm').setAttribute('action', deleteUrl);
    document.getElementById('mhsDeleteId').value = deleteId;
    document.getElementById('mhsDeleteName').textContent = deleteName;
    document.getElementById('mhsDeleteCsrf').value = csrfToken;
    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();
});

// ============================================================
// Tab switcher com validação — global para todas as páginas
// ============================================================
(function () {
    var TODAY_STR = new Date().toISOString().slice(0, 10);
    var CURRENT_YEAR = new Date().getFullYear();

    function clearTabErrors(pane) {
        if (!pane) return;
        pane.querySelectorAll('.mhs-field-error').forEach(function (el) { el.classList.remove('visible'); });
        pane.querySelectorAll('.mhs-invalid').forEach(function (el) { el.classList.remove('mhs-invalid'); });
    }

    function showFieldError(field, errId) {
        if (field) field.classList.add('mhs-invalid');
        var errEl = errId ? document.getElementById(errId) : null;
        if (errEl) errEl.classList.add('visible');
    }

    function mhsValidateTab(tabName) {
        var pane = document.getElementById('tab-' + tabName);
        if (!pane) return true;
        clearTabErrors(pane);
        var ok = true;
        var firstBad = null;

        pane.querySelectorAll('[required]').forEach(function (field) {
            if (!field.value.trim()) {
                showFieldError(field, 'err_' + (field.name || field.id));
                if (!firstBad) firstBad = field;
                ok = false;
            }
        });

        if (tabName === 'ficha') {
            var codFld = pane.querySelector('[name="codigo_inventario"]');
            if (codFld && codFld.value.trim() && !/^EQ-\d{3,}$/.test(codFld.value.trim())) {
                var errCod = document.getElementById('err_codigo_inventario');
                if (errCod) errCod.textContent = 'Formato inválido. Use EQ-001, EQ-002, etc.';
                showFieldError(codFld, 'err_codigo_inventario');
                if (!firstBad) firstBad = codFld;
                ok = false;
            }
            var serFld = pane.querySelector('[name="numero_serie"]');
            if (serFld && serFld.value.trim() && !/^[A-Z0-9]{2,}-\d{4}-\d{3,}$/.test(serFld.value.trim())) {
                var errSer = document.getElementById('err_numero_serie');
                if (errSer) errSer.textContent = 'Formato inválido. Use ex: SN-2024-001';
                showFieldError(serFld, 'err_numero_serie');
                if (!firstBad) firstBad = serFld;
                ok = false;
            }
        }
        if (tabName === 'aquisicao') {
            var anoFld = pane.querySelector('[name="ano_fabrico"]');
            if (anoFld && anoFld.value.trim() && parseInt(anoFld.value) > CURRENT_YEAR) {
                showFieldError(anoFld, 'err_ano_fabrico');
                if (!firstBad) firstBad = anoFld;
                ok = false;
            }
            var dateFld = pane.querySelector('[name="data_aquisicao"]');
            if (dateFld && dateFld.value.trim() && dateFld.value > TODAY_STR) {
                showFieldError(dateFld, 'err_data_aquisicao');
                if (!firstBad) firstBad = dateFld;
                ok = false;
            }
            var custoFld = pane.querySelector('[name="custo_aquisicao"]');
            if (custoFld && custoFld.value.trim() && !/^[\d\s.,]+$/.test(custoFld.value.trim())) {
                showFieldError(custoFld, 'err_custo_aquisicao');
                if (!firstBad) firstBad = custoFld;
                ok = false;
            }
        }
        if (!ok && firstBad) firstBad.focus();
        return ok;
    }

    function mhsActivateTab(tabName, skipValidation) {
        var curBtn = document.querySelector('.mhs-detail-tab.active');
        var curTab = curBtn ? curBtn.dataset.tab : null;
        if (!skipValidation && curTab && curTab !== tabName) {
            if (!mhsValidateTab(curTab)) return;
        }
        var btn  = document.querySelector('.mhs-detail-tab[data-tab="' + tabName + '"]');
        var pane = document.getElementById('tab-' + tabName);
        if (!btn || !pane) return;
        document.querySelectorAll('.mhs-detail-tab').forEach(function (b) { b.classList.remove('active'); });
        document.querySelectorAll('.mhs-tab-pane').forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        pane.classList.add('active');
    }

    document.querySelectorAll('.mhs-detail-tab').forEach(function (btn) {
        btn.addEventListener('click', function () { mhsActivateTab(btn.dataset.tab, false); });
    });

    var tab = new URLSearchParams(window.location.search).get('tab');
    if (tab) mhsActivateTab(tab, true);
})();

// ============================================================
// Sidebar toggle (desktop) — botão abre/fecha sem hover
// ============================================================
function mhsToggleSidebar() {
    var closed = document.body.classList.toggle('mhs-sb-closed');
    try { localStorage.setItem('mhsSbClosed', closed ? '1' : '0'); } catch (e) {}
}
(function () {
    // Por defeito fechada; só abre se o utilizador tiver aberto explicitamente
    try { if (localStorage.getItem('mhsSbClosed') !== '0') { document.body.classList.add('mhs-sb-closed'); } } catch (e) { document.body.classList.add('mhs-sb-closed'); }
})();

// ============================================================
// Auto-preencher Equipamento
// nextCode e eqIdx calculados em PHP; locIds = array de IDs de localizações existentes
// ============================================================
function mhsAutoFillDemo(nextCode, eqIdx, locIds) {
    var YEAR = new Date().getFullYear();
    var dados = [
        { des:'Monitor de Sinais Vitais',   marc:'Philips',        pre:'PHL', mod:'IntelliVue MX40',  fab:'Philips Healthcare',    cat:'Monitorização',   ano:2022, cus:'4500.00',  ent:'Compra', est:'Ativo', crit:'Alta',            obs:'Equipamento de monitorização contínua de sinais vitais. Adquirido para UCI.',          at_emp:'Philips Healthcare Portugal',   at_nom:'João Costa',    at_tel:'213 456 789', at_eml:'suporte@philips.pt' },
        { des:'Ventilador Pulmonar',        marc:'Draeger',        pre:'DRA', mod:'Evita V800',       fab:'Draegerwerk AG',        cat:'Suporte de vida', ano:2021, cus:'18000.00', ent:'Compra', est:'Ativo', crit:'Suporte de vida', obs:'Ventilador de cuidados intensivos com suporte a múltiplos modos ventilatórios.',      at_emp:'Draeger Portugal Lda',          at_nom:'Pedro Sousa',   at_tel:'214 567 890', at_eml:'servico@draeger.pt' },
        { des:'Bomba Infusora',             marc:'Fresenius Kabi', pre:'FRE', mod:'Agilia SP MC',     fab:'Fresenius Kabi GmbH',  cat:'Terapia',         ano:2020, cus:'2200.00',  ent:'Doação', est:'Ativo', crit:'Média',            obs:'Bomba de seringa para administração controlada de medicação IV.',                     at_emp:'MedTech Solutions SA',          at_nom:'Ana Silva',     at_tel:'222 333 444', at_eml:'geral@medtech.pt' },
        { des:'Desfibrilhador',             marc:'Zoll',           pre:'ZLL', mod:'R Series',         fab:'Zoll Medical',          cat:'Suporte de vida', ano:2023, cus:'9800.00',  ent:'Compra', est:'Ativo', crit:'Suporte de vida', obs:'Desfibrilhador bifásico com monitor ECG integrado e suporte a RCP guiada.',          at_emp:'Zoll Medical Portugal',         at_nom:'Carla Nunes',   at_tel:'215 678 901', at_eml:'care@zoll.pt' },
        { des:'Oxímetro de Pulso',          marc:'Masimo',         pre:'MSM', mod:'Rad-97',           fab:'Masimo Corporation',    cat:'Monitorização',   ano:2022, cus:'1200.00',  ent:'Compra', est:'Ativo', crit:'Alta',             obs:'Oxímetro portátil de alta precisão com medição de SpO2, frequência cardíaca e CO.', at_emp:'Siemens Healthineers Portugal', at_nom:'Carlos Mendes', at_tel:'215 678 902', at_eml:'healthineers@siemens.pt' },
        { des:'Electrocardiograma',         marc:'GE Healthcare',  pre:'GEH', mod:'MAC 5500 HD',      fab:'GE Healthcare',         cat:'Diagnóstico',     ano:2021, cus:'6700.00',  ent:'Compra', est:'Ativo', crit:'Alta',             obs:'ECG de 12 derivações com interpretação automática e conectividade DICOM.',            at_emp:'GE Healthcare Portugal',        at_nom:'Rui Tavares',   at_tel:'213 567 890', at_eml:'service@gehealthcare.pt' },
        { des:'Ecógrafo Portátil',          marc:'Mindray',        pre:'MDR', mod:'TE7 Max',          fab:'Mindray Medical',       cat:'Imagiologia',     ano:2023, cus:'22000.00', ent:'Compra', est:'Ativo', crit:'Alta',             obs:'Ecógrafo de alto desempenho com sondas intercambiáveis e registo digital.',          at_emp:'Mindray Portugal',              at_nom:'Sandra Lima',   at_tel:'218 765 432', at_eml:'suporte@mindray.pt' },
        { des:'Cama Articulada Hospitalar', marc:'Stryker',        pre:'STR', mod:'ProCuity',         fab:'Stryker Corporation',   cat:'Reabilitação',    ano:2020, cus:'3800.00',  ent:'Compra', est:'Ativo', crit:'Baixa',            obs:'Cama hospitalar elétrica com grades laterais e sistema de pesagem integrado.',       at_emp:'Stryker Portugal',              at_nom:'Miguel Faria',  at_tel:'214 321 987', at_eml:'service@stryker.pt' }
    ];

    var idx = (eqIdx !== undefined && eqIdx !== null) ? eqIdx : 0;
    var d   = dados[idx % dados.length];
    var num = String(idx + 1).padStart(3, '0');
    var serie = d.pre + '-' + YEAR + '-' + num;

    var s = function(n, v) {
        var el = document.querySelector('[name="' + n + '"]');
        if (!el) return;
        el.value = v;
    };
    var sel = function(n, v) {
        var el = document.querySelector('[name="' + n + '"]');
        if (!el || el.tagName !== 'SELECT') return;
        for (var k = 0; k < el.options.length; k++) {
            if (el.options[k].value === v || el.options[k].text === v) { el.selectedIndex = k; return; }
        }
    };

    // Ficha Técnica
    s('codigo_inventario', nextCode || 'EQ-001');
    s('designacao', d.des); s('marca', d.marc); s('modelo', d.mod);
    s('numero_serie', serie); s('fabricante', d.fab);

    // Categoria — procura opção cujo texto contém o nome
    var catSel = document.querySelector('[name="id_categoria"]');
    if (catSel) {
        for (var i = 0; i < catSel.options.length; i++) {
            if (catSel.options[i].text.indexOf(d.cat) !== -1) { catSel.selectedIndex = i; break; }
        }
    }

    // Aquisição
    var today = new Date().toISOString().slice(0, 10);
    var dpEl = document.querySelector('[name="data_aquisicao"]');
    if (dpEl) { dpEl.value = today; if (dpEl._flatpickr) dpEl._flatpickr.setDate(today, false); }
    s('ano_fabrico', d.ano); s('custo_aquisicao', d.cus); sel('tipo_entrada', d.ent);

    // Localização e Estado
    if (locIds && locIds.length) {
        var locSel = document.querySelector('[name="id_localizacao"]');
        if (locSel) {
            var pickId = String(locIds[idx % locIds.length]);
            for (var j = 0; j < locSel.options.length; j++) {
                if (locSel.options[j].value === pickId) { locSel.selectedIndex = j; break; }
            }
        }
    }
    sel('estado', d.est); sel('criticidade', d.crit); s('observacoes', d.obs);

    // Assistência Técnica
    s('at_empresa', d.at_emp); s('at_nome_contacto', d.at_nom);
    s('at_telefone', d.at_tel); s('at_email', d.at_eml);
}

// ============================================================
// Auto-preencher Localização (existentes = array de "servico|sala" já na BD)
// ============================================================
function mhsAutoFillLocalizacao(existentes) {
    var dados = [
        { ed:'Bloco A',       piso:'Piso 1', srv:'Urgência',            sala:'Sala Técnica' },
        { ed:'Bloco B',       piso:'Piso 2', srv:'Cardiologia',         sala:'Sala de Exames' },
        { ed:'Bloco Central', piso:'Piso 0', srv:'Bloco Operatório',    sala:'Sala de Reanimação' },
        { ed:'Bloco C',       piso:'Piso 3', srv:'Pediatria',           sala:'Sala de Tratamentos' },
        { ed:'Bloco A',       piso:'Piso 2', srv:'UCI',                 sala:'Sala de Observação' },
        { ed:'Bloco B',       piso:'Piso 1', srv:'Oncologia',           sala:'Sala de Procedimentos' },
        { ed:'Bloco Central', piso:'Piso 2', srv:'Ortopedia',           sala:'Sala de Equipamentos' },
        { ed:'Bloco A',       piso:'Piso 3', srv:'Neurologia',          sala:'Sala de Diagnóstico' },
        { ed:'Bloco C',       piso:'Piso 1', srv:'Medicina Interna',    sala:'Sala de Apoio' },
        { ed:'Bloco B',       piso:'Piso 3', srv:'Radiologia',          sala:'Sala de Imagiologia' }
    ];
    var ex = Array.isArray(existentes) ? existentes : [];
    var d = null;
    for (var i = 0; i < dados.length; i++) {
        var chave = dados[i].srv + '|' + dados[i].sala;
        if (ex.indexOf(chave) === -1) { d = dados[i]; break; }
    }
    if (!d) { alert('Todos os dados de demonstração já foram usados.'); return; }
    var s = function(n, v) { var el = document.querySelector('[name="' + n + '"]'); if (el) el.value = v; };
    s('edificio', d.ed); s('piso', d.piso); s('servico', d.srv); s('sala', d.sala);
}

// ============================================================
// Auto-preencher Fornecedor (existentes = array de nomes já na BD)
// ============================================================
function mhsAutoFillFornecedor(existentes) {
    var dados = [
        { nome:'MedTech Solutions SA',          nif:'501234567', tipo:'Distribuidor',        tel:'222 333 444', email:'geral@medtech.pt',           morada:'Rua das Flores 12, 4000-001 Porto',                        web:'https://www.medtech.pt',              cont:'Ana Silva',       tcon:'912 345 678' },
        { nome:'Philips Healthcare Portugal',   nif:'502345678', tipo:'Fabricante',           tel:'213 456 789', email:'info@philips.pt',             morada:'Av. da Liberdade 110, 1250-001 Lisboa',                    web:'https://www.philips.pt',              cont:'João Costa',      tcon:'913 456 789' },
        { nome:'B. Braun Portugal Lda',         nif:'503456789', tipo:'Assistência Técnica',  tel:'214 567 890', email:'support@bbraun.pt',           morada:'Parque Industrial de Sintra, 2710-001 Sintra',             web:'https://www.bbraun.pt',               cont:'Maria Ferreira',  tcon:'914 567 890' },
        { nome:'Siemens Healthineers Portugal', nif:'504567890', tipo:'Fabricante',           tel:'215 678 901', email:'healthineers@siemens.pt',     morada:'Rua Alfredo da Silva 1, 2610-016 Amadora',                 web:'https://www.siemens-healthineers.pt', cont:'Carlos Mendes',   tcon:'915 678 901' },
        { nome:'Draeger Portugal Lda',          nif:'505678901', tipo:'Assistência Técnica',  tel:'214 789 012', email:'servico@draeger.pt',          morada:'Quinta da Fonte, Edif. D. João I, 2770-071 Paço de Arcos', web:'https://www.draeger.com',             cont:'Pedro Sousa',     tcon:'916 789 012' },
        { nome:'GE Healthcare Portugal',        nif:'506789012', tipo:'Fabricante',           tel:'213 890 123', email:'service@gehealthcare.pt',     morada:'Rua Galileu Galilei 2, 1500-392 Lisboa',                   web:'https://www.gehealthcare.com',        cont:'Rui Tavares',     tcon:'917 890 123' }
    ];
    var ex = Array.isArray(existentes) ? existentes : [];
    var d = null;
    for (var i = 0; i < dados.length; i++) {
        if (ex.indexOf(dados[i].nome) === -1) { d = dados[i]; break; }
    }
    if (!d) { alert('Todos os fornecedores de demonstração já foram inseridos.'); return; }
    var s = function(n, v) { var el = document.querySelector('[name="' + n + '"]'); if (el) el.value = v; };
    s('nome', d.nome); s('nif', d.nif); s('tipo_fornecedor', d.tipo);
    s('telefone', d.tel); s('email', d.email); s('morada', d.morada);
    s('website', d.web); s('pessoa_contacto', d.cont); s('tel_contacto', d.tcon);
}

// ============================================================
// Auto-preencher Garantia/Contrato (firstEqId vem de PHP)
// ============================================================
function mhsAutoFillGarantia(firstEqId) {
    var hoje = new Date();
    var fim  = new Date(); fim.setFullYear(fim.getFullYear() + 2);
    var fmt = function(d){ return d.toISOString().slice(0,10); };
    var s = function(n,v){ var el=document.querySelector('[name="'+n+'"]'); if(el) el.value=v; };
    var entidades = ['MedTech Solutions SA','Philips Healthcare Portugal','B. Braun Portugal Lda','Siemens Healthineers Portugal'];
    var tipos = ['Manutenção Preventiva','Manutenção Corretiva','Garantia Fabricante','Contrato de Suporte'];
    var per = ['Anual','Semestral','Trimestral'];
    var ri = Math.floor(Math.random()*entidades.length);
    if (firstEqId) s('id_equipamento', firstEqId);
    s('data_inicio', fmt(hoje)); s('data_fim', fmt(fim));
    s('tipo_contrato', tipos[ri % tipos.length]);
    s('entidade_responsavel', entidades[ri]);
    s('periodicidade', per[ri % per.length]);
    var cb = document.querySelector('[name="tem_contrato"]'); if(cb) cb.checked=true;
    ['data_inicio','data_fim'].forEach(function(n){
        var el=document.querySelector('[name="'+n+'"]'); if(el&&el._flatpickr) el._flatpickr.setDate(el.value,false);
    });
}

// ============================================================
// Auto-preencher Utilizador
// ============================================================
function mhsAutoFillUtilizador(n) {
    var dados = [
        { email:'tecnico01@medsolutions.pt', pw:'Tecnico2024!', perfil:'tecnico' },
        { email:'tecnico02@medsolutions.pt', pw:'Tecnico2024!', perfil:'tecnico' },
        { email:'tecnico03@medsolutions.pt', pw:'Tecnico2024!', perfil:'tecnico' },
        { email:'admin02@medsolutions.pt',   pw:'Admin2024!',   perfil:'admin'   },
        { email:'admin03@medsolutions.pt',   pw:'Admin2024!',   perfil:'admin'   }
    ];
    var idx = (n !== undefined && n !== null) ? n : 0;
    var d = dados[idx % dados.length];
    var s = function(n,v){ var el=document.querySelector('[name="'+n+'"]'); if(el) el.value=v; };
    s('email',d.email); s('password',d.pw); s('profile',d.perfil);
}

// ============================================================
// Auto-preencher Documento
// ============================================================
function mhsAutoFillDocumento(firstEqId) {
    var dados = [
        { tipo:'Manual',        nome:'Manual do utilizador',        obs:'Manual técnico de operação do equipamento fornecido pelo fabricante.', val:null },
        { tipo:'Certificado',   nome:'Certificado de calibração',   obs:'Certificado de calibração anual emitido por entidade acreditada.',     val:2    },
        { tipo:'Contrato',      nome:'Contrato de manutenção',      obs:'Contrato de manutenção preventiva anual com o fornecedor.',            val:1    },
        { tipo:'Ficha técnica', nome:'Ficha técnica do fabricante', obs:'Especificações técnicas e elétricas do equipamento.',                  val:null },
        { tipo:'Outro',         nome:'Declaração de conformidade',  obs:'Declaração CE de conformidade do equipamento.',                        val:3    }
    ];
    var d = dados[Math.floor(Math.random() * dados.length)];
    var hoje = new Date();
    var fmt = function(dt){ return dt.toISOString().slice(0,10); };
    var s = function(nm,v){ var el=document.querySelector('[name="'+nm+'"]'); if(el) el.value=v; };
    if (firstEqId) s('id_equipamento', firstEqId);
    s('tipo_documento', d.tipo);
    s('nome_documento', d.nome);
    s('data_documento', fmt(hoje));
    if (d.val) {
        var val = new Date(hoje); val.setFullYear(val.getFullYear() + d.val);
        s('data_validade', fmt(val));
    } else {
        s('data_validade', '');
    }
    s('observacoes', d.obs);
    ['data_documento','data_validade'].forEach(function(nm){
        var el=document.querySelector('[name="'+nm+'"]'); if(el&&el._flatpickr) el._flatpickr.setDate(el.value,false);
    });
}

// ============================================================
// Validador genérico para formulários planos (class="mhs-validate-form")
// ============================================================
function mhsValidateForm(form) {
    var ok = true;
    var firstBad = null;

    form.querySelectorAll('.mhs-field-error').forEach(function(el) { el.classList.remove('visible'); });
    form.querySelectorAll('.mhs-invalid').forEach(function(el) { el.classList.remove('mhs-invalid'); });

    function badField(field, errId, msg) {
        field.classList.add('mhs-invalid');
        var el = document.getElementById(errId || ('err_' + field.name));
        if (el) { if (msg) el.textContent = msg; el.classList.add('visible'); }
        if (!firstBad) firstBad = field;
        ok = false;
    }

    // Campos required vazios
    form.querySelectorAll('[required]').forEach(function(field) {
        var val = (field.tagName === 'SELECT') ? field.value : field.value.trim();
        if (!val) badField(field, 'err_' + field.name, 'Campo obrigatório.');
    });

    // Serviço: deve conter letras, mín 3 chars, não pode ser só números
    var srv = form.querySelector('[name="servico"]');
    if (srv && srv.value.trim()) {
        if (srv.value.trim().length < 3 || !/[a-zA-ZÀ-ú]/.test(srv.value)) {
            badField(srv, 'err_servico', 'Indique um serviço válido (ex: Urgência, Cardiologia).');
        }
    }

    // NIF: 9 dígitos exactos
    var nif = form.querySelector('[name="nif"]');
    if (nif && nif.value.trim() && !/^\d{9}$/.test(nif.value.trim())) {
        badField(nif, 'err_nif', 'O NIF deve ter exatamente 9 dígitos.');
    }

    // Email: formato válido
    var emailFld = form.querySelector('[name="email"]');
    if (emailFld && emailFld.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailFld.value.trim())) {
        badField(emailFld, 'err_email', 'Endereço de email inválido.');
    }

    // Telefones: mín 9 dígitos
    ['telefone', 'tel_contacto'].forEach(function(nm) {
        var tel = form.querySelector('[name="' + nm + '"]');
        if (tel && tel.value.trim() && tel.value.replace(/\D/g,'').length < 9) {
            badField(tel, 'err_' + nm, 'Número de telefone inválido (mín. 9 dígitos).');
        }
    });

    // Datas: data_fim deve ser >= data_inicio
    var di = form.querySelector('[name="data_inicio"]');
    var df = form.querySelector('[name="data_fim"]');
    if (di && df && di.value && df.value && df.value < di.value) {
        badField(df, 'err_data_fim', 'A data de fim deve ser posterior à data de início.');
    }

    if (!ok && firstBad) firstBad.focus();
    return ok;
}

(function () {
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form.mhs-validate-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!mhsValidateForm(form)) e.preventDefault();
            });
        });
    });
})();

// ============================================================
// Imprimir secção — detalhes de equipamento
// ============================================================
function mhsPrintSection(sectionId) {
    var content = document.getElementById(sectionId);
    if (!content) return;
    var title = document.querySelector('.mhs-page-title') ? document.querySelector('.mhs-page-title').textContent : 'Equipamento';
    var w = window.open('', '_blank', 'width=900,height=700');
    w.document.write('<html><head><title>' + title + '</title>');
    w.document.write('<link rel="stylesheet" href="/MedSolutions/private/assets/bootstrap/bootstrap.min.css">');
    w.document.write('<style>body{font-family:Inter,sans-serif;padding:2rem;font-size:13px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #e2e8f0;padding:8px 10px;text-align:left}th{background:#f8fafc;font-weight:600}dl{display:grid;grid-template-columns:160px 1fr;gap:4px 8px;margin:0}dt{font-weight:600;color:#64748b}dd{margin:0;color:#1e293b}.mhs-info-group{margin-bottom:1.5rem}.mhs-info-group-title{font-weight:700;margin-bottom:.5rem;padding-bottom:.25rem;border-bottom:2px solid #e2e8f0}h2{font-size:1.1rem;margin-bottom:1rem;color:#1e293b}</style>');
    w.document.write('</head><body>');
    w.document.write('<h2>' + title + ' — ' + new Date().toLocaleDateString('pt-PT') + '</h2>');
    w.document.write(content.innerHTML);
    w.document.write('<scr' + 'ipt>window.onload=function(){window.print();window.close()}<\/scr' + 'ipt>');
    w.document.write('</body></html>');
    w.document.close();
}

// ============================================================
// Calendário de manutenções preventivas
// ============================================================
(function () {
    var grid = document.getElementById('mhsCalGrid');
    if (!grid) return;

    var raw = grid.dataset.maints;
    if (!raw) return;
    var mhsMaintData;
    try { mhsMaintData = JSON.parse(raw); } catch (e) { return; }

    var MONTHS_PT   = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    var MONTHS_FULL = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    var calYear = new Date().getFullYear();

    function renderCal() {
        var label = document.getElementById('calYearLabel');
        if (label) label.textContent = calYear;
        var today = new Date(); today.setHours(0,0,0,0);

        var maintMap = {};
        mhsMaintData.forEach(function (m) {
            if (!m.date) return;
            var d = new Date(m.date);
            var key = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
            maintMap[key] = { estado: m.estado, day: d.getDate() };
        });
        mhsMaintData.forEach(function (m) {
            if (!m.proxima) return;
            var d = new Date(m.proxima);
            var key = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
            if (!maintMap[key]) maintMap[key] = { estado: 'Planeada', day: d.getDate() };
        });

        var html = '';
        for (var mo = 0; mo < 12; mo++) {
            var key  = calYear + '-' + String(mo+1).padStart(2,'0');
            var info = maintMap[key];
            var cls  = '', label2 = '—', sublabel = '';
            if (info) {
                label2 = info.day;
                if (info.estado === 'Concluída') {
                    cls = 'mhs-cal-month--done'; sublabel = 'concluída';
                } else {
                    var mDate = new Date(calYear, mo, info.day);
                    cls      = mDate < today ? 'mhs-cal-month--overdue' : 'mhs-cal-month--planned';
                    sublabel = mDate < today ? 'em atraso' : 'planeada';
                }
            }
            html += '<div class="mhs-cal-month ' + cls + '" title="' + MONTHS_FULL[mo] + ' ' + calYear + (info ? ' — ' + sublabel : '') + '">';
            html += '<div class="mhs-cal-month-name">' + MONTHS_PT[mo] + '</div>';
            html += '<div class="mhs-cal-month-day">' + label2;
            if (sublabel) html += '<small>' + sublabel + '</small>';
            html += '</div></div>';
        }
        grid.innerHTML = html;
    }

    var prevBtn = document.getElementById('calPrevYear');
    var nextBtn = document.getElementById('calNextYear');
    if (prevBtn) prevBtn.addEventListener('click', function () { calYear--; renderCal(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { calYear++; renderCal(); });

    var tabBtn = document.querySelector('.mhs-detail-tab[data-tab="manutencoes"]');
    if (tabBtn) tabBtn.addEventListener('click', function () { setTimeout(renderCal, 50); });

    if (new URLSearchParams(window.location.search).get('tab') === 'manutencoes') {
        setTimeout(renderCal, 100);
    }
    renderCal();
})();

// ============================================================
// Dashboard — gráficos (Chart.js), contadores e painéis
// Dados lidos de #mhsDashData[data-charts]; só corre na home.
// ============================================================
(function () {
    var dataEl = document.getElementById('mhsDashData');
    if (!dataEl || typeof Chart === 'undefined') return;

    var chartData = {};
    try { chartData = JSON.parse(dataEl.dataset.charts || '{}'); } catch (e) { chartData = {}; }

    var P = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4',
             '#ec4899','#84cc16','#f97316','#6366f1','#14b8a6','#a855f7'];
    var instances = {};

    function buildChart(canvasId, type) {
        var el = document.getElementById(canvasId);
        if (!el) return;
        var d = chartData[canvasId];
        if (!d) return;
        if (instances[canvasId]) instances[canvasId].destroy();

        var isHBar    = type === 'bar-h';
        var chartType = isHBar ? 'bar' : type;
        var circular  = ['pie','doughnut','polarArea'].indexOf(chartType) !== -1;
        var isLine    = chartType === 'line';

        var bg, border;
        if (circular) {
            bg = P.slice(0, d.values.length); border = '#fff';
        } else if (isLine) {
            var grad = el.getContext('2d').createLinearGradient(0, 0, 0, 280);
            grad.addColorStop(0, P[0] + '38'); grad.addColorStop(1, P[0] + '00');
            bg = grad; border = P[0];
        } else {
            bg = d.values.map(function (_, i) { return P[i % P.length] + 'D0'; });
            border = 'transparent';
        }

        var dataset = {
            label: 'Total', data: d.values,
            backgroundColor: bg, borderColor: border,
            borderWidth: circular ? 2 : isLine ? 2.5 : 0,
            borderRadius: circular || isLine ? 0 : 7,
            fill: isLine, tension: isLine ? 0.45 : 0,
            pointBackgroundColor: isLine ? P[0] : undefined,
            pointBorderColor: isLine ? '#fff' : undefined,
            pointBorderWidth: isLine ? 2.5 : undefined,
            pointRadius: isLine ? 5 : undefined,
            pointHoverRadius: isLine ? 7.5 : undefined
        };

        var tooltipDefs = {
            backgroundColor: '#0f172a', titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,.72)', padding: 13, cornerRadius: 11,
            titleFont: { weight: '700', size: 12.5, family: 'Inter' },
            bodyFont: { size: 12, family: 'Inter' },
            usePointStyle: true, boxWidth: 9, boxHeight: 9
        };
        var scaleBase = {
            grid: { color: 'rgba(148,163,184,.1)', drawBorder: false },
            border: { display: false },
            ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8', padding: 6 },
            beginAtZero: true
        };

        instances[canvasId] = new Chart(el, {
            type: chartType,
            data: { labels: d.labels, datasets: [dataset] },
            options: {
                indexAxis: isHBar ? 'y' : 'x',
                responsive: true, maintainAspectRatio: true,
                animation: { duration: 640, easing: 'easeOutQuart' },
                plugins: {
                    legend: {
                        display: circular, position: 'bottom',
                        labels: { padding: 18, font: { size: 11.5, weight: '600', family: 'Inter' },
                                  usePointStyle: true, pointStyleWidth: 9, color: '#334155' }
                    },
                    tooltip: tooltipDefs
                },
                scales: circular ? {} : {
                    x: Object.assign({}, scaleBase, { grid: Object.assign({}, scaleBase.grid, { display: isHBar }) }),
                    y: Object.assign({}, scaleBase, { grid: Object.assign({}, scaleBase.grid, { display: !isHBar }),
                        ticks: Object.assign({}, scaleBase.ticks, { stepSize: 1 }) })
                }
            }
        });

        var body = document.getElementById('body-' + canvasId);
        if (body) body.classList.remove('loading');
    }

    function animateCounter(el) {
        var target = parseInt(el.dataset.count, 10) || 0;
        var steps = Math.ceil(1100 / (1000 / 60)), frame = 0;
        var timer = setInterval(function () {
            frame++;
            var ease = 1 - Math.pow(1 - frame / steps, 4);
            el.textContent = Math.round(target * ease).toLocaleString('pt-PT');
            if (frame >= steps) { el.textContent = target.toLocaleString('pt-PT'); clearInterval(timer); }
        }, 1000 / 60);
    }
    document.querySelectorAll('.dash-stat-num').forEach(animateCounter);

    document.querySelectorAll('.dash-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var group = this.closest('.dash-toggle');
            group.querySelectorAll('.dash-toggle-btn').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            buildChart(this.dataset.canvas, this.dataset.type);
        });
    });

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); observer.unobserve(e.target); } });
    }, { threshold: 0.06 });
    document.querySelectorAll('.dash-panel').forEach(function (p) { observer.observe(p); });

    var panelChartMap = {
        'body-cEstados': ['cEstados', 'pie'],
        'body-cCategorias': ['cCategorias', 'bar-h'],
        'body-cLocalizacoes': ['cLocalizacoes', 'doughnut'],
        'body-cDocumentos': ['cDocumentos', 'doughnut'],
        'body-cFornecedores': ['cFornecedores', 'bar-h'],
        'body-cGarantias': ['cGarantias', 'line']
    };

    var renderObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting && e.target.classList.contains('loading')) {
                if (panelChartMap[e.target.id]) buildChart.apply(null, panelChartMap[e.target.id]);
                renderObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.05 });
    Object.keys(panelChartMap).forEach(function (id) {
        var el = document.getElementById(id); if (el) renderObserver.observe(el);
    });

    requestAnimationFrame(function () {
        document.querySelectorAll('.dash-panel').forEach(function (p) {
            if (p.getBoundingClientRect().top < window.innerHeight + 100) p.classList.add('in');
        });
        Object.keys(panelChartMap).forEach(function (id) {
            var el = document.getElementById(id);
            if (el && el.getBoundingClientRect().top < window.innerHeight + 100) buildChart.apply(null, panelChartMap[id]);
        });
    });
})();

// ============================================================
// Inicialização dos Bootstrap Tooltips
// Ativa as tooltips em todos os elementos com data-bs-toggle="tooltip"
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});

// ============================================================
// Ligação de eventos sem handlers inline (sidebar, menus, confirmações)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var sbToggle = document.querySelector('[data-toggle-sidebar]');
    if (sbToggle) sbToggle.addEventListener('click', function () { document.body.classList.toggle('mhs-sidebar-open'); });

    var sbOverlay = document.querySelector('.mhs-sidebar-overlay');
    if (sbOverlay) sbOverlay.addEventListener('click', function () { document.body.classList.remove('mhs-sidebar-open'); });

    var notifBtn = document.querySelector('[data-toggle-notif]');
    if (notifBtn) notifBtn.addEventListener('click', toggleNotifMenu);

    // toggleAvatarMenu já está ligado via onclick no HTML — não duplicar
});

// Confirmação de formulários via atributo data-confirm
document.addEventListener('submit', function (e) {
    var f = e.target;
    if (f && f.getAttribute && f.getAttribute('data-confirm') && !window.confirm(f.getAttribute('data-confirm'))) {
        e.preventDefault();
    }
}, true);

// ============================================================
// Ligação perdida — interceta navegação e mostra modal
// ============================================================
(function () {
    'use strict';
    var _dest = null;

    function modalEl() { return document.getElementById('mhsConnModal'); }
    function show(dest) { _dest = dest; var m = modalEl(); if (m) m.classList.add('open'); }
    function hide() { var m = modalEl(); if (m) m.classList.remove('open'); _dest = null; }

    function check(url, onOk) {
        fetch(url, { method: 'HEAD', cache: 'no-store' })
            .then(function () { onOk(); })
            .catch(function () { show(_dest !== null ? _dest : url); });
    }

    window.mhsConnBack = function () { hide(); };

    window.mhsConnRetry = function () {
        var saved = _dest;
        hide();
        if (!saved) { window.location.reload(); return; }
        if (typeof saved === 'string') {
            check(saved, function () { window.location.href = saved; });
        } else {
            check(saved.action || window.location.href, function () { saved.submit(); });
        }
    };

    // Interceta cliques em links internos
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[href]');
        if (!a) return;
        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || a.target === '_blank') return;
        if (/^(javascript|mailto|tel):/.test(href) || /^https?:\/\//.test(href)) return;
        e.preventDefault();
        var full = a.href;
        _dest = full;
        check(full, function () { window.location.href = full; });
    }, true);

    // Interceta submissão de formulários internos
    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        var form = e.target;
        var action = form.getAttribute('action') || '';
        if (/^https?:\/\//.test(action)) return;
        e.preventDefault();
        _dest = form;
        check(form.action || window.location.href, function () { form.submit(); });
    }, true);
})();
