<?php 
    $err_message = $_GET['err_message'] ?? '';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melofy</title>
    <link href="mystyle.css" media="all" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="appContent">
        <div id="loginSection">
            <div id="loginCard" class="card p-4">
                <h3 class="mb-3">Welcome to Melofy</h3>
                <form action="login.php" method="post">
                <input type="text" id="userUsername" name="userUsername" class="form-control mb-2" placeholder="username" required>
                <input type="password" id="userPassword" name="userPassword" class="form-control mb-2" placeholder="password" required>
                <div class="d-grid gap-2 mt-3">
                    <h5 style="font-size: 12px; color: red;"><?php echo htmlspecialchars("$err_message") ?></h5>

                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                </div>
                </form>
                <div class="text-center mt-3">
                <small>Are you new here? 
                    <a href="signup.php">Sign up</a>
                </small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>