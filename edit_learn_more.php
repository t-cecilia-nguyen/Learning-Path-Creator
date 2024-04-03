<?php
    session_start();

    
    include('config/db_connect.php');
    
    // Check to see if user has logged on
    $isLoggedIn = isset($_SESSION['user_id']);

    $steps = [];

    if($isLoggedIn) {
        // Fetch user data
        $sql = "SELECT * FROM users
                WHERE id = {$_SESSION['user_id']}";              
        $result = $conn->query($sql);
        if($result) {
            $user = $result->fetch_assoc();
        } else {
            echo "Query error: " . $conn->error;
        } 
        
        // Check GET request id parameter
        if(isset($_GET['id'])) {
            $id = mysqli_real_escape_string($conn, $_GET['id']);
            $sql = "SELECT * FROM learning_paths WHERE id=$id";
            $result = mysqli_query($conn, $sql);
            $learning_path = mysqli_fetch_assoc($result);

            // Fetch username of creator 
            $creatorId = $learning_path['created_by'];
            $sql = "SELECT username FROM users WHERE id = $creatorId";
            $result = $conn->query($sql);
            $creator = $result->fetch_assoc();

            // Fetch details of learning path
            $sql_learning_path = "SELECT *
                                FROM learning_paths
                                JOIN steps 
                                ON learning_paths.id = steps.learning_path_id
                                JOIN resources 
                                ON steps.id = resources.steps_id
                                WHERE learning_paths.id = $id";
            $result_learning_path = $conn->query($sql_learning_path);
            $detailed_paths = $result_learning_path->fetch_assoc();

            // Fetch details of steps
            $sql_steps = "SELECT * 
                        FROM steps
                        WHERE learning_path_id = $id";
            $result_steps = $conn->query($sql_steps);
            $steps = $result_steps->fetch_all(MYSQLI_ASSOC);
            
            //Fetch details of resources for each step
            foreach ($steps as &$step) {
                $step_number = $step['step_number'];

                // Query resources for the current step
                $sql_resources = "SELECT *
                                FROM resources
                                WHERE steps_id = $step_number && learning_path_id = $id";
                $result_resources = $conn->query($sql_resources);
                $step['resources'] = $result_resources->fetch_all(MYSQLI_ASSOC);
            }
        }
        
        if(isset($_POST['submit'])) {
                // Extract data from form
                $learning_path_id = mysqli_real_escape_string($conn, $_POST['learning_path_id']);
                $edited_title = mysqli_real_escape_string($conn, $_POST['edited_title']);
                $edited_description = mysqli_real_escape_string($conn, $_POST['edited_description']);
                $edited_created_by = mysqli_real_escape_string($conn, $user['id']);
            
                // Update the learning path information
                $sql_update_path = "UPDATE learning_paths
                                    SET title = '$edited_title',
                                    description = '$edited_description',
                                    created_by = '$edited_created_by'
                                    WHERE id = $learning_path_id";
            
                if (mysqli_query($conn, $sql_update_path)) {
                    // Success   
                    $sql_updated_path = "SELECT * FROM learning_paths WHERE id = $learning_path_id";
                    $result_updated_path = mysqli_query($conn, $sql_updated_path);
                    $learning_path = mysqli_fetch_assoc($result_updated_path);
                    
                } else {
                    echo "Error updating learning path: " . mysqli_error($conn);
                }

                // Update the steps and resources
                foreach ($_POST['edited_step_title'] as $old_step_number => $edited_step_title) {
                    $edited_step_description = $_POST['edited_step_description'][$old_step_number];
                    $edited_resource_url = $_POST['edited_resource_url'][$old_step_number];
                    
                    $new_step_number = $old_step_number + 1;

                    // Update the step information
                    $sql_update_step = "UPDATE steps
                                        SET title = '$edited_step_title',
                                        description = '$edited_step_description'
                                        WHERE learning_path_id = $learning_path_id
                                        AND step_number = $new_step_number";

                    if (mysqli_query($conn, $sql_update_step)) {
                        // Update the resource information
                        $sql_update_resource = "UPDATE resources
                                                SET url = '$edited_resource_url'
                                                WHERE learning_path_id = $learning_path_id
                                                AND steps_id = $new_step_number";

                        if (mysqli_query($conn, $sql_update_resource)) {
                                        // Success
                                        header('Location: learn_more.php?id=' . $learning_path['id']);
                                    }
                                } else {
                                    echo "Error updating step $new_step_number: " . mysqli_error($conn);
                                 }
                            }
                        }
                    } else {
                    echo mysqli_error($conn);      
                }
?>


<!DOCTYPE html>
<html lang="en">

    <?php include ('template/header.php'); ?>
    
    <div class="container center grey-text">
    <form method="POST" action="edit_learn_more.php?id=<?php echo $learning_path['id'];?>" style="max-width: none;">
        <h4>Title: <input type="text" name="edited_title" value="<?php echo htmlspecialchars($learning_path['title']); ?>"></h4>
        
        <!-- Created by changes to username after edit -->
        <p>Created By: <?php echo htmlspecialchars($user['username']); ?></p>
        <p>Description: <textarea name="edited_description" style="height: 100px"><?php echo htmlspecialchars($learning_path['description']); ?></textarea></p>   
        
        <!-- Hidden field to send the learning path ID -->
        <input type="hidden" name="learning_path_id" value="<?php echo $learning_path['id']; ?>">
        <!-- Hidden input field for stepCounter -->
        <input type="hidden" name="stepCounter" id="stepCounter" value="<?php echo isset($_POST['stepCounter']) ? htmlspecialchars($_POST['stepCounter']) : 1; ?>">
        
        <div class="row">
            <?php foreach ($steps as &$step): ?>
            <div class="col s7">
                <div class="card blue-grey">
                    <div class="card-content white-text">                    
                        <span class="card-title"> Step Title:
                            <input type="text" style="color: white;" name="edited_step_title[]" value="<?php echo htmlspecialchars($step['title']); ?>"></span>
                        <p>Step Description: <textarea name="edited_step_description[]" style="height: 100px; color: white;"><?php echo htmlspecialchars($step['description']); ?></textarea></p>                    
                    </div>    
                </div>    
            </div>
            <div class="col s5">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Resources</span>
                            <?php foreach ($step['resources'] as $resource): ?>
                                <textarea name="edited_resource_url[]" style="height: 100px"><?php echo htmlspecialchars($resource['url']); ?></textarea>                       
                            <?php endforeach; ?>
                    </div>    
                </div>    
            </div>
            <?php unset($step); ?>
            <?php endforeach; ?>
            <div id="stepsContainer"></div>
        </div>  
        </br>                                           
        <input type="submit" name="submit" value="Submit" class="btn brand z-depth-0"> 
    </form>
    </div>
    <?php include ('template/footer.php'); ?>
</html>