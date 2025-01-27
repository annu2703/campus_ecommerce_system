<?php
session_start();
include 'config.php'; // Ensure this path is correct

if (isset($_POST['login'])) {
    $matrics_number = $_POST['matrics_number'];
    $password = $_POST['password'];
    $user_role = $_POST['user_role'];

    // Update the SQL statement to use matrics_number and user_role
    $sql = "SELECT id, username, password, user_role, matrics_number FROM users WHERE matrics_number = ? AND user_role = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ss", $matrics_number, $user_role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Directly compare the password from the form with the password from the database
            if ($password === $user['password']) {
                // Login Success
                $_SESSION['matrics_number'] = $user['matrics_number'];
                $_SESSION['username'] = $user['username']; // Store the username in the session
                $_SESSION['user_role'] = $user['user_role']; // Store the user's role in the session
                $_SESSION['user_id'] = $user['id']; // Store the user ID in the session

                // Redirect based on the role
                if ($user['user_role'] == 'admin') {
                    header("Location: adminDashboard.php"); // Redirect to the admin dashboard
                } else if ($user['user_role'] == 'seller') {
                    header("Location: sellerDashboard.php");
                } else {
                    header("Location: buyerDashboard.php"); // Redirect to the regular user dashboard
                }
                exit(); // Don't forget to exit after redirection
            } else {
                // Password is incorrect
                $login_err = "Incorrect matrics number or password.";
            }
        } else {
            // Matrics number and role combination doesn't exist
            $login_err = "Incorrect matrics number or password.";
        }

        $stmt->close();
    }
}

if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $matrics_number = $_POST['matrics_number'];
    $password = $_POST['password'];
    $user_role = $_POST['user_role']; 

    // Check if the user with the same matrics number and role already exists
    $sql = "SELECT * FROM users WHERE matrics_number = ? AND user_role = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ss", $matrics_number, $user_role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Matrics number and role combination already exists
            $signup_err = "Matrics number already exists for this role.";
        } else {
            // Insert new user
            $sql = "INSERT INTO users (username, matrics_number, password, user_role) VALUES (?, ?, ?, ?)";
            if ($stmt = $link->prepare($sql)) {
                $stmt->bind_param("ssss", $username, $matrics_number, $password, $user_role);
                if ($stmt->execute()) {
                    $signup_success = "Account created successfully. You can now sign in.";
                } else {
                    $signup_err = "Error creating account. Please try again.";
                }
            }
        }
        $stmt->close();
    }
    $link->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="" method="post">
                <h1>Create Account</h1>
                <input type="text" name="username" placeholder="Username" required />
                <input type="text" name="matrics_number" placeholder="Matrics Number" required />
                <input type="password" name="password" placeholder="Password" required />
                <select name="user_role" required>
                    <option value="" disabled selected>Select Role</option>
                    <!-- <option value="buyer">Buyer</option> -->
                    <option value="seller">Seller</option>
                </select>
                <button type="submit" name="signup">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form action="" method="post">
                <h1>Sign in</h1>
                <input type="text" name="matrics_number" required placeholder="Matrics Number" />
                <input type="password" name="password" required placeholder="Password" />
                <select name="user_role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please login with your personal info</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Hello, UNIMASIAN!</h1>
                    <p>This section is only for students who would like to get registered as 'SELLERS' </p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>
    <?php if (!empty($login_err)): ?>
        <script>
            window.onload = function () {
                alert("<?php echo addslashes($login_err); ?>");
            };
        </script>
    <?php endif; ?>
    <?php if (!empty($signup_err)): ?>
        <script>
            window.onload = function () {
                alert("<?php echo addslashes($signup_err); ?>");
            };
        </script>
    <?php endif; ?>
    <?php if (!empty($signup_success)): ?>
        <script>
            window.onload = function () {
                alert("<?php echo addslashes($signup_success); ?>");
            };
        </script>
    <?php endif; ?>
</body>
</html>
