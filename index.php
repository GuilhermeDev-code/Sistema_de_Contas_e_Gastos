<?php
// Configurações do banco
$host = 'localhost';
$dbname = 'sistema_contas';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar no banco de dados: " . $e->getMessage());
}

$pagina = $_GET['pagina'] ?? 'dashboard';

$erro = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'criar') {
            $nome = trim($_POST['nome']);
            $servico = trim($_POST['servico']);
            $prazo = $_POST['prazo'];
            $valor = $_POST['valor'];

            if ($nome && $servico && $prazo && is_numeric($valor)) {
                $stmt = $pdo->prepare("INSERT INTO contas (nome, servico, prazo, valor) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $servico, $prazo, $valor]);
                $mensagem = "Conta criada com sucesso!";
                header("Location: " . $_SERVER['PHP_SELF'] . "?pagina=dashboard&msg=" . urlencode($mensagem));
                exit;
            } else {
                $erro = "Por favor, preencha todos os campos corretamente.";
            }
        } elseif ($_POST['acao'] === 'excluir_conta') {
            $idExcluir = intval($_POST['id']);
            if ($idExcluir > 0) {
                $stmt = $pdo->prepare("DELETE FROM contas WHERE id = ?");
                $stmt->execute([$idExcluir]);
                $mensagem = "Conta excluída com sucesso!";
                header("Location: " . $_SERVER['PHP_SELF'] . "?pagina=dashboard&msg=" . urlencode($mensagem));
                exit;
            }
        } elseif ($_POST['acao'] === 'criar_gasto') {
            $conta_id = intval($_POST['conta_id']);
            $descricao = trim($_POST['descricao']);
            $valor_gasto = $_POST['valor_gasto'];
            $data_gasto = $_POST['data_gasto'];

            if ($conta_id > 0 && $descricao && $data_gasto && is_numeric($valor_gasto)) {
                $stmt = $pdo->prepare("INSERT INTO gastos (conta_id, descricao, valor, data_gasto) VALUES (?, ?, ?, ?)");
                $stmt->execute([$conta_id, $descricao, $valor_gasto, $data_gasto]);
                $mensagem = "Gasto adicionado com sucesso!";
                header("Location: " . $_SERVER['PHP_SELF'] . "?pagina=gastos&msg=" . urlencode($mensagem));
                exit;
            } else {
                $erro = "Por favor, preencha todos os campos de gasto corretamente.";
            }
        } elseif ($_POST['acao'] === 'excluir_gasto') {
            $idExcluir = intval($_POST['id']);
            if ($idExcluir > 0) {
                $stmt = $pdo->prepare("DELETE FROM gastos WHERE id = ?");
                $stmt->execute([$idExcluir]);
                $mensagem = "Gasto excluído com sucesso!";
                header("Location: " . $_SERVER['PHP_SELF'] . "?pagina=gastos&msg=" . urlencode($mensagem));
                exit;
            }
        }
    }
}

$stmt = $pdo->query("SELECT * FROM contas ORDER BY nome ASC");
$contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($pagina === 'dashboard') {
    $stmtTotalGastos = $pdo->query("SELECT SUM(valor) as total_gastos FROM gastos");
    $total_gastos = $stmtTotalGastos->fetchColumn();
    if (!$total_gastos) {
        $total_gastos = 0;
    }

    $mesSelecionado = $_GET['mes'] ?? date('Y-m');

    $stmtReceitaMes = $pdo->prepare("SELECT SUM(valor) as total_receita FROM contas WHERE DATE_FORMAT(prazo, '%Y-%m') = ?");
    $stmtReceitaMes->execute([$mesSelecionado]);
    $total_receita_mes = $stmtReceitaMes->fetchColumn();
    if (!$total_receita_mes) {
        $total_receita_mes = 0;
    }
}

