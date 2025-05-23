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
        <div class="modal fade" tabindex="-1" style="opacity: 1; display:block;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sign up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="signin.php" method="post">
                <label>First name</label>
                <input type="text" id="signupFirstname" name="firstname" class="form-control mb-2" required>
                <label>Last name</label>
                <input type="text" id="signupLastname" name="lastname" class="form-control mb-2" required>
                <label>Username</label>
                <input type="text" id="signupUsername" name="username" class="form-control mb-2" required>
                <label>Password</label>
                <input type="password" id="signupPassword" name="password" class="form-control mb-2" required>
                <label>Email</label>
                <input type="email" id="signupEmail" name="email" class="form-control mb-2" required>
                <div class="d-grid gap-2 mt-3">
                <button class="btn btn-success" name="signin">Sign up</button>
                </div>
                <div class="text-center mt-3">
                <small>Already have an accunt? <a href="start.php">Σύνδεση</a></small>
                </div>
                </form>
            </div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>