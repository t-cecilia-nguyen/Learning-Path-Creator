<?php
    session_start();

    include('config/db_connect.php');

    // COMMENTS TABLE
    $sql6 = "CREATE TABLE IF NOT EXISTS comments (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6),
        learning_path_id INT(6),
        comment_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

    $sql7 = "CREATE TABLE IF NOT EXISTS votes (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6),
        learning_path_id INT(6),
        vote_value ENUM('upvote', 'downvote')
        )";

    if ($conn->query($sql6) !== TRUE) {
        echo "Error creating comments table: " . $conn->error;
    }

    if ($conn->query($sql7) !== TRUE) {
        echo "Error creating votes table: " . $conn->error;
    }

    if(isset($_SESSION['user_id'])) {
        // Fetch user data
        $sql = "SELECT * FROM users
                WHERE id = {$_SESSION['user_id']}";              
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();
    }

    $title = $description = $created_by = $step = $stepDescription = $resources = '';
    $errors = array('title' => '', 'description' => '', 'created_by' => '', 'step' => '', 'stepDescription' => '', 'resources' => '');

    
    if(isset($_POST['submit'])) {
        // check title
        if (empty($_POST['title'])) {
            $errors['title'] = "A title is required <br />";
            
        } else {
            $title = $_POST['title'];
            if (!preg_match('/^[a-zA-Z0-9\s]+$/', $title)) {
                $errors['title'] = "Title must be letters, numbers and spaces only";
            }
        }

        // check description
        if (empty($_POST['description'])) {
            $errors['description'] = "A description is required <br />";
        } else {
            $description = $_POST['description'];
        }

        // check step title
        if (empty($_POST['step'])) {
            $errors['step'] = "A step title is required <br />";
        } else {
            $step = $_POST['step'];
            if (!preg_match('/^[a-zA-Z0-9\s]+$/', $title)) {
                $errors['step'] = "Step title must be letters, numbers and spaces only";
            }
        }
       
        // check step description
        if (empty($_POST['stepDescription'])) {
            $errors['stepDescription'] = "A description is required for this step <br />";
        } else {
            $stepDescription = $_POST['stepDescription'];
        }

        // check step resources
        if (empty($_POST['resources'])) {
            $errors['resources'] = "At least one resource is required <br />";
        } else {
            $resources = $_POST['resources'];
            if (!preg_match('/^\S+(?:,\S+)*$/', $resources)) {
                $errors['resources'] = "Resources must be comma separated list (no spaces)";
            }
        }

        // Check for errors
        if (array_filter($errors)) {
            // displayed in html
        } else {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']); 
        
            // Create SQL 
            $sql = "INSERT INTO learning_paths(title, description, created_by) 
                    VALUES('$title', '$description', '{$_SESSION['user_id']}')";
            
            // Save to database and check
            if(mysqli_query($conn, $sql)) {
                // Get the last inserted learning path ID
                $lastPathId = mysqli_insert_id($conn);

                $stepTitle = mysqli_real_escape_string($conn, $_POST['step']);
                $stepDescription = mysqli_real_escape_string($conn, $_POST['stepDescription']);
                $stepResources = mysqli_real_escape_string($conn, $_POST['resources']);

                $sql_step = "INSERT INTO steps (learning_path_id, step_number, title, description)
                            VALUES ($lastPathId, 1, '$stepTitle', '$stepDescription');";
                $sql_step .= "INSERT INTO resources (learning_path_id, steps_id, url)
                                VALUES ($lastPathId, 1, '$stepResources')";

                if(mysqli_multi_query($conn, $sql_step))
                {
                    do {
                        // Consume the result set
                        if ($result = mysqli_store_result($conn)) {
                            mysqli_free_result($result);
                        }
                    } while (mysqli_next_result($conn));
                    
                    // Create SQL for Steps
                    for ($i = 2; $i <= $_POST['stepCounter']; $i++) {
                        $stepName = 'step' . $i;
                        $stepDescriptionName = 'stepDescription' . $i;
                        $resourcesName = 'resources' . $i;
                        
                        $stepTitle = mysqli_real_escape_string($conn, $_POST[$stepName]);
                        $stepDescription = mysqli_real_escape_string($conn, $_POST[$stepDescriptionName]);
                        $stepResources = mysqli_real_escape_string($conn, $_POST[$resourcesName]);

                        // Create SQL for step
                        $sql_step = "INSERT INTO steps (learning_path_id, step_number, title, description)
                                    VALUES ($lastPathId, $i, '$stepTitle', '$stepDescription')";

                        if(mysqli_query($conn, $sql_step)) {
                            
                            // Create SQL for resources
                            $sql_resource = "INSERT INTO resources (learning_path_id, steps_id, url)
                                            VALUES ($lastPathId, $i, '$stepResources')";

                            if(mysqli_query($conn, $sql_resource)) {
                                header('Location: index.php');
                            } else {
                                // Error
                                echo 'Query error' . mysqli_error($conn);
                            }               
                        }
                    }
                } // End of POST check
            }
        }
    }
