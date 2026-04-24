<?php
require_once __DIR__ . '/../includes/functions.php';
require_guest();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $identity = sanitize(post('identity')); // username OR email
    $password = post('password');

    if (empty($identity)) $errors[] = "Username or email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        $sql    = "SELECT * FROM users WHERE username='$identity' OR email='$identity' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $user   = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'] ?? 'customer';

            set_flash('success', "Welcome back, {$user['first_name']}!");

            // Redirect to intended page or home
            $redirect = $_SESSION['redirect_after_login'] ?? '/index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
            exit();
        } else {
            $errors[] = "Invalid credentials. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Terra Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
<div class="auth-wrapper">
    <!-- Left decorative panel -->
    <div class="auth-panel">
        <div class="panel-content">
            <a href="/index.php" class="brand">
                <span class="brand-icon">T</span>
                <span>Terra Store</span>
            </a>
            <h1 class="panel-tagline">Good to have <em>you back.</em></h1>
            <ul class="panel-perks">
                <li>✦ Your cart is waiting for you</li>
                <li>✦ Check order status anytime</li>
                <li>✦ Discover new arrivals today</li>
            </ul>
        </div>
        <div class="panel-orb panel-orb--1"></div>
        <div class="panel-orb panel-orb--2"></div>
    </div>

    <!-- Form side -->
    <div class="auth-form-side">
        <div class="auth-card auth-card--centered">
            <div class="auth-card__header">
                <h2>Sign in to Terra</h2>
                <p>Don't have an account? <a href="/auth/register.php">Create one</a></p>
            </div>

            <?= render_flash() ?>

            <?php if (!empty($errors)): ?>
                <div class="flash flash--error">
                    <?php foreach ($errors as $e): ?>
                        <p>&#9888; <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="identity">Username or Email</label>
                    <input type="text" id="identity" name="identity"
                           value="<?= htmlspecialchars(post('identity')) ?>"
                           placeholder="johndoe or john@example.com"
                           autofocus required>
                </div>

                <div class="form-group">
                    <label for="password">
                        Password
                        <a href="/auth/forgot_password.php" class="label-link">Forgot password?</a>
                    </label>
                    <div class="input-eye">
                        <input type="password" id="password" name="password"
                               placeholder="Your password" required>
                        <button type="button" class="eye-btn" data-target="password">&#128065;</button>
                    </div>
                </div>

                <label class="checkbox-label">
                    <input type="checkbox" name="remember">
                    <span>Keep me signed in</span>
                </label>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.eye-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.textContent = input.type === 'password' ? '👁' : '🙈';
    });
});
</script>
</body>
</html>