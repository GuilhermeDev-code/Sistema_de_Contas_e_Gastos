# Sistema de Gerenciamento de Rendas - Serralheria

## Descrição

Sistema web completo desenvolvido em PHP para gerenciamento de rendas de uma serralheria, permitindo o cadastro e controle de clientes e serviços prestados.

## Funcionalidades

### Dashboard
- Visão geral com estatísticas dos negócios
- Total de clientes cadastrados
- Total de serviços realizados
- Receita total acumulada
- Quantidade de serviços pendentes
- Lista dos serviços mais recentes

### Gerenciamento de Clientes
- Cadastro de novos clientes
- Listagem de todos os clientes
- Edição de informações dos clientes
- Exclusão de clientes (soft delete)
- Busca por nome de cliente
- Campos: Nome, Telefone, Email, Endereço

### Gerenciamento de Serviços
- Cadastro de novos serviços
- Listagem de todos os serviços
- Edição de informações dos serviços
- Exclusão de serviços
- Filtros por cliente e status
- Campos: Cliente, Descrição, Valor, Data do Serviço, Status, Observações
- Status disponíveis: Pendente, Em andamento, Concluído, Cancelado

## Tecnologias Utilizadas

- **Backend**: PHP 8.1 com PDO
- **Banco de Dados**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Servidor Web**: Apache 2.4
- **Design**: Interface responsiva com gradientes modernos

## Estrutura do Projeto

```
serralheria-rendas/
├── config/
│   ├── database.php          # Configuração do banco de dados
│   └── database.sql          # Script de criação do banco
├── src/
│   ├── Cliente.php           # Classe para gerenciar clientes
│   └── Servico.php           # Classe para gerenciar serviços
├── public/
│   ├── index.html            # Página principal
│   ├── api/
│   │   ├── clientes.php      # API REST para clientes
│   │   └── servicos.php      # API REST para serviços
│   └── assets/
│       ├── css/
│       │   └── style.css     # Estilos da aplicação
│       └── js/
│           └── app.js        # JavaScript da aplicação
└── README.md                 # Este arquivo
```

## Instalação

### Pré-requisitos
- Apache 2.4+
- PHP 8.1+
- MySQL 8.0+
- Extensões PHP: pdo, pdo_mysql, mysqli

### Passos de Instalação

1. **Clone ou copie os arquivos do projeto**
   ```bash
   cp -r serralheria-rendas/ /var/www/html/
   ```

2. **Configure o banco de dados**
   ```bash
   mysql -u root -p < config/database.sql
   ```

3. **Configure as permissões**
   ```bash
   chown -R www-data:www-data /var/www/html/
   chmod -R 755 /var/www/html/
   ```

4. **Configure o MySQL (se necessário)**
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
   FLUSH PRIVILEGES;
   ```

5. **Reinicie o Apache**
   ```bash
   systemctl restart apache2
   ```

## Configuração

### Banco de Dados

O arquivo `config/database.php` contém as configurações de conexão:

```php
private $host = 'localhost';
private $db_name = 'serralheria_rendas';
private $username = 'root';
private $password = '';
```

Ajuste essas configurações conforme seu ambiente.

### Estrutura do Banco

O sistema utiliza duas tabelas principais:

**Tabela `clientes`:**
- `id` (INT, PK, AUTO_INCREMENT)
- `nome` (VARCHAR(255), NOT NULL)
- `telefone` (VARCHAR(20))
- `email` (VARCHAR(255))
- `endereco` (TEXT)
- `data_cadastro` (TIMESTAMP)
- `ativo` (BOOLEAN)

**Tabela `servicos`:**
- `id` (INT, PK, AUTO_INCREMENT)
- `cliente_id` (INT, FK)
- `descricao` (TEXT, NOT NULL)
- `valor` (DECIMAL(10,2), NOT NULL)
- `data_servico` (DATE, NOT NULL)
- `status` (VARCHAR(50))
- `observacoes` (TEXT)
- `data_cadastro` (TIMESTAMP)

## API REST

### Endpoints de Clientes

**GET /api/clientes.php**
- Lista todos os clientes
- Parâmetros opcionais: `id`, `busca`

**POST /api/clientes.php**
- Cria novo cliente
- Body: `{"nome": "string", "telefone": "string", "email": "string", "endereco": "string"}`

**PUT /api/clientes.php**
- Atualiza cliente existente
- Body: `{"id": int, "nome": "string", "telefone": "string", "email": "string", "endereco": "string"}`

**DELETE /api/clientes.php?id={id}**
- Exclui cliente (soft delete)

### Endpoints de Serviços

**GET /api/servicos.php**
- Lista todos os serviços
- Parâmetros opcionais: `id`, `cliente_id`, `status`, `relatorio=periodo&data_inicio&data_fim`

**POST /api/servicos.php**
- Cria novo serviço
- Body: `{"cliente_id": int, "descricao": "string", "valor": float, "data_servico": "YYYY-MM-DD", "status": "string", "observacoes": "string"}`

**PUT /api/servicos.php**
- Atualiza serviço existente
- Body: `{"id": int, "cliente_id": int, "descricao": "string", "valor": float, "data_servico": "YYYY-MM-DD", "status": "string", "observacoes": "string"}`

**DELETE /api/servicos.php?id={id}**
- Exclui serviço

## Uso do Sistema

### Acessando o Sistema
1. Abra o navegador e acesse `http://localhost` (ou o endereço do seu servidor)
2. O sistema carregará automaticamente na tela do Dashboard

