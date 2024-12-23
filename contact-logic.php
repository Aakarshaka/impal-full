<?php

require 'config/database.php';

// Check if the form was submitted
if (isset($_POST['submit'])) {
    // Retrieve and sanitize form inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Store form data in session in case of errors
    $_SESSION['contact-data'] = [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message
    ];

    // Validation
    if (empty($name)) {
        $_SESSION['contact'] = "Please enter your name.";
    } elseif (empty($email)) {
        $_SESSION['contact'] = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact'] = "Please enter a valid email address.";
    } elseif (empty($subject)) {
        $_SESSION['contact'] = "Please enter a subject.";
    } elseif (empty($message)) {
        $_SESSION['contact'] = "Please enter your message.";
    }

    // If there are validation errors, redirect back to contact page
    if (isset($_SESSION['contact'])) {
        header('Location: ' . ROOT_URL . 'contact.php');
        exit();
    } else {
        // Prepare and execute SQL statement to insert contact message
        $insert_query = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $insert_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                // Optional: Send a confirmation email to the user
                /*
                $to = $email;
                $email_subject = "Contact Form Submission: " . $subject;
                $email_body = "Hi $name,\n\nThank you for reaching out to us. We have received your message and will get back to you shortly.\n\nBest regards,\nSocial News Team";
                $headers = "From: no-reply@socialnews.com";

                mail($to, $email_subject, $email_body, $headers);
                */

                // Clear session contact data
                unset($_SESSION['contact-data']);

                // Set success message
                $_SESSION['contact-success'] = "Thank you for contacting us! We will get back to you shortly.";

                // Redirect to contact page
                header('Location: ' . ROOT_URL . 'contact.php');
                exit();
            } else {
                $_SESSION['contact'] = "Something went wrong. Please try again.";
                header('Location: ' . ROOT_URL . 'contact.php');
                exit();
            }
        } else {
            $_SESSION['contact'] = "Something went wrong. Please try again.";
            header('Location: ' . ROOT_URL . 'contact.php');
            exit();
        }
    }
} else {
    // If form was not submitted, redirect to contact page
    header('Location: ' . ROOT_URL . 'contact.php');
    exit();
}
?>