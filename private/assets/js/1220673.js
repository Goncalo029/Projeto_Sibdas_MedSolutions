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
    flatpickr(el, {
        dateFormat: 'Y-m-d',
        allowInput: true,
        locale: {
            firstDayOfWeek: 1
        }
    });
});

// ============================================================
// Validacao client-side - Formulario Equipamento
// ============================================================
function validarFormEquipamento() {
    const obrigatorios = [
        { id: 'codigo_inventario', label: 'Codigo de inventario' },
        { id: 'designacao', label: 'Designacao' },
        { id: 'id_categoria', label: 'Categoria' },
        { id: 'id_localizacao', label: 'Localizacao' },
        { id: 'estado', label: 'Estado' },
        { id: 'criticidade', label: 'Criticidade' }
    ];

    for (const campo of obrigatorios) {
        const el = document.getElementById(campo.id);
        if (!el || !el.value.trim()) {
            alert('O campo "' + campo.label + '" e obrigatorio.');
            if (el) el.focus();
            return false;
        }
    }

    return true;
}

// ============================================================
// Validacao client-side - Formulario Fornecedor
// ============================================================
function validarFormFornecedor() {
    const nome = document.getElementById('nome');
    if (!nome || !nome.value.trim()) {
        alert('O campo "Nome" e obrigatorio.');
        if (nome) nome.focus();
        return false;
    }
    return true;
}

// ============================================================
// Validacao client-side - Formulario Localizacao
// ============================================================
function validarFormLocalizacao() {
    const servico = document.getElementById('servico');
    if (!servico || !servico.value.trim()) {
        alert('O campo "Servico" e obrigatorio.');
        if (servico) servico.focus();
        return false;
    }
    return true;
}

function validarFormDocumento() {
    const campos = [
        { id: 'id_equipamento', label: 'Equipamento' },
        { id: 'tipo_documento', label: 'Tipo de documento' }
    ];

    for (const campo of campos) {
        const el = document.getElementById(campo.id);
        if (!el || !el.value.trim()) {
            alert('O campo "' + campo.label + '" e obrigatorio.');
            if (el) el.focus();
            return false;
        }
    }

    return true;
}

// ============================================================
// Validacao client-side - Formulario Garantia/Contrato
// ============================================================
function validarFormGarantia() {
    const campos = [
        { id: 'id_equipamento', label: 'Equipamento' },
        { id: 'data_fim', label: 'Data de fim' }
    ];

    for (const campo of campos) {
        const el = document.getElementById(campo.id);
        if (!el || !el.value.trim()) {
            alert('O campo "' + campo.label + '" e obrigatorio.');
            if (el) el.focus();
            return false;
        }
    }

    return true;
}

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
// Tab switcher — global para todas as páginas com .mhs-detail-tab
// ============================================================
(function () {
    function mhsActivateTab(tabName) {
        var btn  = document.querySelector('.mhs-detail-tab[data-tab="' + tabName + '"]');
        var pane = document.getElementById('tab-' + tabName);
        if (!btn || !pane) return;
        document.querySelectorAll('.mhs-detail-tab').forEach(function (b) { b.classList.remove('active'); });
        document.querySelectorAll('.mhs-tab-pane').forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        pane.classList.add('active');
    }

    document.querySelectorAll('.mhs-detail-tab').forEach(function (btn) {
        btn.addEventListener('click', function () { mhsActivateTab(btn.dataset.tab); });
    });

    // Activar tab via URL param (ex: ?tab=assistencia)
    var tab = new URLSearchParams(window.location.search).get('tab');
    if (tab) mhsActivateTab(tab);
})();

// ============================================================
// Exportar PDF — lista de equipamentos
// ============================================================
function mhsExportarPDF() {
    var table = document.getElementById('equipamentosTable');
    var total = table ? (table.dataset.total || '?') : '?';
    var rows  = '';
    document.querySelectorAll('#equipamentosTable tbody tr').forEach(function (tr) {
        var tds = tr.querySelectorAll('td');
        if (tds.length < 8) return;
        rows += '<tr>';
        for (var i = 0; i < 7; i++) rows += '<td>' + tds[i].textContent.trim() + '</td>';
        rows += '</tr>';
    });
    var w = window.open('', '_blank', 'width=1100,height=800');
    w.document.write('<html><head><title>Equipamentos</title>');
    w.document.write('<style>*{font-family:Arial,sans-serif;font-size:11px}body{padding:20px}h2{font-size:14px;margin-bottom:12px}table{width:100%;border-collapse:collapse}th{background:#0d6ea8;color:#fff;padding:7px 8px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.5px}td{padding:6px 8px;border-bottom:1px solid #e2e8f0}tr:nth-child(even) td{background:#f8fafc}.foot{margin-top:10px;font-size:10px;color:#94a3b8}</style>');
    w.document.write('</head><body>');
    w.document.write('<h2>Lista de Equipamentos &mdash; ' + new Date().toLocaleDateString('pt-PT') + '</h2>');
    w.document.write('<table><thead><tr><th>Código</th><th>Designação</th><th>Marca</th><th>Categoria</th><th>Serviço</th><th>Estado</th><th>Criticidade</th></tr></thead><tbody>' + rows + '</tbody></table>');
    w.document.write('<p class="foot">Total: ' + total + ' equipamentos</p>');
    w.document.write('<scr' + 'ipt>window.onload=function(){window.print();window.close()}<\/scr' + 'ipt>');
    w.document.write('</body></html>');
    w.document.close();
}

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
