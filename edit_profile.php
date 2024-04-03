<?php 

    session_start();

    include('config/db_connect.php');

    if(isset($_SESSION['user_id'])) {
        // Fetch user data
        $sql = "SELECT * FROM users
                WHERE id = {$_SESSION['user_id']}";              
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();

        // // Fetch user image data
        $sql_img = "SELECT * FROM profile_images
                    WHERE user_id = {$_SESSION['user_id']}";
        $result_img = $conn->query($sql_img);
        $user_img = $result_img->fetch_assoc();
      
        // Check if user has profile picture
        if (isset($user_img) && !empty($user_img['image_reference'])) {
            $profile_pic = "uploads/" . $user_img['image_reference'];
        } else {           
            $profile_pic = "uploads/default_profile.jpg";
        }
    }

    if (isset($_POST['submit'])) {
        // Check username and email
        if (isset($_POST['username'])) {
            $new_username = mysqli_real_escape_string($conn, $_POST['username']);
            $new_email = mysqli_real_escape_string($conn, $_POST['email']);
            // Create SQL
            $sql_update = "UPDATE users 
                        SET username = '$new_username', email = '$new_email'
                        WHERE id = {$_SESSION['user_id']}";
            $sql_update_result = mysqli_query($conn, $sql_update);
            // Reload user data after updating
            if($sql_update_result) {
                $result = $conn->query($sql);
                $user = $result->fetch_assoc();
            } else {
            // Error updating the username
            echo 'Query error: ' . mysqli_error($conn);
            }
        }
    
        $file = $_FILES['file'];
    
        // check if new file is provided
        if(!empty($file['name'])) {
            $fileName = $_FILES['file']['name'];
            $fileTmpName = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];
            $fileError = $_FILES['file']['error'];
            $fileType = $_FILES['file']['type'];

            // Extract file name and file type
            $fileExtension = explode('.', $fileName);
            $fileActualExtension = strtolower(end($fileExtension));

            // List allowed file types
            $allowed = array('jpg', 'jpeg', 'png');

            if(in_array($fileActualExtension, $allowed)) {
                if($fileError === 0) {
                    if($fileSize < 1000000) {
                        $fileNameNew = uniqid('', true) . "." . $fileActualExtension;
                        $fileDestination = 'uploads/' . $fileNameNew;
                        // Upload file
                        move_uploaded_file($fileTmpName, $fileDestination);
                        $fileNameNew = mysqli_real_escape_string($conn, $fileNameNew);
                        // Create SQL
                        $sql_img = "UPDATE profile_images 
                                    SET image_reference = '$fileNameNew'
                                    WHERE user_id = {$_SESSION['user_id']}";
                        // Save to database and check
                        if($conn->multi_query($sql_img)) {
                            // Success
                            header('Location: profile.php');
                        } else {
                            // Error
                            echo 'Query error' . mysqli_error($conn);
                        }     
                    } else {
                        echo "The file is too large.";
                    }
                } else {
                    echo "There was an error uploading the file.";
                }
            } else {
                echo "Cannot upload files of this type.";
            }
        } else {
            // No file
            header('Location: profile.php');
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

    <?php include ('template/header.php'); ?>    

    <?php if (isset($user)) : ?>
        <div class="container">
            <section class="container grey-text">
                <h3 class="brand-logo brand-text center"><?= htmlspecialchars($user['username']) ?>'s Profile</h1>                
                <div class="center"><img src="<?php if(isset($profile_pic)) echo $profile_pic; ?>" width="200"></div>
                <form action="edit_profile.php" class="white" method="POST" enctype="multipart/form-data">
                    <input type="file" name="file"> </br> </br>
                    <label for="username">Username:</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']) ?>">
                    
                    <label for="email">Email:</label>
                    <input type="text" name="email" value="<?php echo htmlspecialchars($user['email']) ?>">
                    </br>                    
                    <input type="submit" name="submit" value="Submit" class="btn brand z-depth-0"> 
                </form>
            </section>
        </div>        
    <?php else: ?>
        <h4 class="center grey-text">Please <a href="login.php">Log In</a> or <a href="signup.php">Sign Up</a></h4>
    <?php endif; ?>

    <?php include ('template/footer.php'); ?>
</html>