### Navegação
- Use os botões no topo para navegar entre as seções: Dashboard, Clientes e Serviços
- Cada seção possui suas próprias funcionalidades específicas

### Gerenciando Clientes
1. Clique em "Clientes" no menu superior
2. Para adicionar: clique em "Novo Cliente" e preencha o formulário
3. Para editar: clique no botão "Editar" na linha do cliente desejado
4. Para excluir: clique no botão "Excluir" (confirmação será solicitada)
5. Use a barra de busca para encontrar clientes específicos

### Gerenciando Serviços
1. Clique em "Serviços" no menu superior
2. Para adicionar: clique em "Novo Serviço" e preencha o formulário
3. Para editar: clique no botão "Editar" na linha do serviço desejado
4. Para excluir: clique no botão "Excluir" (confirmação será solicitada)
5. Use os filtros para visualizar serviços por cliente ou status específico

### Dashboard
- Visualize estatísticas gerais do negócio
- Acompanhe a receita total e serviços pendentes
- Veja os serviços mais recentes em uma tabela resumida

## Características Técnicas

### Segurança
- Sanitização de dados de entrada
- Prepared statements para prevenir SQL injection
- Validação de dados no frontend e backend

### Performance
- Consultas otimizadas com índices
- Carregamento assíncrono de dados
- Interface responsiva para diferentes dispositivos

### Usabilidade
- Interface intuitiva e moderna
- Notificações toast para feedback do usuário
- Modais para formulários
- Filtros e busca em tempo real

## Dados de Exemplo

O sistema vem com dados de exemplo pré-carregados:

**Clientes:**
- João Silva
- Maria Santos
- Pedro Oliveira

**Serviços:**
- Portão de ferro com grade decorativa (João Silva) - R$ 1.500,00
- Janela de ferro para cozinha (João Silva) - R$ 800,00
- Grade de proteção para janela (Maria Santos) - R$ 600,00
- Corrimão para escada (Pedro Oliveira) - R$ 1.200,00

## Suporte e Manutenção

### Logs
- Logs do Apache: `/var/log/apache2/error.log`
- Logs do MySQL: `/var/log/mysql/error.log`

### Backup
Recomenda-se fazer backup regular do banco de dados:
```bash
mysqldump -u root -p serralheria_rendas > backup_$(date +%Y%m%d).sql
```

### Atualizações
Para atualizações do sistema, substitua os arquivos mantendo a configuração do banco de dados.

## Licença

Este sistema foi desenvolvido especificamente para gerenciamento de serralheria e pode ser customizado conforme necessário.

---

**Desenvolvido com PHP, MySQL e tecnologias web modernas para oferecer uma solução completa de gerenciamento de rendas para serralherias.**

