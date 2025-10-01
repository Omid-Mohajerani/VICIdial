<?php
/* ------------------ CONFIG (adjust if needed) ------------------ */
$DB_HOST = "localhost";
$DB_NAME = "asterisk";
$DB_USER = "cron";      // use your VARDB_user
$DB_PASS = "1234";      // use your VARDB_pass
$LIST_ID = "ViciWhite"; // target list id

$remoteip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$msg = null; $msgType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $pass = trim($_POST['pass'] ?? '');

    try {
        $pdo = new PDO(
            "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
            $DB_USER, $DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Login check (plaintext 'pass' column; if you use pass_hash, ask me to adapt)
        $stmt = $pdo->prepare(
            "SELECT user FROM vicidial_users
             WHERE user = :user AND pass = :pass AND active='Y'
             LIMIT 1"
        );
        $stmt->execute([':user'=>$user, ':pass'=>$pass]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Check if IP already whitelisted
            $check = $pdo->prepare(
                "SELECT 1 FROM vicidial_ip_list_entries
                 WHERE ip_list_id = :list AND ip_address = :ip
                 LIMIT 1"
            );
            $check->execute([':list'=>$LIST_ID, ':ip'=>$remoteip]);

            if ($check->fetch()) {
                $msgType = 'info';
                $msg = "Your IP <strong>$remoteip</strong> is already whitelisted in <strong>$LIST_ID</strong>.";
            } else {
                // Insert new whitelist entry
                $ins = $pdo->prepare(
                    "INSERT INTO vicidial_ip_list_entries (ip_list_id, ip_address)
                     VALUES (:list, :ip)"
                );
                $ins->execute([':list'=>$LIST_ID, ':ip'=>$remoteip]);

                // Optional: force quick firewall sync
                @shell_exec('/usr/bin/VB-firewall --white --dynamic --quiet > /dev/null 2>&1');

                $msgType = 'success';
                $msg = "Login OK. Your IP <strong>$remoteip</strong> was added to <strong>$LIST_ID</strong>.";
            }
        } else {
            $msgType = 'error';
            $msg = "Login incorrect, or your account is inactive.";
        }
    } catch (Throwable $e) {
        $msgType = 'error';
        $msg = "Database error: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Whitelist Login</title>
<style>
  :root {
    --bg: #0f172a; --card: #0b1220; --muted: #94a3b8; --fg: #e5e7eb;
    --primary:#3b82f6; --primary-hover:#2563eb; --ring: rgba(59,130,246,.35);
    --success-bg:#052e16; --success-br:#16a34a;
    --error-bg:#3f1d1d; --error-br:#ef4444;
    --info-bg:#1e293b; --info-br:#38bdf8;
  }
  *{box-sizing:border-box} html,body{height:100%}
  body{
    margin:0; background: radial-gradient(1200px 800px at 20% -10%, #1f2937 0, transparent 60%),
                      radial-gradient(1200px 800px at 120% 110%, #111827 0, transparent 60%), var(--bg);
    color:var(--fg); font:16px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica Neue,Arial;
    display:grid; place-items:center; padding:24px;
  }
  .card{
    width:100%; max-width:480px; background:linear-gradient(180deg,#0b1220 0%,#0a0f1a 100%);
    border:1px solid rgba(148,163,184,.12); border-radius:16px; box-shadow:0 20px 50px rgba(0,0,0,.45);
    padding:28px;
  }
  .brand{display:flex;align-items:center;gap:12px;margin-bottom:8px}
  .brand .dot{width:10px;height:10px;border-radius:9999px;background:var(--primary);
              box-shadow:0 0 0 6px rgba(59,130,246,.15)}
  h1{font-size:20px;margin:0 0 6px} p.sub{margin:0 0 18px;color:var(--muted);font-size:14px}
  .alert{padding:12px 14px;border-radius:10px;margin-bottom:16px;border:1px solid transparent}
  .alert.success{background:var(--success-bg);border-color:var(--success-br)}
  .alert.error{background:var(--error-bg);border-color:var(--error-br)}
  .alert.info{background:var(--info-bg);border-color:var(--info-br)}
  form{display:grid;gap:12px}
  label{font-size:13px;color:var(--muted)}
  input[type="text"],input[type="password"]{
    width:100%;padding:12px 14px;border-radius:10px;border:1px solid rgba(148,163,184,.18);
    background:#0b1220;color:var(--fg);outline:none
  }
  input[type="text"]:focus,input[type="password"]:focus{border-color:var(--primary);box-shadow:0 0 0 4px var(--ring)}
  .row{display:grid;gap:6px}
  .actions{margin-top:6px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .btn{padding:10px 14px;border-radius:10px;border:1px solid transparent;background:var(--primary);color:#fff;font-weight:600;cursor:pointer}
  .btn:hover{background:var(--primary-hover)}
  .meta{font-size:12px;color:var(--muted);margin-top:14px;display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap}
  .badge{padding:2px 8px;border-radius:9999px;border:1px solid rgba(148,163,184,.25);color:var(--muted)}
</style>
</head>
<body>
  <div class="card" role="region" aria-label="Whitelist Login">
    <div class="brand">
      <div class="dot" aria-hidden="true"></div>
      <h1>Whitelist Login</h1>
    </div>
    <p class="sub">Sign in with your VICIdial username and password to add your current IP to the whitelist.</p>

    <?php if ($msg): ?>
      <div class="alert <?= htmlspecialchars($msgType) ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="row">
        <label for="user">Username</label>
        <input id="user" name="user" type="text" required autofocus>
      </div>
      <div class="row">
        <label for="pass">Password</label>
        <input id="pass" name="pass" type="password" required>
      </div>
      <div class="actions">
        <button class="btn" type="submit">Login & Add to Whitelist</button>
        <span class="badge">Your IP: <?= htmlspecialchars($remoteip) ?></span>
      </div>
    </form>

    <div class="meta">
      <span>List: <strong><?= htmlspecialchars($LIST_ID) ?></strong></span>
      <span>Use HTTPS for security</span>
    </div>
  </div>
</body>
</html>

