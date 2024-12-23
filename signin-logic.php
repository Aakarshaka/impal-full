<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    // Get form data
    $username_email = mysqli_real_escape_string($connection, trim($_POST['username_email']));
    $password = mysqli_real_escape_string($connection, trim($_POST['password']));

    if (!$username_email) {
        $_SESSION['signin'] = "Please enter your username or email";
    } elseif (!$password) {
        $_SESSION['signin'] = "Please enter your password";
    } else {
        // Fetch user from the database
        $fetch_user_query = "SELECT * FROM users WHERE username='$username_email' OR email='$username_email'";
        $fetch_user_result = mysqli_query($connection, $fetch_user_query);

        if (mysqli_num_rows($fetch_user_result) == 1) {
            // Convert result to associative array
            $user = mysqli_fetch_assoc($fetch_user_result);
            $db_password = $user['password'];

            // Compare database password with user input password
            if (password_verify($password, $db_password)) {
                // Set session for access control
                $_SESSION['user-id'] = $user['id'];

                // Set session if user is admin
                if ($user['is_admin'] == 1) {
                    $_SESSION['user_is_admin'] = true;
                }

                // Log user in
                header('location: ' . ROOT_URL . 'admin/');
                die();
            } else {
                $_SESSION['signin'] = "Incorrect password";
            }
        } else {
            $_SESSION['signin'] = "User not found";
        }
    }

    // If there is an error, redirect to signin page with login data
    if (isset($_SESSION['signin'])) {
        $_SESSION['signin-data'] = $_POST;
        header('location: ' . ROOT_URL . 'signin.php');
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}
