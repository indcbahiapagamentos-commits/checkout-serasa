<?php

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$file = 'pixels2.json';


// Verifica se o arquivo JSON existe, se não, cria um vazio
if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

// Função para ler o arquivo JSON
function readPixels($file) {
    $content = file_get_contents($file);
    return json_decode($content, true) ?? [];
}

// Função para salvar no arquivo JSON
function savePixels($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Processa requisições POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pixels = readPixels($file);

    // Adicionar novo pixel
    if (isset($_POST['addPixel'])) {
        $newPixel = trim($_POST['newPixel']);
        if (!empty($newPixel)) {
            $pixels[] = [
                "id" => count($pixels) + 1,
                "pixel" => $newPixel
            ];
            savePixels($file, $pixels);
        }
    }

    // Excluir pixel
    if (isset($_POST['deletePixel'])) {
        $id = intval($_POST['pixelId']);
        $pixels = array_filter($pixels, fn($pixel) => $pixel['id'] !== $id);
        savePixels($file, array_values($pixels));
    }

    // Atualizar pixel
    if (isset($_POST['updatePixel'])) {
        $id = intval($_POST['editId']);
        $newValue = trim($_POST['editPixel']);
        foreach ($pixels as &$pixel) {
            if ($pixel['id'] === $id) {
                $pixel['pixel'] = $newValue;
                break;
            }
        }
        savePixels($file, $pixels);
    }

    header('Location: pixels2.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Pixels de Conversão</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

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
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="cpdashboard.php">Gateway</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="pixels.php">Pixel</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link active" href="pixels2.php">Pixel Conversão</a>
                </li>
                
                                <li class="nav-item">
                    <a class="nav-link active" href="cptransacoes.php">Transacoes</a>
                </li>
                                                <li class="nav-item">
                    <a class="nav-link active" href="logout.php">Sair do Painel</a>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Gerenciamento de Pixels de Conversão</h2>

    <!-- Formulário para adicionar pixel -->
    <form method="POST" action="pixels2.php">
        <div class="mb-3">
            <label for="newPixel" class="form-label">Adicionar Pixel:</label>
            <input type="text" class="form-control" id="newPixel" name="newPixel" required>
        </div>
        <button type="submit" class="btn btn-primary" name="addPixel">Adicionar</button>
    </form>

    <hr>

    <!-- Listar pixels -->
    <h3>Lista de Pixels</h3>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Pixel</th>
            <th>Ação</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $pixels = readPixels($file);

        if (count($pixels) > 0) {
            foreach ($pixels as $pixel) {
                echo "<tr>
                    <td>{$pixel['id']}</td>
                    <td>{$pixel['pixel']}</td>
                    <td>
                        <form method='POST' style='display:inline-block;'>
                            <input type='hidden' name='pixelId' value='{$pixel['id']}'>
                            <button type='submit' class='btn btn-danger' name='deletePixel'>Excluir</button>
                        </form>
                        <button class='btn btn-warning' onclick='editPixel({$pixel['id']}, \"{$pixel['pixel']}\")'>Editar</button>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Nenhum pixel encontrado.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Modal para editar pixel -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Pixel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="pixels2.php">
                <div class="modal-body">
                    <input type="hidden" id="editId" name="editId">
                    <div class="mb-3">
                        <label for="editPixel" class="form-label">Novo Valor:</label>
                        <input type="text" class="form-control" id="editPixel" name="editPixel" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="updatePixel">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editPixel(id, value) {
        document.getElementById('editId').value = id;
        document.getElementById('editPixel').value = value;
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
</body>
</html>