if ($pagina === 'gastos') {
    $stmt = $pdo->query("SELECT g.id, g.conta_id, g.descricao, g.valor, g.data_gasto, c.nome as conta_nome FROM gastos g JOIN contas c ON g.conta_id = c.id ORDER BY g.data_gasto DESC");
    $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->query("SELECT conta_id, SUM(valor) as total_gasto FROM gastos GROUP BY conta_id");
    $soma_gastos = [];
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $soma_gastos[$row['conta_id']] = $row['total_gasto'];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sistema de Contas e Gastos</title>
<style>
  /* ... (mesmo estilo CSS do código anterior, você pode copiar daqui) */
  * {margin:0; padding:0; box-sizing:border-box;}
  body {font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f0f2f5; color:#333; display:flex; min-height:100vh;}
  nav {width:220px; background:#24292e; color:#fff; display:flex; flex-direction:column; padding:25px 20px; gap:15px; box-shadow:3px 0 10px rgba(0,0,0,0.15);}
  nav a {display:flex; align-items:center; gap:12px; font-weight:600; text-decoration:none; color:#bbb; padding:12px 16px; border-radius:8px; font-size:16px; transition:background-color 0.25s,color 0.25s;}
  nav a.active, nav a:hover {background-color:#0366d6; color:#fff; box-shadow:0 2px 8px rgba(3,102,214,.6);}
  nav a svg {fill:currentColor; width:20px; height:20px;}
  main {flex-grow:1; padding:30px 40px; background:#fff; overflow-y:auto; box-shadow:inset 0 0 12px #ccc;}
  h1 {margin-bottom:18px; font-weight:700; color:#24292e;}
  .mensagem, .erro {max-width:600px; margin-bottom:20px; font-weight:700; padding:12px 20px; border-radius:8px;}
  .mensagem {background:#e6ffed; color:#22863a; box-shadow:inset 0 0 10px #b2f5bf;}
  .erro {background:#ffeef0; color:#d73a49; box-shadow:inset 0 0 10px #f0c1c9;}
  .total-gastos, .total-receita-mes {font-size:20px; font-weight:700; color:#0366d6; margin-bottom:30px;}
  form {max-width:600px; background:#f9fafb; padding:25px 30px; border-radius:12px; box-shadow:0 2px 10px rgb(0 0 0 / 0.1); margin-bottom:40px;}
  label {display:block; font-weight:600; margin-bottom:6px; margin-top:18px; color:#444;}
  input, select {width:100%; padding:12px 14px; border-radius:8px; border:1.8px solid #ddd; font-size:16px; transition:border-color 0.2s ease;}
  input:focus, select:focus {outline:none; border-color:#0366d6; box-shadow:0 0 6px rgba(3,102,214,0.4);}
  button, .btn-excluir {margin-top:28px; background-color:#0366d6; border:none; padding:12px 26px; color:#fff; font-weight:600; font-size:16px; border-radius:8px; cursor:pointer; transition:background-color 0.25s ease;}
  button:hover, .btn-excluir:hover {background-color:#024a9c;}
  .btn-excluir {padding:8px 14px; font-size:14px; border-radius:6px;}
  table {width:100%; border-collapse:collapse; box-shadow:0 4px 12px rgb(0 0 0 / 0.1); border-radius:12px; overflow:hidden; background:#fff;}
  th, td {text-align:center; padding:15px 12px; border-bottom:1px solid #e1e4e8; font-size:15px;}
  th {background-color:#0366d6; color:#fff; font-weight:600; letter-spacing:0.03em;}
  tbody tr:hover {background-color:#f1f8ff;}
  tbody tr:last-child td {border-bottom:none;}
  @media (max-width:768px) {
    body {flex-direction:column;}
    nav {width:100%; flex-direction:row; justify-content:center; padding:15px 0; gap:20px; box-shadow:0 3px 10px rgba(0,0,0,0.15);}
    nav a {padding:10px 14px; font-size:14px;}
    main {padding:20px; box-shadow:none;}
    form {max-width:100%; padding:20px;}
  }
</style>
</head>
<body>
<nav>
  <a href="?pagina=dashboard" class="<?= $pagina === 'dashboard' ? 'active' : '' ?>">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8v-10h-8v10zm0-18v6h8V3h-8z"/></svg>
    Dashboard
  </a>
  <a href="?pagina=gastos" class="<?= $pagina === 'gastos' ? 'active' : '' ?>">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H1v12a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V7h-2zM5 5h14v2H5V5zm14 12H5v-8h14v8z"/></svg>
    Gestão de Gastos
  </a>
</nav>

<main>
  <?php if (!empty($_GET['msg'])): ?>
    <div class="mensagem"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <?php if (!empty($erro)): ?>
    <div class="erro"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <?php if ($pagina === 'dashboard'): ?>

    <h1>Dashboard</h1>

    <div class="total-gastos">
      Total Geral de Gastos: R$ <?= number_format($total_gastos, 2, ',', '.') ?>
    </div>

    <form method="GET" action="" style="max-width: 600px; margin-bottom: 40px;">
      <label for="mes">Filtrar Ganhos por Mês</label>
      <input type="month" id="mes" name="mes" value="<?= htmlspecialchars($mesSelecionado) ?>" />
      <input type="hidden" name="pagina" value="dashboard" />
      <button type="submit" style="margin-top: 15px;">Filtrar</button>
    </form>

    <div class="total-receita-mes">
      Ganhos no mês <?= date('m/Y', strtotime($mesSelecionado)) ?>: R$ <?= number_format($total_receita_mes, 2, ',', '.') ?>
    </div>

    <hr style="margin: 40px 0; border-color: #ddd;" />

    <h2>Criar Nova Conta</h2>
    <form method="POST" action="">
      <input type="hidden" name="acao" value="criar" />
      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" required autocomplete="off"/>

      <label for="servico">Serviço</label>
      <input type="text" id="servico" name="servico" required autocomplete="off"/>

      <label for="prazo">Prazo de Entrega</label>
      <input type="date" id="prazo" name="prazo" required />

      <label for="valor">Valor Alvo (R$)</label>
      <input type="number" id="valor" name="valor" min="0" step="0.01" required />

      <button type="submit">Criar Conta</button>
    </form>

    <?php if (count($contas) > 0): ?>
      <h2>Contas Cadastradas</h2>
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Serviço</th>
            <th>Prazo de Entrega</th>
            <th>Valor Alvo (R$)</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contas as $conta): ?>
            <tr>
              <td><?= htmlspecialchars($conta['nome']) ?></td>
              <td><?= htmlspecialchars($conta['servico']) ?></td>
              <td><?= date('d/m/Y', strtotime($conta['prazo'])) ?></td>
              <td>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></td>
              <td>
                <form class="excluir-form" method="POST" action="" onsubmit="return confirm('Deseja realmente excluir esta conta?');">
                  <input type="hidden" name="acao" value="excluir_conta" />
                  <input type="hidden" name="id" value="<?= $conta['id'] ?>" />
                  <button type="submit" class="btn-excluir" title="Excluir conta">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>Nenhuma conta cadastrada ainda.</p>
    <?php endif; ?>

  <?php elseif ($pagina === 'gastos'): ?>

    <h1>Gestão de Gastos</h1>

    <form method="POST" action="">
      <input type="hidden" name="acao" value="criar_gasto" />

      <label for="conta_id">Conta</label>
      <select name="conta_id" id="conta_id" required>
        <option value="">Selecione uma conta</option>
        <?php foreach ($contas as $conta): ?>
          <option value="<?= $conta['id'] ?>"><?= htmlspecialchars($conta['nome']) ?> (<?= htmlspecialchars($conta['servico']) ?>)</option>
        <?php endforeach; ?>
      </select>

      <label for="descricao">Descrição do Gasto</label>
      <input type="text" id="descricao" name="descricao" required autocomplete="off"/>

      <label for="valor_gasto">Valor do Gasto (R$)</label>
      <input type="number" id="valor_gasto" name="valor_gasto" min="0" step="0.01" required />

      <label for="data_gasto">Data do Gasto</label>
      <input type="date" id="data_gasto" name="data_gasto" required />

      <button type="submit">Adicionar Gasto</button>
    </form>

    <?php if (!empty($gastos)): ?>
      <table>
        <thead>
          <tr>
            <th>Conta</th>
            <th>Descrição</th>
            <th>Valor (R$)</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($gastos as $gasto): ?>
            <tr>
              <td><?= htmlspecialchars($gasto['conta_nome']) ?></td>
              <td><?= htmlspecialchars($gasto['descricao']) ?></td>
              <td>R$ <?= number_format($gasto['valor'], 2, ',', '.') ?></td>
              <td><?= date('d/m/Y', strtotime($gasto['data_gasto'])) ?></td>
              <td>
                <form class="excluir-form" method="POST" action="" onsubmit="return confirm('Deseja realmente excluir este gasto?');">
                  <input type="hidden" name="acao" value="excluir_gasto" />
                  <input type="hidden" name="id" value="<?= $gasto['id'] ?>" />
                  <button type="submit" class="btn-excluir" title="Excluir gasto">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h2 style="margin-top:40px; color:#24292e;">Resumo dos Gastos por Conta</h2>
      <table>
        <thead>
          <tr>
            <th>Conta</th>
            <th>Total Gasto (R$)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contas as $conta): ?>
            <tr>
              <td><?= htmlspecialchars($conta['nome']) ?></td>
              <td>R$ <?= number_format($soma_gastos[$conta['id']] ?? 0, 2, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>Nenhum gasto cadastrado ainda.</p>
    <?php endif; ?>

  <?php else: ?>
    <h1>Página não encontrada</h1>
  <?php endif; ?>
</main>

</body>
</html>
