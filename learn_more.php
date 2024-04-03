<?php
    session_start();

    include('config/db_connect.php');
    
    // Check to see if user has logged on
    $isLoggedIn = isset($_SESSION['user_id']);

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

        // Fetch user's vote for the current learning path
       $userVote = null;
       $userId = $_SESSION['user_id'];
       $sqlVote = "SELECT vote_value FROM votes WHERE user_id = $userId AND learning_path_id = $id";
       $resultVote = $conn->query($sqlVote);
       $userVoteData = $resultVote->fetch_assoc();

       // Extract the vote value if user has voted
       if ($userVoteData) {
           $userVote = $userVoteData['vote_value'];
       }
        
       if (isset($_POST['vote'])) {
           $voteType = $_POST['vote'];

           // Check for if user has voted already
           if($userVote === null) {
              $sqlInsertVote = "INSERT INTO votes (user_id, learning_path_id, vote_value) VALUES ($userId, $id, '$voteType')";
              mysqli_query($conn, $sqlInsertVote);
           } else {
              // Updates existing vote
              $sqlUpdateVote = "UPDATE votes SET vote_value = '$voteType' WHERE user_id = $userId AND learning_path_id = $id";
              mysqli_query($conn, $sqlUpdateVote);
           }
           // Reload page to change vote
           header("Location: learn_more.php?id=$id");
        }

        // SQL Fetch for comments and user comments on the current learning path
        $sqlComments = "SELECT c.*, u.username
                        FROM comments c
                        LEFT JOIN users u ON c.user_id = u.id
                        WHERE c.learning_path_id = $id";

        $resultComments = $conn->query($sqlComments);

        // Check if there are comments
        $comments = [];
        if ($resultComments) {
        $comments = $resultComments->fetch_all(MYSQLI_ASSOC);
        }

        // Check to see if user has already commented
        $hasCommentedAlready = false;
        foreach ($comments as $comment) {
            if ($comment['user_id'] == $userId) {
                $hasCommentedAlready = true;
                break;
            }
        }

        if (isset($_POST['comment']) && $userVote !== null && !$hasCommentedAlready) {
            $commentText = mysqli_real_escape_string($conn, $_POST['comment']);
            $sqlInsertComment = "INSERT INTO comments (user_id, learning_path_id, comment_text) VALUES ($userId, $id, '$commentText')";
            mysqli_query($conn, $sqlInsertComment);

            // Reload page for comment
            header("Location: learn_more.php?id=$id");
        }       
    }
?>


