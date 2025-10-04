<?php
session_start();

$default_username = 'nascimento171';
$default_password = 'senhasenha';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $default_username && $password === $default_password) {
        // Login válido, cria a sessão
        $_SESSION['logged_in'] = true;
        header('Location: cpdashboard.php');
        exit;
    } else {
        $error = "Login ou senha inválidos!";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center">Login</h3>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
</div>

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