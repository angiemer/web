<!DOCTYPE html>
<html lang="en">
<head>
  <title>Melofy</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background: linear-gradient(to right, #654ea3, #eaafc8);
      font-family: 'Segoe UI', sans-serif;
    }

    .card {
      border-radius: 1rem;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .form-control, .btn {
      border-radius: 0.75rem;
    }

    .navbar, .offcanvas {
      border-radius: 0 0 1rem 1rem;
    }

    .btn-primary {
      background-color: #6f42c1;
      border: none;
    }

    .btn-primary:hover {
      background-color: #5a32a3;
    }

    .offcanvas {
      background-color: #ffffff;
    }

    .offcanvas-title, .offcanvas-body, h2, h3 {
      color: #343a40;
    }

    .list-group-item {
      border: none;
    }

    .avatar-circle {
      width: 40px;
      height: 40px;
      object-fit: cover;
      border-radius: 50%;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    #loginSection {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(to right, #6a11cb, #2575fc);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
    }

    #loginCard {
      width: 100%;
      max-width: 400px;
      padding: 2rem;
      background-color: white;
      border-radius: 1rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
      text-align: center;
    }

    #appContent {
      display: none;
    }

    body.dark-mode {
  background: linear-gradient(to right, #121212, #1f1f1f);
  color: #e0e0e0;
}

body.dark-mode .card,
body.dark-mode #loginCard,
body.dark-mode .modal-content,
body.dark-mode .offcanvas {
  background-color: #2c2c2c;
  color: #e0e0e0;
}

body.dark-mode .btn-primary {
  background-color: #8e44ad;
}

body.dark-mode .btn-primary:hover {
  background-color: #732d91;
}

body.dark-mode .offcanvas-title,
body.dark-mode .offcanvas-body,
body.dark-mode h2,
body.dark-mode h3,
body.dark-mode .card-title,
body.dark-mode .card-text,
body.dark-mode label {
  color: #e0e0e0;
}

body.dark-mode .list-group-item {
  background-color: #333;
  color: #e0e0e0;
  border-color: #444;
}

body.dark-mode .form-control {
  background-color: #444;
  color: #fff;
  border-color: #666;
}

body.dark-mode .form-control::placeholder {
  color: #bbb;
}

body.dark-mode .modal-header {
  background-color: #444;
  color: #fff;
}

body.dark-mode .navbar {
  background-color: #1a1a1a !important;
}

body.dark-mode .btn-outline-light {
  border-color: #ccc;
  color: #ccc;
}

  </style>
</head>
<body>

<div id="appContent">

  <!-- NAVBAR -->
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
        <form class="d-flex me-3">
          <input class="form-control me-2" type="text" placeholder="Search">
          <button class="btn btn-primary" type="submit">Search</button>
        </form>
        <button class="btn btn-outline-light me-2" onclick="toggleDarkMode()">🌙</button>
        <button class="btn p-0 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileSidebar">
          <img src="images.png" class="rounded-circle" alt="User Avatar" style="width: 40px; height: 40px;">
        </button>
      </div>
    </div>
  </nav>

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

  <!-- PROFILE SIDEBAR -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="profileSidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">My profile</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <div class="card text-center">
        <div class="card-body">
          <img src="https://via.placeholder.com/100" class="rounded-circle mb-3" alt="User Avatar">
          <h5 class="card-title" id="profileUsername">Username</h5>
          <p class="card-text" id="profileEmail">email@example.com</p>
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
          <label>Username</label>
          <input type="text" id="editUsername" class="form-control mb-2">
          <label>New Password</label>
          <input type="password" id="editPassword" class="form-control mb-2">
          <label>Profile Image URL</label>
          <input type="text" id="editProfilePic" class="form-control mb-2">
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" onclick="updateProfile()">Save</button>
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
          <img id="viewProfileAvatar" class="rounded-circle mb-3" style="width: 100px; height: 100px;">
          <h5 id="viewProfileName"></h5>
          <p id="viewProfileEmail"></p>
        </div>
      </div>
    </div>
  </div>

  <!-- SIGN UP MODAL -->
<div class="modal fade" id="signupModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Εγγραφή Χρήστη</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>Username</label>
        <input type="text" id="signupUsername" class="form-control mb-2" required>
        <label>Email</label>
        <input type="email" id="signupEmail" class="form-control mb-2" required>
        <label>Password</label>
        <input type="password" id="signupPassword" class="form-control mb-2" required>
        <div class="d-grid gap-2 mt-3">
          <button class="btn btn-success" onclick="handleSignup()">Εγγραφή</button>
        </div>
        <div class="text-center mt-3">
          <small>Έχεις ήδη λογαριασμό; <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Σύνδεση</a></small>
        </div>
      </div>
    </div>
  </div>
</div>

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
</div>

<!-- LOGIN -->
<div id="loginSection">
  <div id="loginCard" class="card">
    <h3 class="mb-3">Welcome to Melofy</h3>
    <input type="text" id="loginUsername" class="form-control" placeholder="Username">
    <input type="password" id="loginPassword" class="form-control" placeholder="Password">
    <div class="d-grid gap-2 mt-3">
      <button class="btn btn-primary" onclick="fakeLogin()">Login</button>
    </div>
    <div class="text-center mt-3">
     <small>Δεν έχεις λογαριασμό; 
     <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal">Εγγραφή</a>
     </small>
    </div>
  </div>
</div>

<script>
  let userProfile = {
    username: "testuser",
    email: "test@example.com",
    password: "1234",
    avatar: "https://via.placeholder.com/100",
    favorites: []
  };

  let playlists = [];

  function fakeLogin() {
    const u = document.getElementById("loginUsername").value.trim();
    const p = document.getElementById("loginPassword").value.trim();

    if (u === "testuser" && p === "1234") {
      document.getElementById("loginSection").style.display = "none";
      document.getElementById("appContent").style.display = "block";
      refreshProfileUI();
    } else {
      alert("Invalid credentials. Use: testuser / 1234");
    }
  }

  function logout() {
    document.getElementById("loginSection").style.display = "flex";
    document.getElementById("appContent").style.display = "none";
  }

  function refreshProfileUI() {
    document.getElementById("profileUsername").textContent = userProfile.username;
    document.getElementById("profileEmail").textContent = userProfile.email;
    document.querySelector("#profileSidebar img").src = userProfile.avatar;
  }

  function updateProfile() {
    const newName = document.getElementById("editUsername").value.trim();
    const newPass = document.getElementById("editPassword").value.trim();
    const newAvatar = document.getElementById("editProfilePic").value.trim();

    if (newName) userProfile.username = newName;
    if (newPass) userProfile.password = newPass;
    if (newAvatar) userProfile.avatar = newAvatar;

    refreshProfileUI();

    const modal = bootstrap.Modal.getInstance(document.getElementById("manageProfileModal"));
    modal.hide();
    alert("Profile updated!");
  }

  function viewProfile() {
    document.getElementById("viewProfileAvatar").src = userProfile.avatar;
    document.getElementById("viewProfileName").textContent = userProfile.username;
    document.getElementById("viewProfileEmail").textContent = userProfile.email;

    const modal = new bootstrap.Modal(document.getElementById("viewProfileModal"));
    modal.show();
  }

  function goToPlaylists() {
    document.getElementById("homeSection").classList.add("d-none");
    document.getElementById("playlistsSection").classList.remove("d-none");
    document.getElementById("favoritesSection").classList.add("d-none");
    renderPlaylists();
  }

  function goHome() {
    document.getElementById("homeSection").classList.remove("d-none");
    document.getElementById("playlistsSection").classList.add("d-none");
    document.getElementById("favoritesSection").classList.add("d-none");
  }

  function showFavorites() {
    document.getElementById("homeSection").classList.add("d-none");
    document.getElementById("playlistsSection").classList.add("d-none");
    document.getElementById("favoritesSection").classList.remove("d-none");

    const favList = document.getElementById("favoritesList");
    favList.innerHTML = "";

    if (userProfile.favorites.length === 0) {
      favList.innerHTML = `<li class="list-group-item text-muted">No favorite songs yet.</li>`;
      return;
    }

    userProfile.favorites.forEach(song => {
      const li = document.createElement("li");
      li.className = "list-group-item d-flex justify-content-between align-items-center";
      li.innerHTML = `
        <span>🎵 ${song}</span>
        <button class="btn btn-sm btn-outline-danger" onclick="toggleFavorite('${song}')">💔 Remove</button>
      `;
      favList.appendChild(li);
    });
  }

  function addPlaylist() {
    const name = document.getElementById("newPlaylistName").value.trim();
    if (!name) return alert("Please enter a playlist name.");
    playlists.push({ name, songs: [] });
    document.getElementById("newPlaylistName").value = "";
    renderPlaylists();
  }

  function addSong(index) {
    const title = prompt("Enter song title:");
    if (title) {
      playlists[index].songs.push(title);
      renderPlaylists();
    }
  }

  function deletePlaylist(index) {
    if (confirm("Delete this playlist?")) {
      playlists.splice(index, 1);
      renderPlaylists();
    }
  }

  function deleteSong(pIndex, sIndex) {
    playlists[pIndex].songs.splice(sIndex, 1);
    renderPlaylists();
  }

  function toggleFavorite(song) {
    const i = userProfile.favorites.indexOf(song);
    if (i >= 0) userProfile.favorites.splice(i, 1);
    else userProfile.favorites.push(song);
    renderPlaylists();
  }

  function renderPlaylists() {
    const container = document.getElementById("playlistsContainer");
    container.innerHTML = "";

    playlists.forEach((pl, i) => {
      const card = document.createElement("div");
      card.className = "card mb-3";

      const header = document.createElement("div");
      header.className = "card-header d-flex justify-content-between align-items-center";
      header.innerHTML = `<strong>${pl.name}</strong>
        <div>
          <button class="btn btn-sm btn-outline-primary me-2" onclick="addSong(${i})">➕</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deletePlaylist(${i})">🗑️</button>
        </div>`;

      const list = document.createElement("ul");
      list.className = "list-group list-group-flush";

      if (pl.songs.length === 0) {
        list.innerHTML = `<li class="list-group-item text-muted">⛔ No songs yet.</li>`;
      } else {
        pl.songs.forEach((song, sIndex) => {
          const li = document.createElement("li");
          li.className = "list-group-item d-flex justify-content-between align-items-center";
          li.innerHTML = `
            <span>🎵 ${song}</span>
            <div>
              <button class="btn btn-sm btn-outline-danger me-2" onclick="toggleFavorite('${song}')">
              ${userProfile.favorites.includes(song) ? '💔' : '❤️'}
              </button>
              <button class="btn btn-sm btn-danger" onclick="deleteSong(${i}, ${sIndex})">❌</button>
            </div>`;
          list.appendChild(li);
        });
      }

      card.appendChild(header);
      card.appendChild(list);
      container.appendChild(card);
    });
  }

  function toggleDarkMode() {
  const isDark = document.body.classList.toggle("dark-mode");
  localStorage.setItem("darkMode", isDark ? "on" : "off");
}

// Εφαρμόζουμε dark mode αν ήταν ενεργό στην προηγούμενη επίσκεψη
window.addEventListener("DOMContentLoaded", () => {
  if (localStorage.getItem("darkMode") === "on") {
    document.body.classList.add("dark-mode");
  }
});

// Αποθήκευση χρηστών στο localStorage
let registeredUsers = JSON.parse(localStorage.getItem("melofyUsers")) || [
  { username: "testuser", password: "1234", email: "test@example.com", avatar: "https://via.placeholder.com/100" }
];

function handleSignup() {
  const username = document.getElementById("signupUsername").value.trim();
  const email = document.getElementById("signupEmail").value.trim();
  const password = document.getElementById("signupPassword").value.trim();

  if (!username || !email || !password) return alert("Συμπλήρωσε όλα τα πεδία.");

  const exists = registeredUsers.find(u => u.username === username);
  if (exists) return alert("Το όνομα χρήστη υπάρχει ήδη.");

  const newUser = { username, email, password, avatar: "https://via.placeholder.com/100" };
  registeredUsers.push(newUser);
  localStorage.setItem("melofyUsers", JSON.stringify(registeredUsers));

  alert("Η εγγραφή ήταν επιτυχής! Μπορείς τώρα να συνδεθείς.");
  bootstrap.Modal.getInstance(document.getElementById("signupModal")).hide();
}

function fakeLogin() {
  const u = document.getElementById("loginUsername").value.trim();
  const p = document.getElementById("loginPassword").value.trim();

  const user = registeredUsers.find(x => x.username === u && x.password === p);
  if (user) {
    userProfile = { ...user, favorites: [] };
    document.getElementById("loginSection").style.display = "none";
    document.getElementById("appContent").style.display = "block";
    refreshProfileUI();
  } else {
    alert("Λάθος στοιχεία. Δοκίμασε ξανά.");
  }
}


</script>

</body>
</html>
