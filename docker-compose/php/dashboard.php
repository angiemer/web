<?php
require_once "ggl.php"; // Assuming this is for external API calls (e.g., YouTube)
require_once 'db.php';  // Database connection

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: start.php');
    exit;
}

$searchResponse = isset($_SESSION['array_of_response']) ? $_SESSION['array_of_response'] : null;
$err_message = $_GET['err_message'] ?? '';

// Initialize $userProfile
$userProfile = [
    'id' => $_SESSION['id'],
    'username' => $_SESSION['username'],
    'first_name' => $_SESSION['first_name'],
    'last_name' => $_SESSION['last_name'],
    'email' => $_SESSION['email'],
    'avatar' => $_SESSION['image'],
    'favorites' => []
];

try {
    // Verify database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection not established"));
    }

    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT id, first_name, last_name, username, email, avatar FROM users WHERE username = ?");
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

        // Step 1: Fetch all playlists for the user
        $stmt2 = $conn->prepare("SELECT id, name FROM playlists WHERE user_id = ?");
        if (!$stmt2) {
            throw new Exception("Failed to prepare playlist query: " . $conn->error);
        }
        $stmt2->bind_param('i', $user['id']);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $playlists = $result2->fetch_all(MYSQLI_ASSOC); // Fetch all playlists as an array
        $stmt2->close();

        // Store playlists in session
        $_SESSION['playlists'] = $playlists;
        $_SESSION['playlist_user_id'] = $user['id'];

        // Step 2: Fetch all songs for these playlists
        $playlist_songs = [];
        if (!empty($playlists)) {
            // Extract playlist IDs
            $playlist_ids = array_column($playlists, 'id');
            $placeholders = implode(',', array_fill(0, count($playlist_ids), '?'));

            // Query to join playlist_songs and songs tables
            $sql = "
                SELECT ps.playlist_id, s.title, s.song_id, s.thumbnail
                FROM playlist_songs ps
                JOIN songs s ON ps.song_id = s.id
                WHERE ps.playlist_id IN ($placeholders)
            ";
            $stmt3 = $conn->prepare($sql);
            if (!$stmt3) {
                throw new Exception("Failed to prepare songs query: " . $conn->error);
            }
            // Bind all playlist IDs dynamically
            $stmt3->bind_param(str_repeat('i', count($playlist_ids)), ...$playlist_ids);
            $stmt3->execute();
            $result3 = $stmt3->get_result();

            // Organize songs by playlist_id
            while ($row = $result3->fetch_assoc()) {
                $playlist_id = $row['playlist_id'];
                if (!isset($playlist_songs[$playlist_id])) {
                    $playlist_songs[$playlist_id] = [];
                }
                $playlist_songs[$playlist_id][] = [
                    'title' => $row['title'],
                    'song_id' => $row['song_id'], // YouTube ID
                    'thumbnail' => $row['thumbnail']
                ];
            }
            $stmt3->close();
        }

        // Store songs in session
        $_SESSION['playlist_songs'] = $playlist_songs;

        // Step 3: Fetch favorite songs for the user
        $favorites = [];
        $stmt4 = $conn->prepare("
            SELECT s.title, s.song_id, s.thumbnail
            FROM favorites f
            JOIN favorite_songs fs ON f.id = fs.favor_id
            JOIN songs s ON fs.song_id = s.id
            WHERE f.user_id = ?
        ");
        if (!$stmt4) {
            throw new Exception("Failed to prepare favorites query: " . $conn->error);
        }
        $stmt4->bind_param('i', $user['id']);
        $stmt4->execute();
        $result4 = $stmt4->get_result();

        while ($row = $result4->fetch_assoc()) {
            $favorites[] = [
                'title' => $row['title'],
                'song_id' => $row['song_id'],
                'thumbnail' => $row['thumbnail']
            ];
        }
        $stmt4->close();

        // Store favorites in session
        $_SESSION['favorites'] = $favorites;

        $userProfile['id'] = $user['id'] ?: '';
        $userProfile['first_name'] = $user['first_name'] ?: '';
        $userProfile['last_name'] = $user['last_name'] ?: '';
        $userProfile['username'] = $user['username'];
        $userProfile['email'] = $user['email'] ?: '';
        $userProfile['avatar'] = !empty($user['avatar']) ? $user['avatar'] : 'images.png';

    } else {
        error_log("User not found or multiple users with username: $username");
        session_destroy();
        header('Location: start.php');
        exit;
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching user profile or playlists: " . $e->getMessage());
    $error_message = "Unable to load user profile or playlists. Please try again later.";
}
$conn->close();

