<?php
require 'config/database.php';

// Make sure edit post button was clicked
if (isset($_POST['submit'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $previous_thumbnail_name = filter_var($_POST['previous_thumbnail_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // Allow <b> and <strong> tags in the body
    $body = strip_tags($_POST['body'], '<b><strong><p><br>');
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = isset($_POST['is_featured']) ? filter_var($_POST['is_featured'], FILTER_SANITIZE_NUMBER_INT) : 0;
    $thumbnail = $_FILES['thumbnail'];

    // Validate input values
    if (!$title) {
        $_SESSION['edit-post'] = "Title is required.";
    } elseif (!$category_id) {
        $_SESSION['edit-post'] = "Category is required.";
    } elseif (!$body) {
        $_SESSION['edit-post'] = "Body is required.";
    } else {
        // Handle thumbnail upload if a new one is provided
        if ($thumbnail['name']) {
            // Delete previous thumbnail if exists
            $previous_thumbnail_path = '../images/' . $previous_thumbnail_name;
            if ($previous_thumbnail_name && file_exists($previous_thumbnail_path)) {
                unlink($previous_thumbnail_path);
            }

            // Upload new thumbnail
            $time = time();
            $thumbnail_name = $time . '_' . $thumbnail['name'];
            $thumbnail_tmp_name = $thumbnail['tmp_name'];
            $thumbnail_destination_path = '../images/' . $thumbnail_name;

            // Move the uploaded file
            move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
        } else {
            // Keep the previous thumbnail if no new thumbnail is uploaded
            $thumbnail_name = $previous_thumbnail_name;
        }

        // Handle the `is_featured` logic
        if ($is_featured == 1) {
            $zero_all_is_featured_query = "UPDATE posts SET is_featured = 0";
            mysqli_query($connection, $zero_all_is_featured_query);
        }

        // Update the post in the database
        $query = "UPDATE posts SET 
                    title = '$title', 
                    body = '$body', 
                    thumbnail = '$thumbnail_name', 
                    category_id = $category_id, 
                    is_featured = $is_featured 
                  WHERE id = $id 
                  LIMIT 1";
        $result = mysqli_query($connection, $query);

        if ($result) {
            $_SESSION['edit-post-success'] = "Post updated successfully.";
            header('Location: ' . ROOT_URL . 'admin/index.php');
            exit();
        } else {
            $_SESSION['edit-post'] = "Failed to update post.";
        }
    }

    // Redirect back to the edit-post page with the same post ID if there is an error
    header('Location: ' . ROOT_URL . 'admin/edit-post.php?id=' . $id);
    exit();
} else {
    header('Location: ' . ROOT_URL . 'admin/index.php');
    exit();
}
?>