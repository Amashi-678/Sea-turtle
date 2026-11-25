
<?php
session_start();

require_once "../config/database.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['admin_csrf'], $token)) {
        $error = "Invalid request. Please refresh the page.";
    } else {

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = "Please enter your email and password.";
        } else {

            $stmt = $conn->prepare(
                "SELECT admin_id, username, email, password
                 FROM admins WHERE email = ? LIMIT 1"
            );

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($admin = $result->fetch_assoc()) {

                if (password_verify($password, $admin['password'])) {

                    session_regenerate_id(true);

                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];

                    unset($_SESSION['admin_csrf']);

                    header("Location: dashboard.php");
                    exit;

                } else {
                    $error = "Invalid email or password.";
                }

            } else {
                $error = "Invalid email or password.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Sea Turtle</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eaf4ef;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-box {
            background: white;
            width: 350px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px #0002;
        }

        h2 {
            text-align: center;
            color: #176b50;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0 18px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #176b50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .error {
            color: #c0392b;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="login-box">

    <h2>Admin Login</h2>

    <p class="error">
        <?php echo htmlspecialchars($error); ?>
    </p>

    <form method="POST">

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars($_SESSION['admin_csrf']); ?>">

        <label>Email Address</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

    </form>

</div>

</body>
</html>
