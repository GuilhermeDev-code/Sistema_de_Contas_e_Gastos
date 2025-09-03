// Configuração da API
const API_BASE = '/api';

// Estado da aplicação
let clientes = [];
let servicos = [];
let clienteEditando = null;
let servicoEditando = null;

// Inicialização da aplicação
document.addEventListener('DOMContentLoaded', function() {
    inicializarApp();
});

async function inicializarApp() {
    configurarEventListeners();
    await carregarDados();
    atualizarDashboard();
    mostrarSecao('dashboard');
}

function configurarEventListeners() {
    // Navegação
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const secao = this.dataset.section;
            mostrarSecao(secao);
            
            // Atualizar botão ativo
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Formulários
    document.getElementById('form-cliente').addEventListener('submit', salvarCliente);
    document.getElementById('form-servico').addEventListener('submit', salvarServico);

    // Busca de clientes
    document.getElementById('search-clientes').addEventListener('input', function() {
        const termo = this.value.toLowerCase();
        filtrarClientes(termo);
    });

    // Filtros de serviços
    document.getElementById('filter-cliente').addEventListener('change', filtrarServicos);
    document.getElementById('filter-status').addEventListener('change', filtrarServicos);

    // Fechar modals ao clicar fora
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModals();
            }
        });
    });
}

function mostrarSecao(secao) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById(secao).classList.add('active');
    
    if (secao === 'clientes') {
        renderizarClientes();
    } else if (secao === 'servicos') {
        renderizarServicos();
        carregarClientesSelect();
    } else if (secao === 'dashboard') {
        atualizarDashboard();
    }
}

// Funções de API
async function carregarDados() {
    mostrarLoading(true);
    try {
        const [clientesResponse, servicosResponse] = await Promise.all([
            fetch(`${API_BASE}/clientes.php`),
            fetch(`${API_BASE}/servicos.php`)
        ]);
        
        clientes = await clientesResponse.json();
        servicos = await servicosResponse.json();
    } catch (error) {
        mostrarToast('Erro ao carregar dados', 'error');
        console.error('Erro:', error);
    }
    mostrarLoading(false);
}

