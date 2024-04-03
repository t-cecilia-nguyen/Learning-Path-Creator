<?php

    include('config/db_connect.php');

    $username = $password = $passwordConfirmation = $email = '';
    $errors = array('username' => '','password' => '' ,'passwordConfirmation' => '' ,'email' => '' );

    if(isset($_POST['submit'])) {
        
        // check username
        if(empty($_POST['username'])) {
            $errors['username'] = "A Username is required <br />";
        } else {
            $username = $_POST['username'];
            if (!preg_match('/^[a-zA-Z0-9_]*$/', $username)) {
                $errors['username'] = "Please use only letters, numbers, and underscores.";                
            }
        }

        // check password
        if(empty($_POST['password'])) {
            $errors['password'] = "A password is required";
        } else {
            $password = $_POST['password'];
            if (strlen($password) < 8 ) {
                $errors['password'] = "Password must be at least 8 characters.";                
            }
            if (!preg_match('/[a-z]/i', $password)) {
                $errors['password'] = "Password must contain at least 1 letter.";                
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors['password'] = "Password must contain at least 1 number.";                
            }
        }

        // check password confirmation
        $passwordConfirmation = $_POST['passwordConfirmation'];
        if ($passwordConfirmation !== $password) {
            $errors['passwordConfirmation'] = "Passwords must match.";
        }

        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT); 
        
        // check email 
        if(empty($_POST['email'])) {
            $errors['email'] = "An email is required <br />";
	    } else {
            $email = $_POST['email'];
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Email must be a valid email address.";
            }
        }  

        // check for errors
        if (array_filter($errors)) {

        } else {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);            

            // Create SQL
            $sql = "INSERT INTO users (username, email, password_hash)
                    VALUES ('$username', '$email', '$password_hash')";

            if ($conn->query($sql) !== TRUE) {
                echo "Error: " . $sql . "<br>" . $conn->error;
            } else {
                $last_id = $conn->insert_id;
                $sql = "INSERT INTO profile_images (user_id) VALUES ('$last_id')";
                if ($conn->query($sql) !== TRUE) {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                } else {
                    // Success
                    echo '<script>alert("Sign Up Successful! Please Log In.");
                    window.location.href = "index.php";</script>';
                }
            }
        }
    }   
?>

<!DOCTYPE html>
<html lang="en">
    <?php include('template/header.php');?>
        <section class="container grey-text">
            <h3 class="brand-logo brand-text center">Sign Up</h1>
            <form action="signup.php" class="white" method="POST">
                <label for="username">Username: <div class="error-text right"><?php echo $errors['username'];?></div></label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username) ?>">

                <label for="password">Password: <div class="error-text right"><?php echo $errors['password'];?></div></label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password) ?>">

                <label for="passwordConfirmation">Confirm Password: <div class="error-text right"><?php echo $errors['passwordConfirmation'];?></div></label>
                <input type="password" id="passwordConfirmation" name="passwordConfirmation">

                <label for="email">Email: <div class="error-text right"><?php echo $errors['email'];?></div></label>
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email) ?>">
                
                <input type="submit" name="submit" value="Create Account" class="btn brand z-depth-0">                       
            </form>
        </section>
    <?php include('template/footer.php'); ?>
</body>
</html>