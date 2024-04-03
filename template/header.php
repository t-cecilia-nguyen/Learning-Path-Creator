<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Path Creator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">    
    <style type="text/css">
        .brand {
            background: #607d8b !important;
        }

        .brand-text {
            color: #ffab00 !important;
        }

        .error-text {
            color: red;
            font-size: 12;
            font-style: italic;
        }

        form {
            max-width: 460px;
            margin: 20px auto;
            padding: 20px;
        } 
    </style>
</head>

<body class="grey lighten-4">
    <nav class="white z-depth-0">
        <?php if (isset($user)) :?>
            <div class="container">
                <a href="index.php" class="brand-logo brand-text left">Learning Path Creator</a>
                <ul class="right hide-on-small-and-down">
                    <li>
                        <a href="profile.php" class="btn brand z-depth-0">Profile</a>
                    </li>
                    <li>
                        <a href="logout.php" class="btn brand z-depth-0">Log Out</a>
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <div class="container">
                <a href="index.php" class="brand-logo brand-text left">Learning Path Creator</a>
                <ul class="right hide-on-small-and-down">
                    <li>
                        <a href="signup.php" class="btn brand z-depth-0">Sign Up</a>
                    </li>
                    <li>
                        <a href="login.php" class="btn brand z-depth-0">Log In</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </nav>

    