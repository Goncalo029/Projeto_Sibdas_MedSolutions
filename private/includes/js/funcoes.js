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
    dropdown.classList.toggle('open', !isOpen);
    menu.classList.toggle('visible', !isOpen);
}

document.addEventListener('click', function () {
    var dropdown = document.getElementById('avatarDropdown');
    var menu = document.getElementById('avatarMenu');
    if (dropdown) dropdown.classList.remove('open');
    if (menu) menu.classList.remove('visible');
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
