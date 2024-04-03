<?php
    // Connect to database

    $db_server = 'localhost';
    $db_user = 'root';
    $db_password = '';
    $db_database = 'tutorials_DB';
    $db_port = 3390;

    // $db_server = 'if0_36298783_login_db';
    // $db_user = 'if0_36298783';
    // $db_password = 'DL0heKFdOIQFY';
    // $db_database = 'login_db';
    // $db_port = 3390;

    // Create connection
    $conn = mysqli_connect($db_server, $db_user, $db_password, $db_database, $db_port);

    // Check connection
    if (!$conn) {
        echo "Connection error: " . mysqli_connect_error();
    }

    // Create all tables

    // USERS TABLE
    $sql1 = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(20),
        email VARCHAR(255),
        password_hash VARCHAR(255)
        )";

    // PROFILE_IMAGES TABLE
    $sql2 = "CREATE TABLE IF NOT EXISTS profile_images (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6),
        image_reference VARCHAR(255)
        )";
    

    // LEARNING_PATHS TABLE
    $sql3 = "CREATE TABLE IF NOT EXISTS learning_paths (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        created_by INT(6),
        title VARCHAR(255),
        description VARCHAR(255)
        )";

    // STEPS TABLE
    $sql4 = "CREATE TABLE IF NOT EXISTS steps (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        learning_path_id INT(6),
        step_number INT(6),
        title VARCHAR(255),
        description VARCHAR(255)
        )";

    // RESOURCES TABLE
    $sql5 = "CREATE TABLE IF NOT EXISTS resources (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        steps_id INT(6),
        learning_path_id INT(6),
        url VARCHAR(255)
        )";

    // // COMMENTS TABLE
    // $sql6 = "CREATE TABLE IF NOT EXISTS comments (
    //     id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    //     user_id INT(6),
    //     learning_path_id INT(6),
    //     comment_text TEXT
    //     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    //     )";

    // $sql7 = "CREATE TABLE IF NOT EXISTS votes (
    //     id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    //     user_id INT(6),
    //     learning_path_id INT(6),
    //     vote_value ENUM('upvote', 'downvote')
    //     )";

    // Execute the queries
    if ($conn->query($sql1) !== TRUE) {
        echo "Error creating users table: " . $conn->error;
    }

    if ($conn->query($sql2) !== TRUE) {
        echo "Error creating profile_images table: " . $conn->error;
    }

    if ($conn->query($sql3) !== TRUE) {
        echo "Error creating learning_paths table: " . $conn->error;
    }

    if ($conn->query($sql4) !== TRUE) {
        echo "Error creating steps table: " . $conn->error;
    }

    if ($conn->query($sql5) !== TRUE) {
        echo "Error creating resources table: " . $conn->error;
    }

    // if ($conn->query($sql6) !== TRUE) {
    //     echo "Error creating comments table: " . $conn->error;
    // }

    // if ($conn->query($sql7) !== TRUE) {
    //     echo "Error creating votes table: " . $conn->error;
    // }
?>
