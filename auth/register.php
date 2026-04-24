<?php
require_once __DIR__ . '/../includes/functions.php';
require_guest(); // redirect if already logged in

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $first_name = sanitize(post('first_name'));
    $last_name  = sanitize(post('last_name'));
    $username   = sanitize(post('username'));
    $email      = sanitize(post('email'));
    $phone      = sanitize(post('phone'));
    $address    = sanitize(post('address'));
    $password   = post('password');
    $confirm    = post('confirm_password');

    // Validation
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name))  $errors[] = "Last name is required.";
    if (empty($username))   $errors[] = "Username is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (strlen($password) < 8)   $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirm)  $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // Check duplicate username / email
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' OR email='$email' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $errors[] = "Username or email already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql  = "INSERT INTO users (first_name, last_name, username, email, phone, address, password_hash)
                     VALUES ('$first_name','$last_name','$username','$email','$phone','$address','$hash')";

            if (mysqli_query($conn, $sql)) {
                $new_id = mysqli_insert_id($conn);
                $_SESSION['user_id']   = $new_id;
                $_SESSION['username']  = $username;
                set_flash('success', "Welcome to Terra Store, $first_name! 🎉");
                header("Location: /index.php");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Terra Store</title>
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
            <h1 class="panel-tagline">Shop the world, <em>naturally.</em></h1>
            <ul class="panel-perks">
                <li>✦ Curated products, genuine quality</li>
                <li>✦ Track every order in real-time</li>
                <li>✦ Leave reviews, shape the community</li>
            </ul>
        </div>
        <div class="panel-orb panel-orb--1"></div>
        <div class="panel-orb panel-orb--2"></div>
    </div>

    <!-- Form side -->
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-card__header">
                <h2>Create your account</h2>
                <p>Already have one? <a href="/auth/login.php">Sign in</a></p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="flash flash--error">
                    <?php foreach ($errors as $e): ?>
                        <p>&#9888; <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name"
                               value="<?= htmlspecialchars(post('first_name')) ?>"
                               placeholder="John" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name"
                               value="<?= htmlspecialchars(post('last_name')) ?>"
                               placeholder="Doe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="<?= htmlspecialchars(post('username')) ?>"
                           placeholder="johndoe123" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars(post('email')) ?>"
                           placeholder="john@example.com" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone <span class="optional">(optional)</span></label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= htmlspecialchars(post('phone')) ?>"
                           placeholder="+250 7XX XXX XXX">
                </div>

                <div class="form-group">
                    <label for="address">Delivery Address <span class="optional">(optional)</span></label>
                    <textarea id="address" name="address" rows="2"
                              placeholder="Street, City, District"><?= htmlspecialchars(post('address')) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-eye">
                            <input type="password" id="password" name="password"
                                   placeholder="Min. 8 characters" required>
                            <button type="button" class="eye-btn" data-target="password">&#128065;</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-eye">
                            <input type="password" id="confirm_password" name="confirm_password"
                                   placeholder="Repeat password" required>
                            <button type="button" class="eye-btn" data-target="confirm_password">&#128065;</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Create Account</button>
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