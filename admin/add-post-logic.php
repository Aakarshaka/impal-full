<?php
require 'config/database.php';

if(isset($_POST['submit'])) {
    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // Allow <b> and <strong> tags in the body
    $body = strip_tags($_POST['body'], '<b><strong><p><br>');
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = filter_var($_POST['is_featured'], FILTER_SANITIZE_NUMBER_INT);
    $thumbnail = $_FILES['thumbnail'];

    // Set is_featured to 0 if not checked
    $is_featured = $is_featured == 1 ? 1 : 0;

    // Check if all fields are filled
    if(!$title) {
        $_SESSION['add-post'] = "Please enter a title";
    } elseif(!$category_id) {
        $_SESSION['add-post'] = "Please select a category";
    } elseif(!$body) {
        $_SESSION['add-post'] = "Please enter a post body";
    } elseif (!$thumbnail['name']) {
        $_SESSION['add-post'] = "Please upload a thumbnail";
    } else {
        if($is_featured) {
            // Unset is_featured for all other posts
            $unset_featured_query = "UPDATE posts SET is_featured = 0 WHERE is_featured = 1";
            mysqli_query($connection, $unset_featured_query);
        }

        // Rename the image file
        $time = time(); // Make the thumbnail name unique
        $thumbnail_name = $time . '_' . $thumbnail['name'];
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = '../images/' . $thumbnail_name;

        // Move the uploaded file
        move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);

        // Insert post into database
        $query = "INSERT INTO posts (title, body, thumbnail, category_id, author_id, is_featured) VALUES ('$title', '$body', '$thumbnail_name', $category_id, $author_id, $is_featured)";
        $result = mysqli_query($connection, $query);

        if($result) {
            // Redirect to admin index page
            $_SESSION['add-post-success'] = "New post added successfully";
            header('Location: ' . ROOT_URL . 'admin/index.php');
            exit();
        } else {
            $_SESSION['add-post'] = "Failed to add post";
        }
    }

    // Redirect back to add-post page if there is an error
    header('Location: ' . ROOT_URL . 'admin/add-post.php');
    exit();
}
?>