// Gerenciamento de Clientes
async function salvarCliente(e) {
    e.preventDefault();
    
    const dados = {
        nome: document.getElementById('cliente-nome').value,
        telefone: document.getElementById('cliente-telefone').value,
        email: document.getElementById('cliente-email').value,
        endereco: document.getElementById('cliente-endereco').value
    };

    const id = document.getElementById('cliente-id').value;
    const isEdicao = id !== '';

    mostrarLoading(true);
    try {
        let response;
        if (isEdicao) {
            dados.id = id;
            response = await fetch(`${API_BASE}/clientes.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
        } else {
            response = await fetch(`${API_BASE}/clientes.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
        }

        const resultado = await response.json();
        
        if (response.ok) {
            mostrarToast(resultado.message, 'success');
            fecharModalCliente();
            await carregarDados();
            renderizarClientes();
            atualizarDashboard();
        } else {
            mostrarToast(resultado.message, 'error');
        }
    } catch (error) {
        mostrarToast('Erro ao salvar cliente', 'error');
        console.error('Erro:', error);
    }
    mostrarLoading(false);
}

async function excluirCliente(id) {
    if (!confirm('Tem certeza que deseja excluir este cliente?')) {
        return;
    }

    mostrarLoading(true);
    try {
        const response = await fetch(`${API_BASE}/clientes.php?id=${id}`, {
            method: 'DELETE'
        });

        const resultado = await response.json();
        
        if (response.ok) {
            mostrarToast(resultado.message, 'success');
            await carregarDados();
            renderizarClientes();
            atualizarDashboard();
        } else {
            mostrarToast(resultado.message, 'error');
        }
    } catch (error) {
        mostrarToast('Erro ao excluir cliente', 'error');
        console.error('Erro:', error);
    }
    mostrarLoading(false);
}

function editarCliente(id) {
    const cliente = clientes.find(c => c.id == id);
    if (!cliente) return;

    document.getElementById('cliente-id').value = cliente.id;
    document.getElementById('cliente-nome').value = cliente.nome;
    document.getElementById('cliente-telefone').value = cliente.telefone || '';
    document.getElementById('cliente-email').value = cliente.email || '';
    document.getElementById('cliente-endereco').value = cliente.endereco || '';
    
    document.getElementById('modal-cliente-title').textContent = 'Editar Cliente';
    abrirModalCliente();
}

function renderizarClientes() {
    const tbody = document.querySelector('#clientes-table tbody');
    tbody.innerHTML = '';

    clientes.forEach(cliente => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${cliente.nome}</td>
            <td>${cliente.telefone || '-'}</td>
            <td>${cliente.email || '-'}</td>
            <td>${cliente.endereco || '-'}</td>
            <td>
                <button class="btn btn-edit" onclick="editarCliente(${cliente.id})">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn btn-danger" onclick="excluirCliente(${cliente.id})">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filtrarClientes(termo) {
    const linhas = document.querySelectorAll('#clientes-table tbody tr');
    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}

// Gerenciamento de Serviços
async function salvarServico(e) {
    e.preventDefault();
    
    const dados = {
        cliente_id: document.getElementById('servico-cliente').value,
        descricao: document.getElementById('servico-descricao').value,
        valor: parseFloat(document.getElementById('servico-valor').value),
        data_servico: document.getElementById('servico-data').value,
        status: document.getElementById('servico-status').value,
        observacoes: document.getElementById('servico-observacoes').value
    };

    const id = document.getElementById('servico-id').value;
    const isEdicao = id !== '';

    mostrarLoading(true);
    try {
        let response;
        if (isEdicao) {
            dados.id = id;
            response = await fetch(`${API_BASE}/servicos.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
        } else {
            response = await fetch(`${API_BASE}/servicos.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
        }

        const resultado = await response.json();
        
        if (response.ok) {
            mostrarToast(resultado.message, 'success');
            fecharModalServico();
            await carregarDados();
            renderizarServicos();
            atualizarDashboard();
        } else {
            mostrarToast(resultado.message, 'error');
        }
    } catch (error) {
        mostrarToast('Erro ao salvar serviço', 'error');
        console.error('Erro:', error);
    }
    mostrarLoading(false);
}

async function excluirServico(id) {
    if (!confirm('Tem certeza que deseja excluir este serviço?')) {
        return;
    }

    mostrarLoading(true);
    try {
        const response = await fetch(`${API_BASE}/servicos.php?id=${id}`, {
            method: 'DELETE'
        });

        const resultado = await response.json();
        
        if (response.ok) {
            mostrarToast(resultado.message, 'success');
            await carregarDados();
            renderizarServicos();
            atualizarDashboard();
        } else {
            mostrarToast(resultado.message, 'error');
        }
    } catch (error) {
        mostrarToast('Erro ao excluir serviço', 'error');
        console.error('Erro:', error);
    }
    mostrarLoading(false);
}

function editarServico(id) {
    const servico = servicos.find(s => s.id == id);
    if (!servico) return;

    document.getElementById('servico-id').value = servico.id;
    document.getElementById('servico-cliente').value = servico.cliente_id;
    document.getElementById('servico-descricao').value = servico.descricao;
    document.getElementById('servico-valor').value = servico.valor;
    document.getElementById('servico-data').value = servico.data_servico;
    document.getElementById('servico-status').value = servico.status;
    document.getElementById('servico-observacoes').value = servico.observacoes || '';
    
    document.getElementById('modal-servico-title').textContent = 'Editar Serviço';
    abrirModalServico();
}

function renderizarServicos() {
    const tbody = document.querySelector('#servicos-table tbody');
    tbody.innerHTML = '';

    servicos.forEach(servico => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${servico.cliente_nome}</td>
            <td>${servico.descricao}</td>
            <td>R$ ${parseFloat(servico.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            <td>${formatarData(servico.data_servico)}</td>
            <td><span class="status-badge status-${servico.status.toLowerCase().replace(' ', '-')}">${servico.status}</span></td>
            <td>
                <button class="btn btn-edit" onclick="editarServico(${servico.id})">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn btn-danger" onclick="excluirServico(${servico.id})">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filtrarServicos() {
    const clienteId = document.getElementById('filter-cliente').value;
    const status = document.getElementById('filter-status').value;
    
    const linhas = document.querySelectorAll('#servicos-table tbody tr');
    linhas.forEach((linha, index) => {
        const servico = servicos[index];
        let mostrar = true;
        
        if (clienteId && servico.cliente_id != clienteId) {
            mostrar = false;
        }
        
        if (status && servico.status !== status) {
            mostrar = false;
        }
        
        linha.style.display = mostrar ? '' : 'none';
    });
}

function carregarClientesSelect() {
    const selects = ['servico-cliente', 'filter-cliente'];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        const opcaoInicial = select.querySelector('option[value=""]');
        select.innerHTML = '';
        select.appendChild(opcaoInicial);
        
        clientes.forEach(cliente => {
            const option = document.createElement('option');
            option.value = cliente.id;
            option.textContent = cliente.nome;
            select.appendChild(option);
        });
    });
}

// Dashboard
function atualizarDashboard() {
    // Estatísticas gerais
    document.getElementById('total-clientes').textContent = clientes.length;
    document.getElementById('total-servicos').textContent = servicos.length;
    
    const receitaTotal = servicos.reduce((total, servico) => total + parseFloat(servico.valor), 0);
    document.getElementById('receita-total').textContent = `R$ ${receitaTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
    
    const servicosPendentes = servicos.filter(s => s.status === 'Pendente').length;
    document.getElementById('servicos-pendentes').textContent = servicosPendentes;
    
    // Serviços recentes
    renderizarServicosRecentes();
}

function renderizarServicosRecentes() {
    const tbody = document.querySelector('#recent-services-table tbody');
    tbody.innerHTML = '';
    
    const servicosRecentes = servicos
        .sort((a, b) => new Date(b.data_servico) - new Date(a.data_servico))
        .slice(0, 5);
    
    servicosRecentes.forEach(servico => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${servico.cliente_nome}</td>
            <td>${servico.descricao.substring(0, 50)}${servico.descricao.length > 50 ? '...' : ''}</td>
            <td>R$ ${parseFloat(servico.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            <td>${formatarData(servico.data_servico)}</td>
            <td><span class="status-badge status-${servico.status.toLowerCase().replace(' ', '-')}">${servico.status}</span></td>
        `;
        tbody.appendChild(tr);
    });
}

// Modals
function abrirModalCliente() {
    document.getElementById('modal-cliente').classList.add('active');
    document.getElementById('cliente-nome').focus();
}

function fecharModalCliente() {
    document.getElementById('modal-cliente').classList.remove('active');
    document.getElementById('form-cliente').reset();
    document.getElementById('cliente-id').value = '';
    document.getElementById('modal-cliente-title').textContent = 'Novo Cliente';
}

function abrirModalServico() {
    carregarClientesSelect();
    document.getElementById('modal-servico').classList.add('active');
    document.getElementById('servico-data').value = new Date().toISOString().split('T')[0];
    document.getElementById('servico-cliente').focus();
}

function fecharModalServico() {
    document.getElementById('modal-servico').classList.remove('active');
    document.getElementById('form-servico').reset();
    document.getElementById('servico-id').value = '';
    document.getElementById('modal-servico-title').textContent = 'Novo Serviço';
}

function fecharModals() {
    fecharModalCliente();
    fecharModalServico();
}

// Utilitários
function mostrarLoading(mostrar) {
    const loading = document.getElementById('loading');
    if (mostrar) {
        loading.classList.add('active');
    } else {
        loading.classList.remove('active');
    }
}

function mostrarToast(mensagem, tipo = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${tipo}`;
    toast.textContent = mensagem;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

function formatarData(data) {
    return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
}

// Atalhos de teclado
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModals();
    }
});

