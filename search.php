<?php
    session_start();
    include('config/db_connect.php');

    // Check if search term is provided
    if (isset($_POST['searchTerm'])) {
        $searchTerm = $_POST['searchTerm'];

        $query = "SELECT * FROM learning_paths WHERE LOWER(title) LIKE LOWER('%$searchTerm%') OR LOWER(description) LIKE LOWER('%$searchTerm%')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo '<div class="row"><div class="col s12 cards-container">';
            while ($filteredPath = mysqli_fetch_assoc($result)) {
                displayCard($filteredPath);
            }
            echo '</div></div>';
        } else {
            echo "Query error: " . mysqli_error($conn);
        }
    } else {
        // If not searching, display all tutorials
        $sql = "SELECT * FROM learning_paths";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo '<div class="row"><div class="col s12 cards-container">';
            while ($learning_path = mysqli_fetch_assoc($result)) {
                displayCard($learning_path);
            }
            echo '</div></div>';
        } else {
            echo "Query error: " . mysqli_error($conn);
        }
    }

    // Function to display card content
    function displayCard($path)
    {
        echo '
            <div class="card blue-grey">
                <div class="card-image">
                    <img src="uploads/default_learningpath.jpg">
                    <span class="card-title">' . htmlspecialchars($path['title']) . '</span>
                </div>
                <div class="card-content" style="overflow: overlay;">
                    <p class="white-text">' . htmlspecialchars($path['description']) . '</p>
                </div>
                <div class="card-action">';
                if (isset($user)) :
                    echo '<a href="learn_more.php?id=' . $path['id'] . '">Learn More</a>';
                endif; 
               echo '</div>
            </div>
            ';
    }
?>


<style>
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
        flex-direction: column; /* Stack on top */

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

</style>