<?php

include "config/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check empty fields
    if (empty($name) || empty($email) || empty($phone) ||
        empty($password) || empty($confirm_password)) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    // Validate email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    // Validate phone number
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $message = "Phone number must contain exactly 10 digits.";
        $message_type = "error";

    // Check password length
    } elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $message_type = "error";

    // Check password confirmation
    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    } else {

        // Check whether email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();

        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {

            $message = "This email is already registered.";
            $message_type = "error";

        } else {

            // Securely hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $insert_sql = "INSERT INTO users
                           (name, email, phone, password)
                           VALUES (?, ?, ?, ?)";

            $insert_stmt = $conn->prepare($insert_sql);

            $insert_stmt->bind_param(
                "ssss",
                $name,
                $email,
                $phone,
                $hashed_password
            );

            if ($insert_stmt->execute()) {

                $message = "Registration successful! You can now login.";
                $message_type = "success";

                // Clear form values
                $name = "";
                $email = "";
                $phone = "";

            } else {

                $message = "Registration failed. Please try again.";
                $message_type = "error";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register - SlotSync</title>

    <link rel="stylesheet"
          href="css/style.css">

    <style>

        body {
            background: #eef6f9;
        }

        .register-container {
            min-height: calc(100vh - 75px);

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 40px 20px;
        }

        .register-box {
            width: 450px;

            background: white;

            padding: 35px;

            border-radius: 15px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .register-box h1 {
            text-align: center;

            color: #0f172a;

            margin-bottom: 10px;
        }

        .register-box .subtitle {
            text-align: center;

            color: #666;

            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;

            margin-bottom: 7px;

            color: #263238;

            font-weight: bold;
        }

        .form-group input {
            width: 100%;

            padding: 12px;

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

        .register-btn {
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

        .register-btn:hover {
            background: #0f4f59;
        }

        .message {
            padding: 12px;

            border-radius: 7px;

            margin-bottom: 20px;

            text-align: center;
        }

        .success {
            background: #d1fae5;

            color: #065f46;
        }

        .error {
            background: #fee2e2;

            color: #991b1b;
        }

        .login-link {
            text-align: center;

            margin-top: 20px;

            color: #555;
        }

        .login-link a {
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

            <a href="#">
                Rooms
            </a>

            <a href="#">
                Login
            </a>

        </nav>

    </header>


    <!-- REGISTRATION -->

    <div class="register-container">

        <div class="register-box">

            <h1>Create Account</h1>

            <p class="subtitle">
                Register to book your perfect space
            </p>


            <?php if (!empty($message)): ?>

                <div class="message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST"
                  action="register.php">

                <!-- Name -->

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($name ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Email -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Phone -->

                <div class="form-group">

                    <label for="phone">
                        Mobile Number
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        placeholder="Enter 10-digit mobile number"
                        maxlength="10"
                        value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Password -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minimum 6 characters"
                        required
                    >

                </div>


                <!-- Confirm Password -->

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter your password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="register-btn">

                    Create Account

                </button>

            </form>


            <div class="login-link">

                Already have an account?

                <a href="#">
                    Login here
                </a>

            </div>

        </div>

    </div>

</body>

</html>