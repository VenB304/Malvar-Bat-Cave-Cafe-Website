<?php
session_start();
require_once 'includes/json_handler.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

$userHandler = new JsonHandler('data/users.json');
$users = $userHandler->read();
$isFirstBoot = empty($users);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($isFirstBoot) {
        // Registration Logic
        if (!empty($username) && !empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $newUser = [
                'id' => uniqid(),
                'username' => $username,
                'password' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $userHandler->append($newUser);

            // Auto-login
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            header("Location: admin.php");
            exit;
        } else {
            $error = "Username and password are required.";
        }
    } else {
        // Login Logic
        $foundUser = null;
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $foundUser = $user;
                break;
            }
        }

        if ($foundUser && password_verify($password, $foundUser['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $foundUser['username'];
            header("Location: admin.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isFirstBoot ? 'Admin Registration' : 'Admin Login'; ?> - Malvar Bat Cave Cafe</title>
    <link rel="stylesheet" href="css/vendor.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .error-msg {
            color: var(--color-error);
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="section-header">
            <h2 class="text-display-title"><?php echo $isFirstBoot ? 'Admin Setup' : 'Admin Login'; ?></h2>
            <?php if ($isFirstBoot): ?>
                <p class="lead">Welcome! Please create an admin account to get started.</p>
            <?php else: ?>
                <p class="lead">Please sign in to access the dashboard.</p>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="u-full-width" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="u-full-width" required>
            </div>
            <button type="submit" class="btn btn--primary u-full-width">
                <?php echo $isFirstBoot ? 'Create Account' : 'Sign In'; ?>
            </button>
        </form>

        <div style="margin-top: 2rem;">
            <a href="index.php">Back to Home</a>
        </div>
    </div>

</body>

</html>