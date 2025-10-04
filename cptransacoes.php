<?php

session_start();

date_default_timezone_set('America/Sao_Paulo'); // Ajusta o fuso horário para Brasília

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Transações</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .daterangepicker-container {
            display: flex;
            justify-content: flex-end;
        }

        #dateFilter {
            width: 250px;
            font-size: 14px;
            padding: 5px;
        }

        .dataTables_filter {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    

/* === Pro Dashboard Skin (transactions) === */
.page-title {
  font-weight: 700;
  letter-spacing: .2px;
  margin-bottom: .75rem;
}
.kpi {
  position: relative;
  overflow: hidden;
  border-radius: 16px;
  padding: 18px;
  background: linear-gradient(145deg, rgba(59,130,246,.15), rgba(167,139,250,.10));
  border: 1px solid rgba(148,163,184,.18);
  box-shadow: 0 10px 30px rgba(2,6,23,.45);
}
.kpi .kpi-title {
  font-size: .95rem;
  color: #c7d2fe;
  margin: 0 0 .25rem;
  display:flex;align-items:center;gap:.5rem;
}
.kpi .kpi-value {
  font-size: 1.75rem;
  font-weight: 800;
  letter-spacing:.3px;
}
.kpi:before, .kpi:after{
  content:"";
  position:absolute;
  inset:auto -20% -40% -20%;
  height:80px;
  background: radial-gradient(150px 40px at 10% 30%, rgba(34,211,238,.25), transparent 60%),
              radial-gradient(150px 40px at 80% 70%, rgba(167,139,250,.25), transparent 60%);
  transform: translateY(0);
  filter: blur(14px);
  pointer-events:none;
}
.kpi.success{ background: linear-gradient(145deg, rgba(16,185,129,.18), rgba(34,211,238,.10)); }
.kpi.info{ background: linear-gradient(145deg, rgba(56,189,248,.18), rgba(167,139,250,.10)); }
.kpi.warning{ background: linear-gradient(145deg, rgba(245,158,11,.18), rgba(56,189,248,.10)); }

/* Pills for status from existing .text-success/.text-warning */
.table td .text-success,
.table td .text-warning {
  display:inline-flex;align-items:center;gap:.35rem;
  padding:.2rem .55rem;border-radius:999px;font-weight:600;
  border:1px solid rgba(148,163,184,.25);
}
.table td .text-success{ background: rgba(16,185,129,.18); color:#86efac !important; }
.table td .text-warning{ background: rgba(234,179,8,.18);  color:#fde68a !important; }
.table td .text-success::before{content:"";width:.4rem;height:.4rem;border-radius:999px;background:#34d399;box-shadow:0 0 0 3px rgba(34,197,94,.15);}
.table td .text-warning::before{content:"";width:.4rem;height:.4rem;border-radius:999px;background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.15);}

/* DataTable header + rows */
.table thead th {
  font-size:.85rem; text-transform:uppercase; letter-spacing:.6px;
}
.table tbody td{ vertical-align: middle; }

/* Filter bar */
.toolbar {
  display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:.75rem;
}
.toolbar .search { display:flex;align-items:center; gap:.5rem; }
.toolbar .search i{ opacity:.8; }
.filter-pill {
  border:1px solid rgba(148,163,184,.25);border-radius:999px;padding:.35rem .75rem;
  background:linear-gradient(180deg, rgba(99,102,241,.18), rgba(17,24,39,.4));
}

/* Navbar icons */
.navbar .nav-link .bi{ margin-right:.45rem; opacity:.9; }
</style>

<!-- THEME:start -->
<style>
  :root {
    --bg-1: #0f172a; /* slate-900 */
    --bg-2: #111827; /* gray-900 */
    --bg-3: #1f2937; /* gray-800 */
    --card: #111827;
    --card-2: #0b1220;
    --muted: #9ca3af;
    --ring: #60a5fa;
    --accent: #22d3ee; /* cyan-400 */
    --accent-2: #a78bfa; /* violet-400 */
    --success: #34d399;
    --danger: #f87171;
    --warning: #f59e0b;
    --info: #38bdf8;
  }

  html[data-bs-theme="dark"] body {
    background: radial-gradient(1200px 800px at 10% -10%, rgba(34,211,238,0.12), transparent 60%),
                radial-gradient(1200px 800px at 110% -10%, rgba(167,139,250,0.10), transparent 60%),
                linear-gradient(180deg, var(--bg-1), var(--bg-2));
    color: #e5e7eb;
    min-height: 100vh;
  }

  .navbar, .dropdown-menu {
    background: rgba(15,23,42,0.85) !important;
    backdrop-filter: saturate(130%) blur(6px);
  }

  .card, .modal-content, .offcanvas, .toast, .form-control, .form-select, .input-group-text, .list-group-item {
    background-color: var(--card) !important;
    border: 1px solid rgba(148,163,184,0.12) !important;
    color: #e5e7eb !important;
  }

  .table {
    --bs-table-bg: transparent;
    --bs-table-striped-bg: rgba(148,163,184,0.04);
    --bs-table-striped-color: #e5e7eb;
    --bs-table-hover-bg: rgba(148,163,184,0.08);
    --bs-table-hover-color: #fff;
    color: #e5e7eb;
  }
  table.dataTable thead th, .table thead th {
    background: linear-gradient(180deg, rgba(59,130,246,0.15), rgba(59,130,246,0.02));
    color: #e5e7eb;
    border-bottom: 1px solid rgba(148,163,184,0.2);
    position: sticky;
    top: 0;
    z-index: 2;
  }

  .btn {
    position: relative;
    overflow: hidden;
    border-radius: 12px !important;
    transition: transform .08s ease, box-shadow .2s ease, background-color .2s ease;
  }
  .btn:hover { box-shadow: 0 8px 24px rgba(34,211,238,0.12); transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }

  .btn-primary { background: linear-gradient(135deg, var(--accent), var(--accent-2)); border: 0; }
  .btn-success { background: linear-gradient(135deg, #10b981, #22d3ee); border: 0; }
  .btn-danger  { background: linear-gradient(135deg, #ef4444, #f59e0b); border: 0; }
  .btn-outline-secondary, .btn-secondary {
    background: linear-gradient(135deg, #374151, #1f2937);
    border: 1px solid rgba(148,163,184,0.25) !important;
  }

  .form-control, .form-select {
    border-radius: 12px;
    border: 1px solid rgba(148,163,184,0.2) !important;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);
  }
  .form-control:focus, .form-select:focus {
    outline: none;
    border-color: var(--ring) !important;
    box-shadow: 0 0 0 4px rgba(96,165,250,0.15);
  }

  .card {
    border-radius: 18px;
    box-shadow: 0 20px 40px rgba(2,6,23,0.6), inset 0 1px 0 rgba(255,255,255,0.02);
    transition: transform .2s ease, box-shadow .2s ease, background .3s ease;
  }
  .card:hover { transform: translateY(-2px); box-shadow: 0 30px 60px rgba(2,6,23,0.7); }

  .badge { border-radius: 999px; }

  .nav-link, .navbar-brand {
    transition: color .2s ease, transform .1s ease;
  }
  .nav-link:hover { color: var(--accent) !important; }
  .nav-link:active { transform: scale(0.98); }

  /* Pills/filters bar */
  .filter-bar {
    background: linear-gradient(180deg, rgba(99,102,241,0.15), rgba(99,102,241,0.03));
    border: 1px solid rgba(148,163,184,0.18);
    border-radius: 14px;
    padding: .5rem;
  }

  /* Daterangepicker tweak (dark) */
  .daterangepicker, .daterangepicker .calendar-table {
    background: var(--card) !important;
    color: #e5e7eb !important;
    border-color: rgba(148,163,184,0.2) !important;
  }
  .daterangepicker .ranges li:hover, .daterangepicker td.active, .daterangepicker td.active:hover {
    background: linear-gradient(135deg, var(--accent), var(--accent-2)) !important;
    color: #0b1220 !important;
  }

  /* Sweet micro shadows on table rows */
  tbody tr { transition: background .15s ease, transform .05s ease; }
  tbody tr:active { transform: scale(0.999); }
</style>
<!-- THEME:end -->
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Painel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="#navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="cpdashboard.php">Transações</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pixels.php"><i class="bi bi-bullseye"></i> Pixel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="pixels2.php"><i class="bi bi-flag"></i> Pixel Conversão</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="cptransacoes.php">Transações</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="page-title">Gerenciamento de Transações</h2>
<!-- ======= DASHBOARD SECTION (auto from table) ======= -->
<div class="row g-3 mb-3">
  <div class="col-lg-3 col-md-6">
    <div class="card kpi success">
      <div class="card-body">
        <div class="kpi-title"><i class="bi bi-graph-up-arrow"></i> Vendas aprovadas - Hoje</div>
        <div id="kpiTodayValue" class="kpi-value">R$ 0,00</div>
        <div class="text-success-50 small">Comissão: <span id="kpiTodayComissao">R$ 0,00</span></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card kpi info">
      <div class="card-body">
        <div class="kpi-title"><i class="bi bi-graph-up"></i> Vendas aprovadas - Ontem</div>
        <div id="kpiYesterdayValue" class="kpi-value">R$ 0,00</div>
        <div class="text-info small">Comissão: <span id="kpiYesterdayComissao">R$ 0,00</span></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card kpi warning">
      <div class="card-body">
        <div class="kpi-title"><i class="bi bi-calendar3"></i> Vendas aprovadas - 30 dias</div>
        <div id="kpi30dValue" class="kpi-value">R$ 0,00</div>
        <div class="text-warning small">Comissão: <span id="kpi30dComissao">R$ 0,00</span></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card kpi">
      <div class="card-body">
        <div class="kpi-title"><i class="bi bi-cash-coin"></i> Ticket médio - 30 dias</div>
        <div id="kpiTicketValue" class="kpi-value">R$ 0,00</div>
        <div class="text-muted small">Hoje: <span id="kpiTicketHoje">R$ 0,00</span></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-3">Quantidade aprovadas - 30 dias</h5>
        <canvas id="chartQtd30d" height="110"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-3">Valor aprovadas - 30 dias</h5>
        <canvas id="chartValor30d" height="110"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="card kpi">
      <div class="card-body">
        <div class="kpi-title"><i class="bi bi-collection"></i> Vendas aprovadas - Todas</div>
        <div id="kpiAllValue" class="kpi-value">R$ 0,00</div>
        <div class="text-muted small">Comissão: <span id="kpiAllComissao">R$ 0,00</span></div>
      </div>
    </div>
  </div>
</div>

<!-- ======= /DASHBOARD SECTION ======= -->

<div class="toolbar"><div class="search"><i class="bi bi-search"></i><span class="muted">Use a busca à direita da tabela</span></div><div class="filter-pill"><i class="bi bi-calendar3"></i> <span>Filtro de data</span> &nbsp;<input id="dateFilter" type="text" class="form-control form-control-sm d-inline-block" style="width:180px;background:transparent;"></div></div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card kpi success">
                <div class="card-body">
                    <h5 class="card-title kpi-title"><i class="bi bi-check-circle"></i> Pagamentos Aprovados</h5>
                    <p class="card-text" id="approvedCount" class="kpi-value">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi warning">
                <div class="card-body">
                    <h5 class="card-title kpi-title"><i class="bi bi-qr-code-scan"></i> Total de Pix Gerados</h5>
                    <p class="card-text" id="totalPixGenerated" class="kpi-value">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi info">
                <div class="card-body">
                    <h5 class="card-title kpi-title"><i class="bi bi-cash-coin"></i> Total de Pix Pagos</h5>
                    <p class="card-text" id="totalPixPaid" class="kpi-value">R$ 0,00</p>
                </div>
            </div>
        </div>
    </div>

    <table id="transactionsTable" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>CPF</th>
            <th>Serviço</th>
            <th>Descrição</th>
            <th>Valor</th>
            <th>Status</th>
            <th>Data Criação</th>
        </tr>
        </thead>
        <tbody>
        <?php
        require_once 'config/database.php';

        if ($conn->connect_error) {
            die("<tr><td colspan='8'>Erro na conexão com o banco de dados.</td></tr>");
        }

        $result = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $statusText = match ($row['status']) {
                    'approved', 'paid' => '<span class="text-success">Aprovado</span>',
                    'waiting_payment' => '<span class="text-warning">Aguardando Pagamento</span>',
                    default => 'Outro',
                };

                $formattedAmount = isset($row['amount']) ? number_format($row['amount'] / 100, 2, ',', '.') : '0,00';

                $createdAt = new DateTime($row['created_at'], new DateTimeZone('UTC'));
                $createdAt->modify('+4 hours');
                $formattedCreatedAt = $createdAt->format('d/m/Y H:i:s');

                echo "<tr data-status='{$row['status']}' data-amount='{$row['amount']}' data-created='" . $createdAt->format('Y-m-d') . "'>
                    <td>{$row['id']}</td>
                    <td>{$row['customer_name']}</td>
                    <td>{$row['customer_cpf']}</td>
                    <td>{$row['service_type']}</td>
                    <td>{$row['service_description']}</td>
                    <td>R$ {$formattedAmount}</td>
                    <td>{$statusText}</td>
                    <td>{$formattedCreatedAt}</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='8'>Nenhum registro encontrado.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function () {
        const table = $('#transactionsTable').DataTable({
            dom: '<"daterangepicker-container">frtip',
            order: [[7, 'desc']],
            columnDefs: [
                { type: 'datetime', targets: 7 }
            ]
        });

        $('.daterangepicker-container').html('<input type="text" id="dateFilter" class="form-control" placeholder="Selecione o Período" autocomplete="off">');

        function updateCounters(start, end, allData = false) {
            const rows = allData ? table.rows().data() : table.rows({ search: 'applied' }).data();
            let approvedCount = 0;
            let totalPixGenerated = 0;
            let totalPixPaid = 0;

            rows.each(function (value) {
                const status = value[6];
                const amountText = value[5];
                const amount = parseFloat(amountText.replace('R$ ', '').replace(/\./g, '').replace(',', '.'));

                if (allData || (start && end)) {
                    if (status.includes('Aprovado')) {
                        approvedCount++;
                        totalPixPaid += amount;
                    }
                    if (status.includes('Aguardando Pagamento')) {
                        totalPixGenerated++;
                    }
                }
            });

            $('#approvedCount').text(approvedCount.toLocaleString('pt-BR'));
            $('#totalPixGenerated').text(totalPixGenerated.toLocaleString('pt-BR'));
            $('#totalPixPaid').text(`R$ ${totalPixPaid.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
        }

        $('#dateFilter').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                firstDay: 0
            },
            autoUpdateInput: false,
            opens: 'left',
            ranges: {
                'Hoje': [moment().startOf('day'), moment().endOf('day')],
                'Ontem': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                'Últimos 7 Dias': [moment().subtract(6, 'days'), moment()],
                'Este Mês': [moment().startOf('month'), moment().endOf('month')],
                'Mês Passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Todo Período': []
            }
        }, function (start, end, label) {
            if (label === 'Todo Período') {
                table.column(7).search('').draw();
                updateCounters(null, null, true);
            } else {
                table.column(7).search(start.format('DD/MM/YYYY') + '|' + end.format('DD/MM/YYYY'), true, false).draw();
                updateCounters(start, end);
            }
        });

        // Inicializa "Hoje" ao carregar a página
        const today = moment().format('DD/MM/YYYY');
        const startOfDay = moment().startOf('day');
        const endOfDay = moment().endOf('day');
        table.column(7).search(today).draw();
        updateCounters(startOfDay, endOfDay);
        $('#dateFilter').val('Hoje'); // Atualiza o seletor para "Hoje"
    });
</script>


<!-- DASHBOARD: scripts -->
<script>
(function(){
  function brl(n){ return (n/100).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}); }
  function startOfDay(d){ let x=new Date(d); x.setHours(0,0,0,0); return x; }
  function isApproved(s){ s=(s||'').toLowerCase(); return s==='approved' || s==='paid' || s==='approved_payment' || s==='aprovado'; }

  function collect(){
    const rows = Array.from(document.querySelectorAll('#transactionsTable tbody tr'));
    return rows.map(r=>{
      const cents = parseInt(r.dataset.amount||'0',10) || 0;
      const st = (r.dataset.status||'').trim();
      const created = r.dataset.created ? new Date(r.dataset.created+'T00:00:00') : null;
      return {cents, st, created};
    }).filter(x=>x.created);
  }

  function sumByDay(items, daysBack){
    const map = new Map();
    const today = startOfDay(new Date());
    for(let i=daysBack-1;i>=0;i--){
      const d = new Date(today); d.setDate(d.getDate()-i);
      map.set(d.toISOString().slice(0,10), {count:0, cents:0});
    }
    items.forEach(it=>{
      if(isApproved(it.st)){ centsAll += it.cents; cntAll++; }
      const key = startOfDay(it.created).toISOString().slice(0,10);
      if (map.has(key) && isApproved(it.st)) {
        const obj = map.get(key);
        obj.count += 1;
        obj.cents += it.cents;
      }
    });
    return map;
  }

  function computeKPIs(items){
    let centsAll=0, cntAll=0;
    const today = startOfDay(new Date());
    const yesterday = startOfDay(new Date(today)); yesterday.setDate(yesterday.getDate()-1);
    const d30 = startOfDay(new Date(today)); d30.setDate(d30.getDate()-29);

    let centsToday=0, cntToday=0;
    let centsYesterday=0, cntYesterday=0;
    let cents30=0, cnt30=0;

    items.forEach(it=>{
      if(isApproved(it.st)){ centsAll += it.cents; cntAll++; }
      if(!isApproved(it.st)) return;
      const d = startOfDay(it.created).getTime();
      if (d === today.getTime()){ centsToday += it.cents; cntToday++; }
      if (d === yesterday.getTime()){ centsYesterday += it.cents; cntYesterday++; }
      if (it.created >= d30 && it.created <= today){ cents30 += it.cents; cnt30++; }
    });

    const comissaoRate = 0; // ajuste se tiver regra
    const set = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent = val; };

    set('kpiTodayValue', brl(centsToday));
    set('kpiYesterdayValue', brl(centsYesterday));
    set('kpi30dValue', brl(cents30));
    set('kpiTicketValue', cnt30? brl(Math.round(cents30/cnt30)) : 'R$ 0,00');
    set('kpiTicketHoje', cntToday? brl(Math.round(centsToday/cntToday)) : 'R$ 0,00');
    set('kpiAllValue', brl(centsAll));
    set('kpiAllComissao', brl(Math.round(centsAll*comissaoRate)));
    set('kpiTodayComissao', brl(Math.round(centsToday*comissaoRate)));
    set('kpiYesterdayComissao', brl(Math.round(centsYesterday*comissaoRate)));
    set('kpi30dComissao', brl(Math.round(cents30*comissaoRate)));
  }

  function renderCharts(items){
    const days = 30;
    const map = sumByDay(items, days);
    const labels = Array.from(map.keys()).map(s=>{ const [y,m,d]=s.split('-'); return d+'/'+m; });
    const counts = Array.from(map.values()).map(v=>v.count);
    const values = Array.from(map.values()).map(v=> (v.cents/100).toFixed(2));

    const c1 = document.getElementById('chartQtd30d');
    const c2 = document.getElementById('chartValor30d');

    if (c1){
      new Chart(c1, {
        type:'line',
        data:{ labels, datasets:[{label:'Total', data:counts, fill:true, tension:.35, pointRadius:3}]},
        options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{x:{grid:{display:false}}, y:{beginAtZero:true}}}
      });
    }
    if (c2){
      new Chart(c2, {
        type:'line',
        data:{ labels, datasets:[{label:'R$ aprovados', data:values, fill:true, tension:.35, pointRadius:3}]},
        options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{x:{grid:{display:false}}, y:{beginAtZero:true}}}
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    const items = collect();
    computeKPIs(items);
    renderCharts(items);
  });
})();
</script>

<!-- UI-EFFECTS:start -->
<script>
(function(){
  // Enable Bootstrap dark theme if not already set
  try {
    var html = document.documentElement;
    if (!html.getAttribute('data-bs-theme')) html.setAttribute('data-bs-theme','dark');
  } catch(e){}

  // Ripple effect for buttons/links
  const addRipple = (el) => {
    el.addEventListener('click', function(e){
      const rect = el.getBoundingClientRect();
      const circle = document.createElement('span');
      const d = Math.max(rect.width, rect.height);
      circle.style.width = circle.style.height = d + 'px';
      circle.style.left = (e.clientX - rect.left - d/2) + 'px';
      circle.style.top = (e.clientY - rect.top - d/2) + 'px';
      circle.className = 'ripple';
      el.appendChild(circle);
      setTimeout(()=> circle.remove(), 600);
    }, {passive:true});
  };

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.btn, .nav-link, .page-link').forEach(addRipple);
  });
})();
</script>
<style>
  .ripple {
    position: absolute;
    border-radius: 50%;
    transform: scale(0);
    animation: ripple .6s linear;
    background: rgba(255,255,255,.35);
    pointer-events: none;
  }
  @keyframes ripple {
    to { transform: scale(2.5); opacity: 0; }
  }
</style>
<!-- UI-EFFECTS:end -->

<!-- Pushcut Modal -->
<div class="modal fade" id="pushcutModal" tabindex="-1" aria-labelledby="pushcutLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pushcutLabel">Configurar Pushcut</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="pushcutUrl" class="form-label">URL do Pushcut</label>
          <input type="text" class="form-control" id="pushcutUrl" placeholder="https://api.pushcut.io/...." value="<?php
            $pcfile = __DIR__ . '/config/pushcut.json';
            if (file_exists($pcfile)) { $pc = json_decode(file_get_contents($pcfile), true); echo htmlspecialchars($pc['pushcut_url'] ?? '', ENT_QUOTES, 'UTF-8'); }
          ?>">
          <div class="form-text">Cole aqui sua URL completa do Pushcut.</div>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" id="savePushcutBtn">Salvar</button>
          <button class="btn btn-outline-secondary" id="testPushcutBtn">Testar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Floating actions -->
<div class="position-fixed" style="right: 18px; bottom: 18px; z-index: 1030;">
  <div class="btn-group dropup">
    <button type="button" class="btn btn-outline-light" id="toggleAutoBtn">Auto: OFF</button>
    <button type="button" class="btn btn-pink" data-bs-toggle="modal" data-bs-target="#pushcutModal">
      <i class="bi bi-bell"></i> Pushcut
    </button>
    <button type="button" class="btn btn-outline-light" id="syncPushcutNow">
      <i class="bi bi-arrow-repeat"></i>
    </button>
  </div>
</div>
<style>
  .btn-pink{ background:#ec0978; color:#fff; }
  .btn-pink:hover{ background:#d2086b; color:#fff; }
</style>

<script>
(async function(){
  function toast(msg, type='info'){
    const el = document.createElement('div');
    el.textContent = msg;
    el.style.position='fixed'; el.style.right='20px'; el.style.bottom='80px';
    el.style.padding='10px 14px'; el.style.borderRadius='12px';
    el.style.background = type==='success' ? '#16a34a' : (type==='error' ? '#b91c1c' : '#334155');
    el.style.color='#fff'; el.style.zIndex=2000; el.style.boxShadow='0 6px 22px rgba(0,0,0,.35)';
    document.body.appendChild(el); setTimeout(()=> el.remove(), 2600);
  }

  document.getElementById('savePushcutBtn')?.addEventListener('click', async ()=>{
    const url = document.getElementById('pushcutUrl').value.trim();
    if(!url){ toast('Informe a URL do Pushcut', 'error'); return; }
    const fd = new FormData(); fd.append('pushcut_url', url);
    try{
      const r = await fetch('save_pushcut.php', {method:'POST', body: fd});
      const j = await r.json().catch(()=>({}));
      if(j.ok){ toast('Pushcut salvo!', 'success'); } else { toast('Erro ao salvar: ' + (j.error||'desconhecido'), 'error'); }
    }catch(e){ toast('Erro: '+e.message, 'error'); }
  });

  document.getElementById('testPushcutBtn')?.addEventListener('click', async ()=>{
    try{
      const r = await fetch('send_pushcut_test.php', { method:'POST' });
      const j = await r.json().catch(()=>({}));
      if(j.ok){ toast('Teste enviado!', 'success'); } else { toast('Falha no teste' + (j.http ? ' ('+j.http+')' : ''), 'error'); }
    }catch(e){ toast('Erro: '+e.message, 'error'); }
  });

  document.getElementById('syncPushcutNow')?.addEventListener('click', async ()=>{
    try{
      const r = await fetch('check_and_notify.php');
      const j = await r.json().catch(()=>({}));
      if(j.ok){ toast('Notificadas: ' + j.notified, 'success'); }
      else{ toast('Erro: ' + (j.error||'falha'), 'error'); }
    }catch(e){ toast('Erro: '+e.message, 'error'); }
  });

  // Auto-sync a cada 10s (pausa quando aba não está visível)
  let inFlight=false;
  async function autoSync(){
    if(inFlight || document.hidden) return;
    inFlight = true;
    try{ await fetch('check_and_notify.php'); }catch(e){/*silencioso*/}
    inFlight = false;
  }
  setInterval(autoSync, 10000);
  document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) autoSync(); });
})();
</script>

</body>
</html>