?>

<!DOCTYPE html>
<html>
    <?php include('template/header.php'); ?>
    <section class="container grey-text">
        <h4 class="center">Add Learning Path</h4>
        <form action="add_path.php" class="white" method="POST">
            
        <label>Tutorial Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
            <div class="red-text"><?php echo $errors['title']; ?></div>
            
            <label for="description" >Description:</label>      
            <textarea id="description" name="description" style="max-width: 420px; height: 100px;" maxlength="220"><?php echo htmlspecialchars($description); ?></textarea>
            <div class="red-text"><?php echo $errors['description']; ?></div>
            </br>

            <label>Step Title:</label>
            <input type="text" name="step" placeholder="Step 1" value="<?php echo htmlspecialchars($step); ?>">
            <div class="red-text"><?php echo $errors['step']; ?></div>
            
            <!-- Hidden input field for stepCounter -->
            <input type="hidden" name="stepCounter" id="stepCounter" value="<?php echo isset($_POST['stepCounter']) ? htmlspecialchars($_POST['stepCounter']) : 1; ?>">

            <label for="stepDescription" >Step Description:</label>      
            <textarea id="stepDescription" name="stepDescription" style="max-width: 420px; height: 100px;" maxlength="220" placeholder="Step Description 1"><?php echo htmlspecialchars($stepDescription); ?></textarea>
            <div class="red-text"><?php echo $errors['stepDescription']; ?></div>

            <label for="resources" >Resources (must be comma separated list and no spaces):</label>      
            <textarea id="resources" name="resources" style="max-width: 420px; height: 130px;" maxlength="255" placeholder="Resource 1"><?php echo htmlspecialchars($resources); ?></textarea>
            <div class="red-text"><?php echo $errors['resources']; ?></div>

            <div id="stepsContainer"></div>
            </br>
            <button type="button" onclick="addStep()" class="btn brand z-depth-0">Add Step</button>

            <script>
                var stepCounter = 1;

                function addStep() {
                    stepCounter++;
                    var stepValue = stepCounter;

                    // Create a new input field for the step
                    var newStepInput = document.createElement("input");
                    newStepInput.type = "text";
                    newStepInput.name = "step" + stepValue;
                    newStepInput.placeholder = "Step " + stepValue;
                    newStepInput.style.marginTop = "10px";

                    // Append the new input field to the container
                    document.getElementById("stepsContainer").appendChild(newStepInput);

                    // Create a new input field for the step description
                    var newStepDescriptionTextarea = document.createElement("textarea");
                    newStepDescriptionTextarea.type = "textbox";
                    newStepDescriptionTextarea.name = "stepDescription" + stepValue;
                    newStepDescriptionTextarea.placeholder = "Step Description " + stepValue;
                    newStepDescriptionTextarea.style.marginTop = "10px";
                    newStepDescriptionTextarea.style.maxWidth = "420px";
                    newStepDescriptionTextarea.style.height = "130px";
                    newStepDescriptionTextarea.maxLength = 255;
                    
                    // Append the new input field to the container
                    document.getElementById("stepsContainer").appendChild(newStepDescriptionTextarea);

                    // Create a new input field for resources
                    var newResourcesTextarea = document.createElement("textarea");
                    newResourcesTextarea.type = "textbox";
                    newResourcesTextarea.name = "resources" + stepValue;
                    newResourcesTextarea.placeholder = "Resource " + stepValue;
                    newResourcesTextarea.style.marginTop = "10px";
                    newResourcesTextarea.style.maxWidth = "420px";
                    newResourcesTextarea.style.height = "130px";
                    newResourcesTextarea.maxLength = 255;
                    
                    // Append the new input field to the container
                    document.getElementById("stepsContainer").appendChild(newResourcesTextarea);

                    // Add the new variable to the form
                    var newVariableInput = document.createElement("input");
                    newVariableInput.type = "hidden";
                    newVariableInput.name = "stepCounter";
                    newVariableInput.value = stepCounter;
                    document.getElementById("stepsContainer").appendChild(newVariableInput);

                    document.getElementById("stepCounter").value = stepCounter;
                }
            </script>
            <div class="center">
                <input type="submit" name="submit" value="submit" class="btn brand z-depth-0">
            </div>
        </form>
    </section>

    <?php include('template/footer.php'); ?>
    
</html>
