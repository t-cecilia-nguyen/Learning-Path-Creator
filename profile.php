<?php 

    session_start();

    include('config/db_connect.php');

    if(isset($_SESSION['user_id'])) {
      // User data
        $sql = "SELECT * FROM users
                WHERE id = {$_SESSION['user_id']}";              
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();
        
        // User image
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
?>

<!DOCTYPE html>
<html lang="en">

    <?php include ('template/header.php'); ?>    

    <?php if(isset($user)) : ?>
        <div class="container">
            <section class="container grey-text">
                <h3 class="brand-logo brand-text center"><?= htmlspecialchars($user['username']) ?>'s Profile</h1>
                <div class="center"><img src="<?php if(isset($profile_pic)) echo $profile_pic; ?>" width="200"></div>
                <form action="edit_profile.php" class="white" method="POST">
                    <label for="username">Username:</label>
                    <p class="black-text"><?php echo htmlspecialchars($user['username']) ?></p>
                    
                    <label for="email">Email:</label>
                    <p class="black-text"><?php echo htmlspecialchars($user['email']) ?></p>
                    </br>                                         
                    <a href="edit_profile.php" class="btn brand z-depth-0">Edit Profile</a>
                </form>
            </section>
        </div>        
    <?php else: ?>
        <h4 class="center grey-text">Please <a href="login.php">Log In</a> or <a href="signup.php">Sign Up</a></h4>
    <?php endif; ?>

    <?php include ('template/footer.php'); ?>
    
</html>