<?php
session_start();
require_once '../includes/config.php';

// Already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in both fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login — UniCheck</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">

  <div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-logo">
        <div class="logo-icon">U</div>
        <div>
          <span class="logo-name">UniCheck</span>
          <span class="logo-sub">Admin Portal</span>
        </div>
      </div>

      <h2 class="auth-title">Sign In</h2>
      <p class="auth-sub">Enter your credentials to access the dashboard.</p>

      <?php if ($error): ?>
      <div class="alert alert-error" style="margin-bottom:20px">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= $error ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus/>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter password" required/>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px">
          Sign In to Dashboard
        </button>
      </form>

      <p class="auth-back"><a href="../index.php">← Back to Student Portal</a></p>
      <p class="auth-hint">Default: <code>admin</code> / <code>admin123</code></p>
    </div>
  </div>

</body>
</html>
