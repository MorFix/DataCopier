<?php
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . '/../src/load-dotenv.php');

session_start();

if (isset($_SESSION['verified']) && $_SESSION['verified']) {
    header('Location: index.php');

    die();
}

if (isset($_POST['login'])) {
    if (isset($_POST['password']) && $_POST['password'] === $_ENV['PASSWORD']) {
        session_start();
        $_SESSION['verified'] = true;
        header('Location: index.php');

        die();
    } else {
        $error = "Bad password, try again";
    }
}
?>

<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="assets/style.css" />
    <title>Login</title>
</head>

<body>

<div class="column center">
    <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" class="column center">
        <?php if (!empty($error)): ?>
        <h3 class="row">
            <?= $error; ?>
        </h3>
        <?php endif; ?>

        <div class="row margin">
            <label for="password">Enter password:</label>
            <input type="password" name="password" id="password" />
        </div>

        <input type="submit" name="login" value="Login" />
    </form>
</div>

</body>
</html>