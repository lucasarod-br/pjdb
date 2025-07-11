// Configuração da API
const API_BASE = 'api/';

// Estado da aplicação
let currentTab = 'eventos';
let editingId = null;
let editingType = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function () {
    setupEventListeners();
    loadInitialData();
});

// Event Listeners
function setupEventListeners() {
    // Formulários
    document.getElementById('formEvento').addEventListener('submit', handleEventoSubmit);
    document.getElementById('formAtividade').addEventListener('submit', handleAtividadeSubmit);
    document.getElementById('formInscricao').addEventListener('submit', handleInscricaoSubmit);

    // Modal
    document.querySelector('.close').addEventListener('click', closeModal);
    window.addEventListener('click', function (e) {
        if (e.target === document.getElementById('confirmModal')) {
            closeModal();
        }
    });
}

// Carregamento inicial
async function loadInitialData() {
    try {
        await Promise.all([
            loadEventos(),
            loadAtividades(),
            loadInscricoes(),
            loadSelects()
        ]);
        updateStats();
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
    }
}

// Navegação entre tabs
function showTab(tabName) {
    // Esconder todas as tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Desativar todos os botões
    document.querySelectorAll('.nav-tab').forEach(btn => {
        btn.classList.remove('active');
    });

    // Ativar a tab selecionada
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');

    currentTab = tabName;

    // Limpar formulários
    limparFormEvento();
    limparFormAtividade();
    limparFormInscricao();
}

// ======================= EVENTOS =======================
// Adicione estas funções ao seu app.js

// Preview da imagem selecionada
document.getElementById('eventoFoto').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('fotoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Remover foto
function removerFoto() {
    document.getElementById('eventoFoto').value = '';
    document.getElementById('fotoPreview').style.display = 'none';
    document.getElementById('previewImage').src = '';
}

// Atualizar a função handleEventoSubmit
async function handleEventoSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);

    try {
        const url = editingId ? `${API_BASE}eventos.php?id=${editingId}` : `${API_BASE}eventos.php`;
        const method = editingId ? 'PUT' : 'POST';

        // Para PUT, precisamos converter FormData para JSON, exceto para arquivo
        if (method === 'PUT') {
            const data = {};
            for (let [key, value] of formData.entries()) {
                if (key !== 'foto') {
                    data[key] = value;
                }
            }

            // Se há uma nova foto, enviamos separadamente
            const fotoFile = formData.get('foto');
            if (fotoFile && fotoFile.size > 0) {
                data.foto = await fileToBase64(fotoFile);
                data.foto_type = fotoFile.type;
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showAlert('alertEventos', result.message, 'success');
                limparFormEvento();
                loadEventos();
                updateStats();
            } else {
                showAlert('alertEventos', result.message, 'error');
            }
        } else {
            // POST com FormData (suporta arquivo)
            const response = await fetch(url, {
                method: method,
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAlert('alertEventos', result.message, 'success');
                limparFormEvento();
                loadEventos();
                updateStats();
            } else {
                showAlert('alertEventos', result.message, 'error');
            }
        }
    } catch (error) {
        console.error('Erro ao salvar evento:', error);
        showAlert('alertEventos', 'Erro ao salvar evento', 'error');
    }
}

// Função para converter arquivo para base64
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
    });
}

