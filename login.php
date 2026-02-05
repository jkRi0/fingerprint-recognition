<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    // For demonstration, use hardcoded credentials. Replace with DB logic in production.
    $valid_user = 'admin';
    $valid_pass = 'admin123';
    if ($username === $valid_user && $password === $valid_pass) {
        $_SESSION['user'] = $username;
        header('Location: biometric_register.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login - Fingerprint Recognition</title>
	<link rel="stylesheet" href="css/bootstrap-min.css">
	<link rel="stylesheet" href="app.css">
</head>
<body>
	<div class="app-root">
		<div class="app-card shadow-sm">
			<header class="app-header">
				<h1 class="app-title">Login</h1>
				<p class="app-subtitle">Sign in to manage fingerprint recognition.</p>
			</header>
			<main class="app-main">
				<div class="capture-status">
			<?php if ($error): ?>
				<div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>
				<form method="post" autocomplete="off">
					<div class="form-group">
						<label for="username">Username</label>
						<input type="text" name="username" id="username" class="form-control" required autofocus>
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" name="password" id="password" class="form-control" required>
					</div>
					<button type="submit" class="btn btn-primary" style="margin-top:10px;">Login</button>
				</form>
				<a href="index.html" class="btn btn-ghost" style="margin-top:12px;">Back to Main Page</a>
				</div>
			</main>
		</div>
	</div>
	</body>
	</html>
