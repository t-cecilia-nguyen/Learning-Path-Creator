<?php
    $is_invalid = false;

    // Check form submission
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        include('config/db_connect.php');

        // Select record based on email
        $sql = sprintf("SELECT * FROM users 
                        WHERE email = '%s'",
                        mysqli_real_escape_string($conn, $_POST['email']));
        // Execute SQL                
        $result = $conn->query($sql);
        // Get data
        $user = $result->fetch_assoc();

        // if found
        if($user) {
            // check password
            if (password_verify($_POST['password'], $user['password_hash'])) {
                session_start();
                session_regenerate_id();
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit;
            }
        }
        $is_invalid = true;
    }
?>

<!DOCTYPE html>
<html lang="en">
    <?php include('template/header.php');?>
        <section class="container grey-text">
            <h3 class="brand-logo brand-text center">Log In</h1>           

            <form action="" class="white" method="POST">
                <?php if ($is_invalid): ?>
                    <em class="error-text">Invalid login </br></em>
                <?php endif; ?>                

                <label for="email">Email: <div class="error-text right"></div></label>
                <input type="text" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? "") ?>">

                <label for="password">Password: <div class="error-text right"></div></label>
                <input type="password" id="password" name="password" value="">

                <input type="submit" name="submit" value="Log In" class="btn brand z-depth-0">        
                
            </form>
        </section>
    <?php include('template/footer.php'); ?>
</body>
</html>