// Atualizar a função editarEvento
async function editarEvento(id) {
    try {
        const response = await fetch(`${API_BASE}eventos.php?id=${id}`);
        const evento = await response.json();

        if (evento.success) {
            const data = evento.data;

            document.getElementById('eventoId').value = data.id_evento;
            document.getElementById('eventoNome').value = data.nome;
            document.getElementById('eventoDescricao').value = data.descricao || '';
            document.getElementById('eventoDataInicio').value = data.data_inicio;
            document.getElementById('eventoDataFim').value = data.data_fim;
            document.getElementById('eventoCategoria').value = data.id_categoria;
            document.getElementById('eventoLocal').value = data.id_local;

            // Mostrar foto atual se existe
            if (data.foto) {
                const fotoAtual = document.getElementById('fotoAtual');
                if (fotoAtual) {
                    fotoAtual.innerHTML = `
                        <div class="foto-atual">
                            <p>Foto atual:</p>
                            <img src="data:image/jpeg;base64,${data.foto}" alt="Foto atual">
                        </div>
                    `;
                }
            }

            editingId = id;
            editingType = 'evento';

            document.querySelector('#formEvento button[type="submit"]').textContent = 'Atualizar Evento';
        } else {
            showAlert('alertEventos', evento.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao carregar evento:', error);
        showAlert('alertEventos', 'Erro ao carregar evento', 'error');
    }
}

// Deletar evento
function deletarEvento(id) {
    showConfirmModal('Tem certeza que deseja excluir este evento?', async () => {
        try {
            const response = await fetch(`${API_BASE}eventos.php?id=${id}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAlert('alertEventos', result.message, 'success');
                loadEventos();
                updateStats();
            } else {
                showAlert('alertEventos', result.message, 'error');
            }
        } catch (error) {
            console.error('Erro ao deletar evento:', error);
            showAlert('alertEventos', 'Erro ao deletar evento', 'error');
        }
    });
}
// Limpar formulário
function limparFormEvento() {
    document.getElementById('formEvento').reset();
    document.getElementById('eventoId').value = '';
    document.getElementById('fotoPreview').style.display = 'none';
    document.getElementById('previewImage').src = '';

    // Remover foto atual se existir
    const fotoAtual = document.getElementById('fotoAtual');
    if (fotoAtual) {
        fotoAtual.innerHTML = '';
    }

    document.querySelector('#formEvento button[type="submit"]').textContent = 'Salvar Evento';
    editingId = null;
    editingType = null;
}

// Carregar eventos
async function loadEventos() {
    try {
        const response = await fetch(`${API_BASE}eventos.php`);
        const eventos = await response.json();

        const tbody = document.querySelector('#tabelaEventos tbody');
        tbody.innerHTML = '';

        if (eventos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="loading">Nenhum evento encontrado</td></tr>';
            return;
        }

        eventos.forEach(evento => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${evento.id_evento}</td>
                <td>${evento.nome}</td>
                <td>${evento.categoria_nome || 'N/A'}</td>
                <td>${formatDate(evento.data_inicio)}</td>
                <td>${formatDate(evento.data_fim)}</td>
                <td>${evento.local_nome || 'N/A'}</td>
                <td>${evento.foto ? '📷' : '❌'}</td>
                <td class="actions">
                    <button class="btn btn-sm" onclick="editarEvento(${evento.id_evento})">Editar</button>
                    <button class="btn btn-sm btn-danger" onclick="deletarEvento(${evento.id_evento})">Excluir</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
        showAlert('alertEventos', 'Erro ao carregar eventos', 'error');
    }
}
// ======================= ATIVIDADES =======================

// Carregar atividades
async function loadAtividades() {
    try {
        const response = await fetch(`${API_BASE}atividades.php`);
        const atividades = await response.json();

        const tbody = document.querySelector('#tabelaAtividades tbody');
        tbody.innerHTML = '';

        if (atividades.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="loading">Nenhuma atividade encontrada</td></tr>';
            return;
        }

        atividades.forEach(atividade => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${atividade.id_atividade}</td>
                <td>${atividade.titulo}</td>
                <td>${atividade.tipo}</td>
                <td>${atividade.evento_nome || 'N/A'}</td>
                <td>${formatDate(atividade.data_inicio)}</td>
                <td class="actions">
                    <button class="btn btn-sm" onclick="editarAtividade(${atividade.id_atividade})">Editar</button>
                    <button class="btn btn-sm btn-danger" onclick="deletarAtividade(${atividade.id_atividade})">Excluir</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Erro ao carregar atividades:', error);
        showAlert('alertAtividades', 'Erro ao carregar atividades', 'error');
    }
}

// Submissão do formulário de atividade
async function handleAtividadeSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const url = editingId ? `${API_BASE}atividades.php?id=${editingId}` : `${API_BASE}atividades.php`;
        const method = editingId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showAlert('alertAtividades', result.message, 'success');
            limparFormAtividade();
            loadAtividades();
            updateStats();
        } else {
            showAlert('alertAtividades', result.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao salvar atividade:', error);
        showAlert('alertAtividades', 'Erro ao salvar atividade', 'error');
    }
}

// Editar atividade
async function editarAtividade(id) {
    try {
        const response = await fetch(`${API_BASE}atividades.php?id=${id}`);
        const atividade = await response.json();

        if (atividade.success) {
            const data = atividade.data;

            document.getElementById('atividadeId').value = data.id_atividade;
            document.getElementById('atividadeTitulo').value = data.titulo;
            document.getElementById('atividadeTipo').value = data.tipo;
            document.getElementById('atividadeEvento').value = data.id_evento;

            editingId = id;
            editingType = 'atividade';

            document.querySelector('#formAtividade button[type="submit"]').textContent = 'Atualizar Atividade';
        } else {
            showAlert('alertAtividades', atividade.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao carregar atividade:', error);
        showAlert('alertAtividades', 'Erro ao carregar atividade', 'error');
    }
}

// Deletar atividade
function deletarAtividade(id) {
    showConfirmModal('Tem certeza que deseja excluir esta atividade?', async () => {
        try {
            const response = await fetch(`${API_BASE}atividades.php?id=${id}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                showAlert('alertAtividades', result.message, 'success');
                loadAtividades();
                updateStats();
            } else {
                showAlert('alertAtividades', result.message, 'error');
            }
        } catch (error) {
            console.error('Erro ao deletar atividade:', error);
            showAlert('alertAtividades', 'Erro ao deletar atividade', 'error');
        }
    });
}

// Limpar formulário de atividade
function limparFormAtividade() {
    document.getElementById('formAtividade').reset();
    document.getElementById('atividadeId').value = '';
    document.querySelector('#formAtividade button[type="submit"]').textContent = 'Salvar Atividade';
    editingId = null;
    editingType = null;
}

// ======================= INSCRIÇÕES =======================

// Carregar inscrições
async function loadInscricoes() {
    try {
        const response = await fetch(`${API_BASE}inscricoes.php`);
        const inscricoes = await response.json();

        const tbody = document.querySelector('#tabelaInscricoes tbody');
        tbody.innerHTML = '';

        if (inscricoes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="loading">Nenhuma inscrição encontrada</td></tr>';
            return;
        }

        inscricoes.forEach(inscricao => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${inscricao.participante_nome}</td>
                <td>${inscricao.email}</td>
                <td>${inscricao.evento_nome}</td>
                <td><span class="status-${inscricao.status.toLowerCase()}">${inscricao.status}</span></td>
                <td>${formatDate(inscricao.data_inscricao)}</td>
                <td class="actions">
                    <button class="btn btn-sm" onclick="editarInscricao(${inscricao.id_participante}, ${inscricao.id_evento})">Editar</button>
                    <button class="btn btn-sm btn-danger" onclick="deletarInscricao(${inscricao.id_participante}, ${inscricao.id_evento})">Excluir</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Erro ao carregar inscrições:', error);
        showAlert('alertInscricoes', 'Erro ao carregar inscrições', 'error');
    }
}

// Submissão do formulário de inscrição
async function handleInscricaoSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const isEditing = editingId !== null;
        let url, method;

        if (isEditing) {
            const originalParticipante = data.id_participante_original;
            const originalEvento = data.id_evento_original;
            url = `${API_BASE}inscricoes.php?id_participante=${originalParticipante}&id_evento=${originalEvento}`;
            method = 'PUT';
        } else {
            url = `${API_BASE}inscricoes.php`;
            method = 'POST';
        }

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showAlert('alertInscricoes', result.message, 'success');
            limparFormInscricao();
            loadInscricoes();
            updateStats();
        } else {
            showAlert('alertInscricoes', result.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao salvar inscrição:', error);
        showAlert('alertInscricoes', 'Erro ao salvar inscrição', 'error');
    }
}

// Editar inscrição
async function editarInscricao(id_participante, id_evento) {
    try {
        const response = await fetch(`${API_BASE}inscricoes.php?id_participante=${id_participante}&id_evento=${id_evento}`);
        const inscricao = await response.json();

        if (inscricao.success) {
            const data = inscricao.data;

            document.getElementById('inscricaoParticipanteId').value = data.id_participante;
            document.getElementById('inscricaoEventoId').value = data.id_evento;
            document.getElementById('inscricaoParticipante').value = data.id_participante;
            document.getElementById('inscricaoEvento').value = data.id_evento;
            document.getElementById('inscricaoStatus').value = data.status;
            document.getElementById('inscricaoData').value = data.data_inscricao;

            editingId = `${id_participante}-${id_evento}`;
            editingType = 'inscricao';

            document.querySelector('#formInscricao button[type="submit"]').textContent = 'Atualizar Inscrição';
        } else {
            showAlert('alertInscricoes', inscricao.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao carregar inscrição:', error);
        showAlert('alertInscricoes', 'Erro ao carregar inscrição', 'error');
    }
}

// Deletar inscrição
function deletarInscricao(id_participante, id_evento) {
    showConfirmModal('Tem certeza que deseja excluir esta inscrição?', async () => {
        try {
            const response = await fetch(`${API_BASE}inscricoes.php?id_participante=${id_participante}&id_evento=${id_evento}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                showAlert('alertInscricoes', result.message, 'success');
                loadInscricoes();
                updateStats();
            } else {
                showAlert('alertInscricoes', result.message, 'error');
            }
        } catch (error) {
            console.error('Erro ao deletar inscrição:', error);
            showAlert('alertInscricoes', 'Erro ao deletar inscrição', 'error');
        }
    });
}

// Limpar formulário de inscrição
function limparFormInscricao() {
    document.getElementById('formInscricao').reset();
    document.getElementById('inscricaoParticipanteId').value = '';
    document.getElementById('inscricaoEventoId').value = '';
    document.getElementById('inscricaoData').value = new Date().toISOString().split('T')[0];
    document.querySelector('#formInscricao button[type="submit"]').textContent = 'Salvar Inscrição';
    editingId = null;
    editingType = null;
}

// ======================= UTILITÁRIOS =======================

// Carregar selects
async function loadSelects() {
    try {
        // Carregar categorias
        const categorias = await fetch(`${API_BASE}categorias.php`);
        const categoriasData = await categorias.json();

        const selectCategoria = document.getElementById('eventoCategoria');
        selectCategoria.innerHTML = '<option value="">Selecione uma categoria</option>';
        categoriasData.forEach(categoria => {
            selectCategoria.innerHTML += `<option value="${categoria.id_categoria}">${categoria.nome}</option>`;
        });

        // Carregar locais
        const locais = await fetch(`${API_BASE}locais.php`);
        const locaisData = await locais.json();

        const selectLocal = document.getElementById('eventoLocal');
        selectLocal.innerHTML = '<option value="">Selecione um local</option>';
        locaisData.forEach(local => {
            selectLocal.innerHTML += `<option value="${local.id_local}">${local.nome} - ${local.campus}</option>`;
        });

        // Carregar eventos para atividades
        const eventos = await fetch(`${API_BASE}eventos.php`);
        const eventosData = await eventos.json();

        const selectEventoAtividade = document.getElementById('atividadeEvento');
        selectEventoAtividade.innerHTML = '<option value="">Selecione um evento</option>';
        eventosData.forEach(evento => {
            selectEventoAtividade.innerHTML += `<option value="${evento.id_evento}">${evento.nome}</option>`;
        });

        const selectEventoInscricao = document.getElementById('inscricaoEvento');
        selectEventoInscricao.innerHTML = '<option value="">Selecione um evento</option>';
        eventosData.forEach(evento => {
            selectEventoInscricao.innerHTML += `<option value="${evento.id_evento}">${evento.nome}</option>`;
        });

        // Carregar tipos de atividade
        const tiposAtividade = ['Palestra', 'Workshop', 'Mesa Redonda', 'Minicurso', 'Apresentação', 'Competição', 'Networking'];
        const selectTipoAtividade = document.getElementById('atividadeTipo');
        selectTipoAtividade.innerHTML = '<option value="">Selecione um tipo</option>';
        tiposAtividade.forEach(tipo => {
            selectTipoAtividade.innerHTML += `<option value="${tipo}">${tipo}</option>`;
        });

        // Carregar participantes
        const participantes = await fetch(`${API_BASE}participantes.php`);
        const participantesData = await participantes.json();

        const selectParticipante = document.getElementById('inscricaoParticipante');
        selectParticipante.innerHTML = '<option value="">Selecione um participante</option>';
        participantesData.forEach(participante => {
            selectParticipante.innerHTML += `<option value="${participante.id_participante}">${participante.nome} - ${participante.email}</option>`;
        });

        // Carregar status de inscrição
        const statusInscricao = ['Confirmada', 'Pendente', 'Cancelada', 'Presente', 'Ausente'];
        const selectStatus = document.getElementById('inscricaoStatus');
        selectStatus.innerHTML = '<option value="">Selecione um status</option>';
        statusInscricao.forEach(status => {
            selectStatus.innerHTML += `<option value="${status}">${status}</option>`;
        });

        // Definir data atual para inscrição
        document.getElementById('inscricaoData').value = new Date().toISOString().split('T')[0];

    } catch (error) {
        console.error('Erro ao carregar selects:', error);
    }
}

// Atualizar estatísticas
async function updateStats() {
    try {
        const [eventos, atividades, inscricoes] = await Promise.all([
            fetch(`${API_BASE}eventos.php`).then(r => r.json()),
            fetch(`${API_BASE}atividades.php`).then(r => r.json()),
            fetch(`${API_BASE}inscricoes.php`).then(r => r.json())
        ]);

        document.getElementById('totalEventos').textContent = eventos.length;
        document.getElementById('totalAtividades').textContent = atividades.length;
        document.getElementById('totalInscricoes').textContent = inscricoes.length;
    } catch (error) {
        console.error('Erro ao atualizar estatísticas:', error);
    }
}

// Mostrar alerta
function showAlert(elementId, message, type) {
    const alert = document.getElementById(elementId);
    alert.textContent = message;
    alert.className = `alert ${type}`;
    alert.style.display = 'block';

    setTimeout(() => {
        alert.style.display = 'none';
    }, 5000);
}

// Formatar data
function formatDate(dateString) {
    if (!dateString) return 'N/A';

    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
}

// Modal de confirmação
function showConfirmModal(message, onConfirm) {
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmModal').style.display = 'block';

    document.getElementById('confirmBtn').onclick = function () {
        onConfirm();
        closeModal();
    };
}

function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
