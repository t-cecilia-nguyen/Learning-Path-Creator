<?php

    session_start();

    include('config/db_connect.php');
    
    if (isset($_GET['id'])) {
        $originalLearningPathId = mysqli_real_escape_string($conn, $_GET['id']);
    
        // Fetchs original learning path details
        $sqlOriginalPath = "SELECT * FROM learning_paths WHERE id = $originalLearningPathId";
        $resultOriginalPath = mysqli_query($conn, $sqlOriginalPath);
        $originalPathData = mysqli_fetch_assoc($resultOriginalPath); 
    
        // Creates new learning path with new owner
        $newOwnerId = $_SESSION['user_id']; 
        $title = mysqli_real_escape_string($conn, $originalPathData['title']);
        $description = mysqli_real_escape_string($conn, $originalPathData['description']);
        
        // Inserts cloned learning path data into db
        $sqlClonePath = "INSERT INTO learning_paths (title, description, created_by) VALUES ('$title', '$description', $newOwnerId)";
        mysqli_query($conn, $sqlClonePath);
    
        // Fetchs cloned path ID
        $newLearningPathId = mysqli_insert_id($conn);
    
        // Clones original learning path steps
        $sqlOriginalSteps = "SELECT * FROM steps WHERE learning_path_id = $originalLearningPathId";
        $resultOriginalSteps = mysqli_query($conn, $sqlOriginalSteps);
        $numRows = $resultOriginalSteps->num_rows;
        $newStepId = 1;

        while (($originalStepData = mysqli_fetch_assoc($resultOriginalSteps)) && ($newStepId <= $numRows)) {
            $newStepTitle = mysqli_real_escape_string($conn, $originalStepData['title']);
            $newStepDescription = mysqli_real_escape_string($conn, $originalStepData['description']);
            $originalStepNumber = $originalStepData['step_number']; 

            // Inserts cloned steps data into db
            $sqlCloneStep = "INSERT INTO steps (title, description, learning_path_id, step_number) VALUES ('$newStepTitle', '$newStepDescription', $newLearningPathId, $originalStepNumber)";
            mysqli_query($conn, $sqlCloneStep);
   
            // Clones original learning path resources
            $sqlOriginalResources = "SELECT * FROM resources WHERE steps_id = {$originalStepData['step_number']} AND learning_path_id = $originalLearningPathId";
            $resultOriginalResources = mysqli_query($conn, $sqlOriginalResources);
            

            while ($originalResourceData = mysqli_fetch_assoc($resultOriginalResources)) {
                $newResourceUrl = mysqli_real_escape_string($conn, $originalResourceData['url']);
                // Inserts cloned resources into db
                $sqlCloneResource = "INSERT INTO resources (url, steps_id, learning_path_id) VALUES ('$newResourceUrl', $newStepId, $newLearningPathId)";
                mysqli_query($conn, $sqlCloneResource);  
            }
            $newStepId++;
        }
        // Redirect to the main menu 
        header("Location: index.php");
    } else {
        echo "Invalid request.";
    }

?>