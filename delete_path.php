<?php
    include('config/db_connect.php');

    // DELETE logic
    if(isset($_POST['delete'])){
        $id_to_delete = mysqli_real_escape_string($conn, $_POST['id_to_delete']);

        // Delete associated comments first
        $sqlDeleteComments = "DELETE FROM comments WHERE learning_path_id=$id_to_delete";
        mysqli_query($conn, $sqlDeleteComments);

        // Delete associated votes
        $sqlDeleteVotes = "DELETE FROM votes WHERE learning_path_id=$id_to_delete";
        mysqli_query($conn, $sqlDeleteVotes);

        // Delete associated resources
        $sqlDeleteResources = "DELETE FROM resources WHERE learning_path_id=$id_to_delete";
        mysqli_query($conn, $sqlDeleteResources);

        // Delete associated steps
        $sqlDeleteSteps = "DELETE FROM steps WHERE learning_path_id=$id_to_delete";
        mysqli_query($conn, $sqlDeleteSteps);

        // Now delete the learning_path record
        $sqlDeletePath = "DELETE FROM learning_paths WHERE id=$id_to_delete";
        if (mysqli_query($conn, $sqlDeletePath)) {
            // Success
            header ('Location: index.php');
        } else {
            // Failure
            echo "Query error: " . mysqli_error($conn);
        }
    }
?>