<!DOCTYPE html>
<html lang="en">

    <?php include ('template/header.php');?>
    
    <div class="container center grey-text"><h4><?php echo htmlspecialchars($learning_path['title']); ?></h4>
    
    <p>Created By: <?php echo htmlspecialchars($creator['username']); ?></p>
    <p>Description: <?php echo htmlspecialchars($learning_path['description']); ?></p>   
       
    <!-- Clone learning path button -->
    <?php if ($isLoggedIn): ?>
        <a href="clone_path.php?id=<?php echo $learning_path['id'];?>" class="btn brand z-depth-0 center" style="margin-left: 11.25px">Clone this Learning Path</a>
    <?php endif; ?>

    <!-- Displays total upvotes and downvotes -->
    <div class="row">
        <div class="col s12">
            <?php
            $totalUpvotes = 0;
            $totalDownvotes = 0;

            // SQL Fetch for total upvotes and downvotes
            $sqlVotes = "SELECT COUNT(*) AS count, vote_value FROM votes WHERE learning_path_id = $id GROUP BY vote_value";
            $resultVotes = $conn->query($sqlVotes);

            while ($row = $resultVotes->fetch_assoc()) {
                if ($row['vote_value'] === 'upvote') {
                    $totalUpvotes = $row['count'];
                } elseif ($row['vote_value'] === 'downvote') {
                    $totalDownvotes = $row['count'];
                }
            }
            ?>
            <p>Total Upvotes: <?php echo htmlspecialchars($totalUpvotes); ?> </p>
            <p>Total Downvotes: <?php echo htmlspecialchars($totalDownvotes); ?></p>
        </div>
    </div>

    <!-- ADD EDIT/DELETE IF USER AND CREATED BY ARE SAME -->
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to permanently delete this tutorial? This action cannot be undone.");
        }
    </script>

    <div class="row" style="margin-bottom: -20px">
        <?php if ($isLoggedIn && $user['id'] == $creatorId): ?>
            <a href="edit_learn_more.php?id=<?php echo $learning_path['id'];?>" class="btn brand z-depth-0 left" style="margin-left: 11.25px">Edit</a>   
            <!-- Hidden delete form -->
            <form method="POST" action="delete_path.php" onsubmit="return confirmDelete();" style="width: 0; margin-left: -20px; padding: 0;" >
            <input type="hidden" name="id_to_delete" value="<?php echo $learning_path['id']; ?>">
            <input type="submit" name="delete" value="Delete" class="btn brand z-depth-0" style="margin: 0 11.25px">
            </form>
    <?php endif; ?> 
    </div>

    <div class="row">
        <?php foreach ($steps as &$step): ?>
        <div class="col s7">
            <div class="card blue-grey">
                <div class="card-content white-text">                    
                    <span class="card-title"><?php echo htmlspecialchars($step['step_number'] . '. ' . $step['title']); ?></span>
                    <p><?php echo htmlspecialchars($step['description']); ?></p>                    
                </div>    
            </div>    
        </div>
        <div class="col s5">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Resources</span>
                    <ul>
                        <?php $i=1 ?>
                        <?php foreach ($step['resources'] as $resource): ?>
                            <?php foreach (explode(',', $resource['url']) as $singleURL): ?>
                                <li><a href="<?php echo htmlspecialchars(trim($singleURL)); ?>"><?php echo $i?>. Link</a></li>
                                <?php $i++ ?>
                                <?php endforeach; ?>                        
                        <?php endforeach; ?>
                        
                    </ul>
                </div>    
            </div>    
        </div>
        <?php unset($step); ?>
        <?php endforeach; ?>
    </div>
    
    <!-- HTML layout for upvote/downvote buttons -->
    <div class="row">
        <div class="col s12">
            <form method="POST" action="learn_more.php?id=<?php echo $id; ?>">
                <?php if ($isLoggedIn): ?>
                    <label>
                        <input type="radio" name="vote" value="upvote" <?php echo ($userVote === 'upvote') ? 'checked' : ''; ?>>
                        <span>Upvote</span>
                    </label>
                    <label>
                        <input type="radio" name="vote" value="downvote" <?php echo ($userVote === 'downvote') ? 'checked' : ''; ?>>
                        <span>Downvote</span>
                    </label>
                    <button type="submit" class="btn blue-grey" style="margin: 0 11.25px">Vote</button>
                <?php else: ?>
                    <p>Login to vote</p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- HTML layout for comment section -->
    <div class="row">
        <div class="col s12">
            <?php if ($isLoggedIn && $userVote !== null && !$hasCommentedAlready): ?>
                <form method="POST" action="learn_more.php?id=<?php echo $id; ?>">
                    <label for="comment">Add a Comment:</label>
                    <textarea id="comment" name="comment" maxlength="300" style="height: 100px;" required></textarea></br>
                    <button type="submit" class="btn blue-grey">Submit Comment</button>
                </form>
            <?php elseif ($isLoggedIn && $userVote === null): ?>
                <p>Please vote before adding a comment!</p>
            <?php elseif ($isLoggedIn && $hasCommentedAlready): ?>
                <p>Sorry, you have already commented on this tutorial.</p>
            <?php else: ?>
                <p>Please login to add a comment.</p>
            <?php endif; ?>
        </div>
    </div>
     
    <!-- HTML Layout to display previous comments -->
    <div class="row">
        <div class="col s12">
            <h5>Give us Feedback!</h5>
            <?php foreach ($comments as $comment): ?>
                <div class="card blue-grey">
                    <div class="card-content white-text">
                        <p><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                        <p class="right-align">- <?php echo htmlspecialchars($comment['username']); ?>, <?php echo htmlspecialchars($comment['created_at']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
   
    <!-- HTML layout for sharing learn more page link -->
    <div class="row">
        <div class="col s12">
            <label for="shareLink">Share Link:</label>
            <input type="text" id="shareLink" value="<?php echo "http://f3749684.gblearn.com/comp1230/assignments/project/learn_more.php?id=$id"; ?>" readonly>
            <button onclick="copyToClipboard()" class="btn blue-grey">Copy Link</button>
        </div>
    </div>

    <!-- JS function for sharing link -->
    <script>
        function copyToClipboard() {
            var copyText = document.getElementById("shareLink");
            copyText.select();
            document.execCommand("copy");
            alert("Copied link: " + copyText.value);
        }
    </script>
    
    <?php if($learning_path): ?>
        <?php else: ?>
            <h5>No such learning path exists</h5>
        <?php endif; ?>
    </div>

    <?php include ('template/footer.php'); ?>
</html>