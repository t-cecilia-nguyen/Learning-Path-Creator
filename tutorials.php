<?php
    
    include('config/db_connect.php');

    // Query for all learning paths
    $sql = "SELECT * FROM learning_paths";
    $result = mysqli_query($conn, $sql);
    $learning_paths = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>

<style>
    /* .cards-container {
    column-break-inside: avoid;
    }
    

    .cards-container .card {
    display: inline-block;
    overflow: visible;
    } */

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

    @media only screen and (min-width : 991px) {
    .cards-container {
        -webkit-column-count: 3;
        -moz-column-count: 3;
        column-count: 3;
    }
    }

    .text-center {
    text-align: center;
    }

    .row {
        display: flex;
        flex-direction: row;
        justify-content: start;
        align-items: stretch;
    }

    .col {
        display: flex;
        flex-direction: column;
    }

    .card {
        flex-grow: 1;
    }


</style>

<?php if (isset($user)) : ?>
<a href="add_path.php" class="btn brand z-depth-0" style="margin: 0 11.25px">Add Learning Path</a>   
<?php endif; ?>    
<div class="row">
    <?php 
    $counter = 0;
    foreach ($learning_paths as $learning_path): 
        if($counter != 0 && $counter % 2 == 0): ?>
            </div>
            <div class="row">
        <?php endif; ?>
        <div class="col s12 m6">
            <div class="card blue-grey"  >
                <div class="card-image">
                    <img src="uploads/default_learningpath.jpg">
                    <span class="card-title"><?php echo htmlspecialchars($learning_path['title']); ?></span>
                </div>
                <div class="card-content" style="overflow: overlay;">
                    <p class="white-text"><?php echo htmlspecialchars($learning_path['description']); ?></p>
                </div>
                <div class="card-action">
                    <?php if (isset($user)) : ?>
                        <a href="learn_more.php?id=<?php echo $learning_path['id'];?>">Learn More</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php 
    $counter++;
    endforeach; ?>
</div>

