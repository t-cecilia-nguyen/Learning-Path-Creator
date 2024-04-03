<?php
    session_start();

    include('config/db_connect.php');

    if(isset($_SESSION['user_id'])) {    
        // Fetch user data
        $sql = "SELECT * FROM users
                WHERE id = {$_SESSION['user_id']}";              
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();
    }
?>
<!-- <style>
    .cards-container {
    column-break-inside: avoid;
    }
    

    .cards-container .card {
    display: inline-block;
    overflow: visible;
    }

    @media only screen and (max-width : 600px) {
    .cards-container {
        -webkit-column-count: 1;
        -moz-column-count: 1;
        column-count: 1;
    }
    }

    @media only screen and (min-width : 601px) {
    .cards-container {
        -webkit-column-count: 2;
        -moz-column-count: 2;
        column-count: 2;
    }
    }

    /* @media only screen and (min-width : 991px) {
    .cards-container {
        -webkit-column-count: 3;
        -moz-column-count: 3;
        column-count: 3;
    }
    } */

    .text-center {
    text-align: center;
    }

</style> -->
<!DOCTYPE html>
<html lang="en">

    <?php include ('template/header.php'); ?>

    <div class="container">
            <label for="searchInput">Search Tutorials:</label>
            <input type="text" id="searchInput" placeholder="Type to search">
            <button onclick="searchTutorials()" class="btn blue-grey">Search</button>
    </div>

    <?php if (isset($user)) : ?>
        <h4 class="center grey-text">Welcome <?= htmlspecialchars($user['username'])?>!</h4>
    <?php endif; ?>

        <div class="container">
            <h4 class="center blue-grey-text">Tutorials</h4>
        </div>


        <div id="tutorialList" class="container"> 
                <?php include('tutorials.php'); ?>
        </div>

    <?php include('template/footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        function searchTutorials() {
            var searchTerm = $("#searchInput").val();

            $.ajax({
                type: "POST",
                url: "search.php",
                data: { searchTerm: searchTerm },
                success: function (response) {
                    console.log("Search Term:", searchTerm);
                    console.log("Response:", response);
                    $("#tutorialList").html(response);
                },
                error: function (error) {
                    console.error("Error: ", error);
                }
            });
        }
    </script> 
</html>