<?php
require 'config/database.php';

//get form data if submit button was clicked
if (isset($_POST['submit'])) {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $is_admin = filter_var($_POST['userrole'], FILTER_SANITIZE_NUMBER_INT);
    $avatar = $_FILES['avatar'];
    

    //validate input data
    if(!$firstname){
        $_SESSION['add-user'] = 'Please enter your first name';
    } elseif (!$lastname) {
        $_SESSION['add-user'] = 'Please enter your last name';
    } elseif (!$username) {
        $_SESSION['add-user'] = 'Please enter your username';
    } elseif (!$email) {
        $_SESSION['add-user'] = 'Please enter your a valid email';
    } elseif (strlen($createpassword) < 8 || strlen($confirmpassword) < 8) {
        $_SESSION['add-user'] = 'Password should be at least 8 characters';
    } elseif (!$avatar['name']) {
        $_SESSION['add-user'] = 'Please add an avatar';
    } else {
        //check if passwords dont match
        if($createpassword !== $confirmpassword){
            $_SESSION['signup'] = 'Passwords do not match';
        } else {
            //hash password
            $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);
            
            //check if email already exists and username already exists in the database
            $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
            $user_check_result = mysqli_query($connection, $user_check_query);
            if ($existing_user['username'] === $username) {
                $_SESSION['signup'] = 'Username already exists';
            } elseif ($existing_user['email'] === $email) {
                $_SESSION['signup'] = 'Email already exists';
            } else {
                // work on the avatar
                // rename the avatar file
                $time = time(); // get the current time to rename the file so it'll be unique
                $avatar_name = $time . $avatar['name'];
                $avatar_tmp_name = $avatar['tmp_name'];
                $avatar_destination_path = '../images/' . $avatar_name;

                // make sure the file is an image
                $allowed_files = ['jpg', 'jpeg', 'png'];
                $extention = explode('.', $avatar_name);
                $extention = end($extention);
                if(in_array($extention, $allowed_files)){
                    // make sure the image is not too large (2mb+)
                    if($avatar['size'] < 2000000){
                        //upload the image
                        move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                    } else {
                        $_SESSION['add-user'] = 'Image is too large. Maximum size is 2mb';
                    }
                } else {
                    $_SESSION['add-user'] = 'Please upload a valid image file (jpg, jpeg, or png)';
                }
            }
        }
    }

    //redirect to the add-user page if there are errors
    if(isset($_SESSION['add-user'])){
        //pass the form data to the add-user page
        $_SESSION['add-user-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/add-user.php');
        die();
    } else {
        //insert the user into the database
        $insert_user_query = "INSERT INTO users SET firstname='$firstname', lastname='$lastname', username='$username', 
        email='$email', password='$hashed_password', avatar='$avatar_name', is_admin=$is_admin";
        $insert_user_result = mysqli_query($connection, $insert_user_query);

        if(!mysqli_errno($connection)){
            // redirect to the signin page
            $_SESSION['add-user-success'] = "New user $firstname $lastname added successfully";
            header('location: ' . ROOT_URL . 'admin/manage-users.php');
            die();
        }
    }
} else  {
    //if the signup button was not clicked, redirect to the signup page
    header('location: ' . ROOT_URL . 'admin/add-user.php');
    die();
}