$_SESSION['image'] = 'images.png';

// User profile variables
$userId = $_SESSION['id'];
$userUsername = $_SESSION['username'];
$userFirstName = $_SESSION['first_name'];
$userLastName = $_SESSION['last_name'];
$userEmail = $_SESSION['email'];
$userAvatar = $_SESSION['image'];
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
                    <form class="d-flex me-3" method="post" action="dashboard.php">
                        <input class="form-control me-2" type="text" placeholder="Search" name="query">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </form>
                    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileSidebar" onclick="loadSidebarProfile()" style="margin-right: 10px;">
                        Profile
                    </button>
                    <button class="btn btn-outline-light me-2" onclick="toggleDarkMode()">🌙</button>
                </div>
            </div>
        </nav>
        <!-- HOME -->
        <div class="container mt-4" id="homeSection">
            <h5 style="font-size: 12px; color: red;"><?php echo htmlspecialchars("$err_message") ?></h5>
        </div>
        <!-- PLAYLISTS -->
        <div class="container mt-5 d-none" id="playlistsSection">
            <h2>My Playlists</h2>
            <div id="playlistsContainer">
                <?php if (!empty($_SESSION['playlists'])): ?>
                    <?php foreach ($_SESSION['playlists'] as $playlist): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($playlist['name']); ?></h5>
                                    <form action="delete_playlist.php" method="post" class="ms-3">
                                        <input type="hidden" name="remove_list" value="<?php echo htmlspecialchars(json_encode($playlist)); ?>">
                                        <button class="btn btn-sm btn-outline-danger">🗑️</button>
                                    </form>
                                </div>
                                <?php if (!empty($_SESSION['playlist_songs'][$playlist['id']])): ?>
                                    <ul class="list-group mt-2">
                                        <?php foreach ($_SESSION['playlist_songs'][$playlist['id']] as $song): ?>
                                            <li class="class=list-group-item d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($song['thumbnail']); ?>" alt="Thumbnail" style="width: 80px; height: 80px; margin: 15px;">
                                                    <a href="https://youtu.be/<?php echo htmlspecialchars($song['song_id']); ?>" target="_blank" style="all: unset; cursor: pointer; border-radius: 15px; background-color: #6f42c1; padding: 10px; font-size: 18px; color: white;">
                                                        <?php echo htmlspecialchars($song['title']); ?>
                                                    </a>
                                                </div>
                                                <form method="post" action="favorite.php" class="d-inline">
                                                    <input type="hidden" name="song_values" value="<?php echo htmlspecialchars(json_encode($song)); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary me-2">❤️</button>
                                                </form>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="mt-2 mb-0">No songs in this playlist.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No playlists found.</p>
                <?php endif; ?>
            </div>
            <form class="mt-4" action="addPlaylist.php" method="post">
                <input id="newPlaylistName" class="form-control mb-2" placeholder="New playlist name" name="playlist_name" required>
                <button class="btn btn-success" type="submit">Add Playlist</button>
            </form>
        </div>
        <!-- FAVORITES -->
        <div class="container mt-5 d-none" id="favoritesSection">
            <h2>Favorites</h2>
            <ul class="list-group" id="favoritesList">
                <?php if (!empty($_SESSION['favorites'])): ?>
                    <?php foreach ($_SESSION['favorites'] as $favorite): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($favorite['thumbnail']); ?>" alt="Thumbnail" style="width: 80px; height: 80px; margin-right: 10px;">
                                <a href="https://youtu.be/<?php echo htmlspecialchars($favorite['song_id']); ?>" target="_blank" style="all: unset; cursor: pointer; border-radius: 15px; background-color: #6f42c1; padding: 10px; font-size: 18px; color: white;">
                                    <?php echo htmlspecialchars($favorite['title']); ?>
                                </a>
                            </div>
                            <form action="delete_favorite.php" method="post">
                                <input type="hidden" name="remove_fav" value="<?php echo htmlspecialchars(json_encode($favorite)); ?>">
                                <button class="btn btn-sm btn-outline-danger">🗑️</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No favorite songs found.</p>
                <?php endif; ?>
            </ul>
        </div>

        <!-- PROFILE SIDEBAR -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="profileSidebar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">My Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <div class="card text-center">
                    <div class="card-body">
                        <img src="<?php echo htmlspecialchars($userAvatar); ?>" class="rounded-circle mb-3" alt="User Avatar">
                        <h5 class="card-title" id="profileUsername"><?php echo htmlspecialchars($userUsername); ?></h5>
                        <p class="card-text" id="profileName"><?php echo htmlspecialchars($userFirstName . ' ' . $userLastName); ?></p>
                        <p class="card-text" id="profileEmail"><?php echo htmlspecialchars($userEmail); ?></p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="viewProfile()">View profile</button>
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#manageProfileModal">Manage profile</button>
                            <button class="btn btn-outline-danger" onclick="logout()">Logout</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW PROFILE MODAL -->
        <div class="modal fade" id="viewProfileModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title">User Profile</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="viewProfileAvatar" class="rounded-circle mb-3" style="width: 100px; height: 100px;" src="<?php echo htmlspecialchars($userAvatar); ?>">
                        <h5 id="viewProfileName"><?php echo htmlspecialchars($userFirstName . ' ' . $userLastName); ?></h5>
                        <p id="viewProfileUsername"><?php echo htmlspecialchars($userUsername); ?></p>
                        <p id="viewProfileEmail"><?php echo htmlspecialchars($userEmail); ?></p>
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
                                <input type="text" id="editFirstName" name="first_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['first_name']); ?>" placeholder="Enter first name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editLastName" class="form-label">Last Name</label>
                                <input type="text" id="editLastName" name="last_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['last_name']); ?>" placeholder="Enter last name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editUsername" class="form-label">Username</label>
                                <input type="text" id="editUsername" name="username" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" placeholder="Enter new username" required>
                            </div>
                            <div class="mb-3">
                                <label for="editPassword" class="form-label">New Password</label>
                                <input type="password" id="editPassword" name="password" class="form-control" placeholder="Enter new password">
                            </div>
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email</label>
                                <input type="email" id="editEmail" name="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" placeholder="Enter new email" required>
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

        <!-- OFFCANVAS MENU -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="menuSidebar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="goHome()" data-bs-dismiss="offcanvas">Home</button>
                    <button class="btn btn-primary" onclick="goToPlaylists()" data-bs-dismiss="offcanvas">Playlists</button>
                    <button class="btn btn-primary" onclick="showFavorites()" data-bs-dismiss="offcanvas">Favorites</button> 
                </div>
            </div>
        </div>

        <!-- VIDEOS -->
        <div class="container mt-5" id="videos_section">
            <h1 class="mb-4">Search Results</h1>
            <?php if ($searchResponse && !empty($searchResponse['items'])): ?>
                <div class="row">
                    <?php foreach ($searchResponse['items'] as $item):
                        $videoId = $item['id']['videoId'];
                        $title = $item['snippet']['title'];
                        $thumbnail = $item['snippet']['thumbnails']['medium']['url'];
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo htmlspecialchars($thumbnail); ?>" class="card-img-top h-50" alt="Thumbnail" style="object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($title); ?></h5>
                                <form method="post" action="favorite.php" class="d-inline">
                                    <input type="hidden" name="song_values" value="<?php echo htmlspecialchars(json_encode($item)); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary me-2">❤️</button>
                                </form>
                                </br>
                                <a href="https://youtu.be/<?php echo $videoId; ?>" class="btn btn-sm btn-primary" target="_blank">Δες στο YouTube</a>
                                <form method="post" action="add_video.php" class="d-inline">
                                    <input type="hidden" name="videoId" value="<?php echo $videoId; ?>">
                                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($title); ?>">
                                    <input type="hidden" name="values" value="<?php echo htmlspecialchars(json_encode($item)); ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Add to list</button>
                                    <input class="form-control me-2" type="text" placeholder="playlist name" name="playlistName" required>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Search to see results.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        const userId = <?php echo json_encode($userId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
        const userUsername = <?php echo json_encode($userUsername, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
        const firstName = <?php echo json_encode($userFirstName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
        const lastName = <?php echo json_encode($userLastName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
        const userEmail = <?php echo json_encode($userEmail, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
        const userAvatar = <?php echo json_encode($userAvatar, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
        const playlistUserId = <?php echo json_encode($_SESSION['playlist_user_id'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
    </script>
    <script type="text/javascript" src="functions.js"></script>
</body>
</html>