<?php

session_start();

include "config/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } else {

        $sql = "SELECT id, name, email, password
                FROM users
                WHERE email = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Incorrect password.";
                $message_type = "error";
            }

        } else {

            $message = "No account found with this email.";
            $message_type = "error";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - BookMySapce</title>

    <link rel="stylesheet"
          href="css/style.css">

    <style>

        body {
            background: #eef6f9;
        }

        .login-container {

            min-height: calc(100vh - 75px);

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 40px 20px;
        }

        .login-box {

            width: 420px;

            background: white;

            padding: 35px;

            border-radius: 15px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .login-box h1 {

            text-align: center;

            color: #0f172a;

            margin-bottom: 10px;
        }

        .subtitle {

            text-align: center;

            color: #666;

            margin-bottom: 25px;
        }

        .form-group {

            margin-bottom: 20px;
        }

        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            color: #263238;
        }

        .form-group input {

            width: 100%;

            padding: 13px;

            border: 1px solid #ccd6dd;

            border-radius: 7px;

            font-size: 15px;

            outline: none;
        }

        .form-group input:focus {

            border-color: #15616d;

            box-shadow:
                0 0 5px rgba(21, 97, 109, 0.2);
        }

        .login-btn {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 7px;

            background: #15616d;

            color: white;

            font-size: 17px;

            font-weight: bold;

            cursor: pointer;
        }

        .login-btn:hover {

            background: #0f4f59;
        }

        .message {

            padding: 12px;

            border-radius: 7px;

            margin-bottom: 20px;

            text-align: center;
        }

        .error {

            background: #fee2e2;

            color: #991b1b;
        }

        .register-link {

            text-align: center;

            margin-top: 20px;

            color: #555;
        }

        .register-link a {

            color: #15616d;

            text-decoration: none;

            font-weight: bold;
        }

    </style>

</head>

<body>

    <!-- HEADER -->

    <header>

        <div class="logo">
            BookMySapce
        </div>

        <nav>

            <a href="index.php">
                Home
            </a>

            <a href="register.php">
                Register
            </a>

        </nav>

    </header>


    <!-- LOGIN -->

    <div class="login-container">

        <div class="login-box">

            <h1>Welcome Back</h1>

            <p class="subtitle">
                Login to your BookMySpace account
            </p>


            <?php if (!empty($message)): ?>

                <div class="message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST"
                  action="login.php">

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="login-btn">

                    Login

                </button>

            </form>


            <div class="register-link">

                Don't have an account?

                <a href="register.php">
                    Register here
                </a>

            </div>

        </div>

    </div>

</body>

</html>