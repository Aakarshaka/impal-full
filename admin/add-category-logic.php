<?php
require 'config/database.php';

if(isset($_POST['submit'])) {
    // Get the form data
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if(!$title) {
        $_SESSION['add-category'] = "Title is required";
    } elseif (!$description) {
        $_SESSION['add-category'] = "Description is required";
    } 

    // Redirect to the add-category page with form data if there is an error
    if(isset($_SESSION['add-category'])) {
        $_SESSION['add-category-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/add-category.php');
        die();
    } else {
        // Insert the category into the database
        $query = "INSERT INTO categories (title, description) VALUES ('$title', '$description')";
        $result = mysqli_query($connection, $query);
        if(mysqli_errno($connection)){
            $_SESSION['add-category'] = "An error occurred while adding the category: ";
            header('location: ' . ROOT_URL . 'admin/add-category.php');
            die();
        } else {
            // Redirect to the manage categories page upon success
            $_SESSION['add-category-success'] = "Category $title added successfully";
            header('location: ' . ROOT_URL . 'admin/manage-categories.php');
            die();
        }
    }
}
?>