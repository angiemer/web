<?php

if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
}
session_start();
$searchResponse = isset($_SESSION['array_of_response']) ? $_SESSION['array_of_response'] : null;

require_once 'db.php';

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['username'])) {
    header('Location: start.php');
    exit;
}

// Αρχικοποίηση της $userProfile
$userProfile = [
    'username' => $_SESSION['username'],
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'avatar' => 'https://via.placeholder.com/100.png',
    'favorites' => []
];

try {
    // Ελέγξτε αν η σύνδεση είναι έγκυρη
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection not established"));
    }

    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT first_name, last_name, username, email, avatar FROM users WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare SQL statement: " . $conn->error);
    }
    $stmt->bind_param('s', $username);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute SQL statement: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Failed to get result from query: " . $conn->error);
    }
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $userProfile['first_name'] = $user['first_name'] ?: '';
        $userProfile['last_name'] = $user['last_name'] ?: '';
        $userProfile['username'] = $user['username'];
        $userProfile['email'] = $user['email'] ?: '';
        $userProfile['avatar'] = $user['avatar'] ?: 'https://via.placeholder.com/100.png';
    } else {
        error_log("User not found or multiple users with username: $username");
        session_destroy();
        header('Location: start.php');
        exit;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching user profile: " . $e->getMessage());
    $error_message = "Unable to load user profile. Please try again later.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melofy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyle.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="appContent">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#" onclick="goHome()">Melofy</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mynavbar">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <button class="btn btn-outline-light me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuSidebar">
                                Menu
                            </button>
                        </li>
                    </ul>
                    <form class="d-flex me-3" method="post" action="ggl.php">
                        <input class="form-control me-2" type="text" placeholder="Search" name="query">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </form>
                    <button class="btn btn-outline-light me-2" onclick="toggleDarkMode()">🌙</button>
                    <button class="btn p-0 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileSidebar">
                        <img src="<?php echo htmlspecialchars($userProfile['avatar']); ?>" class="rounded-circle" alt="User Avatar" style="width: 40px; height: 40px;">
                    </button>
                </div>
            </div>
        </nav>
        <!-- HOME -->
        <div class="container mt-4" id="homeSection">
            <h3>Καλώς ήρθες στην αρχική!</h3>
            <p>Εδώ εμφανίζονται τα playlists, αγαπημένα, κλπ.</p>
        </div>
        <!-- PLAYLISTS -->
        <div class="container mt-5 d-none" id="playlistsSection">
            <h2>My playlists</h2>
            <div id="playlistsContainer"></div>
            <div class="mt-4">
                <input id="newPlaylistName" class="form-control mb-2" placeholder="New playlist name">
                <button class="btn btn-success" onclick="addPlaylist()">Add Playlist</button>
            </div>
        </div>
        <!-- FAVORITES -->
        <div class="container mt-5 d-none" id="favoritesSection">
            <h2>Favorites</h2>
            <ul class="list-group" id="favoritesList"></ul>
        </div>

        <!-- PROFILE SIDEBAR -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="profileSidebar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">My profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <div class="card text-center">
                    <div class="card-body">
                        <img src="<?php echo htmlspecialchars($userProfile['avatar']); ?>" class="rounded-circle mb-3" alt="User Avatar">
                        <h5 class="card-title" id="profileUsername"><?php echo htmlspecialchars($userProfile['username']); ?></h5>
                        <p class="card-text" id="profileName"><?php echo htmlspecialchars($userProfile['first_name'] . ' ' . $userProfile['last_name']); ?></p>
                        <p class="card-text" id="profileEmail"><?php echo htmlspecialchars($userProfile['email']); ?></p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="viewProfile()">View profile</button>
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#manageProfileModal">Manage profile</button>
                            <button class="btn btn-outline-danger" onclick="logout()">Logout</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MANAGE PROFILE MODAL -->
        <div class="modal fade" id="manageProfileModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="profileError" class="alert alert-danger d-none"></div>
                        <form id="Save" action="update_profile.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="editFirstName" class="form-label">First Name</label>
                                <input type="text" id="editFirstName" name="first_name" class="form-control" value="<?php echo htmlspecialchars($userProfile['first_name']); ?>" placeholder="Enter first name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editLastName" class="form-label">Last Name</label>
                                <input type="text" id="editLastName" name="last_name" class="form-control" value="<?php echo htmlspecialchars($userProfile['last_name']); ?>" placeholder="Enter last name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editUsername" class="form-label">Username</label>
                                <input type="text" id="editUsername" name="username" class="form-control" value="<?php echo htmlspecialchars($userProfile['username']); ?>" placeholder="Enter new username" required>
                            </div>
                            <div class="mb-3">
                                <label for="editPassword" class="form-label">New Password</label>
                                <input type="password" id="editPassword" name="password" class="form-control" placeholder="Enter new password">
                            </div>
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email</label>
                                <input type="email" id="editEmail" name="email" class="form-control" value="<?php echo htmlspecialchars($userProfile['email']); ?>" placeholder="Enter new email" required>
                            </div>
                            <div class="mb-3">
                                <label for="editProfilePic" class="form-label">Profile Image URL</label>
                                <input type="text" id="editProfilePic" name="avatar" class="form-control" value="<?php echo htmlspecialchars($userProfile['avatar']); ?>" placeholder="Enter image URL">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="manage_profile" form="Save">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW PROFILE MODAL -->
        <div class="modal fade" id="viewProfileModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title">User profile</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="viewProfileAvatar" class="rounded-circle mb-3" style="width: 100px; height: 100px;" src="">
                        <h5 id="viewProfileName"></h5>
                        <p id="viewProfileUsername"></p>
                        <p id="viewProfileEmail"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- OFFCANVAS MENU -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="menuSidebar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                    <ul class="list-unstyled">
                        <li><a href="#" class="btn btn-link" onclick="goHome()" data-bs-dismiss="offcanvas">Home</a></li>
                        <li><a href="#" class="btn btn-link" onclick="goToPlaylists()" data-bs-dismiss="offcanvas">Playlists</a></li>
                        <li><a href="#" class="btn btn-link" onclick="showFavorites()" data-bs-dismiss="offcanvas">Favorites</a></li>
                    </ul>
            </div>
        </div>

        <!-- VIDEOS -->
    <div class="container mt-5" id="result">
        <h1 class="mb-4">Αποτελέσματα αναζήτησης</h1>

        <?php if ($searchResponse && !empty($searchResponse['items'])):?>
            <div class="row">
                <?php foreach ($searchResponse['items'] as $item):
                    $videoId = $item['id']['videoId'];
                    $title = $item['snippet']['title'];
                    $thumbnail = $item['snippet']['thumbnails']['medium']['url'];
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $thumbnail; ?>" class="card-img-top" alt="Thumbnail">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($title); ?></h5>
                            <a href="https://youtu.be/<?php echo $videoId; ?>" class="btn btn-sm btn-primary" target="_blank">Δες στο YouTube</a>
                            <form method="post" action="add_video.php" class="d-inline">
                                <input type="hidden" name="videoId" value="<?php echo $videoId; ?>">
                                <input type="hidden" name="title" value="<?php echo htmlspecialchars($title); ?>">
                                <button type="submit" class="btn btn-sm btn-success">Προσθήκη στη λίστα</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Κάνε μια αναζήτηση για να δεις αποτελέσματα.</p>
        <?php endif; ?>
    </div>
    <script type="text/javascript" src="functions.js"></script>
</body>
</html>