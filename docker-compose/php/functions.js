 // Αρχικοποίηση δεδομένων
        let userProfile = "<?php echo json_encode($userProfile); ?>";
        let playlists = [];

        function toggleDarkMode() {
            const isDark = document.body.classList.toggle("dark-mode");
            localStorage.setItem("darkMode", isDark ? "on" : "off");
        }

        window.addEventListener("DOMContentLoaded", () => {
            if (localStorage.getItem("darkMode") === "on") {
                document.body.classList.add("dark-mode");
            }
            refreshProfileUI();

        const profileForm = document.getElementById("profileUpdateForm");
            profileForm.addEventListener("submit", async (event) => {
                event.preventDefault();
                await updateProfile();
            });
        });

        function goHome() {
            document.getElementById("homeSection").classList.remove("d-none");
            document.getElementById("playlistsSection").classList.add("d-none");
            document.getElementById("favoritesSection").classList.add("d-none");
        }

        function goToPlaylists() {
            document.getElementById("homeSection").classList.add("d-none");
            document.getElementById("playlistsSection").classList.remove("d-none");
            document.getElementById("favoritesSection").classList.add("d-none");
            renderPlaylists();
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

        function refreshProfileUI() {
            document.getElementById("profileUsername").textContent = userProfile.username;
            document.getElementById("profileName").textContent = (userProfile.first_name || '') + " " + (userProfile.last_name || '');
            document.getElementById("profileEmail").textContent = userProfile.email;
            const avatarEls = document.querySelectorAll(".rounded-circle");
            avatarEls.forEach(el => el.src = userProfile.avatar);
            //showFavorites();
        }

        function viewProfile() {
            document.getElementById("viewProfileAvatar").src = userProfile.avatar || 'https://via.placeholder.com/100.png';
            document.getElementById("viewProfileName").textContent = (userProfile.first_name || '') + " " + (userProfile.last_name || '');
            document.getElementById("viewProfileUsername").textContent = userProfile.username || '';
            document.getElementById("viewProfileEmail").textContent = userProfile.email || '';
            
            const modal = new bootstrap.Modal(document.getElementById("viewProfileModal"));
            modal.show();
        }

        async function updateProfile() {
            const errorDiv = document.getElementById("profileError");
            errorDiv.classList.add("d-none");

            const newFirstName = document.getElementById("editFirstName").value.trim();
            const newLastName = document.getElementById("editLastName").value.trim();
            const newUsername = document.getElementById("editUsername").value.trim();
            const newEmail = document.getElementById("editEmail").value.trim();
            const newPassword = document.getElementById("editPassword").value.trim();
            const newAvatar = document.getElementById("editProfilePic").value.trim();

            // Client-side validation
            if (!newFirstName) {
                errorDiv.textContent = "First name is required.";
                errorDiv.classList.remove("d-none");
                return;
            }
            if (!newLastName) {
                errorDiv.textContent = "Last name is required.";
                errorDiv.classList.remove("d-none");
                return;
            }
            if (!newUsername) {
                errorDiv.textContent = "Username is required.";
                errorDiv.classList.remove("d-none");
                return;
            }
            if (!newEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
                errorDiv.textContent = "Please enter a valid email address"
                errorDiv.classList.remove("d-none");
                return;
            }
            if (newPassword && newPassword.length < 6) {
                errorDiv.textContent = "Password must be at least 6 characters long.";
                errorDiv.classList.remove("d-none");
                return;
            }
            if (newAvatar && !/^https?:\/\/.*\.(png|jpg|jpeg|gif)$/i.test(newAvatar)) {
                errorDiv.textContent = "Please enter a valid image URL (png, jpg, jpeg, gif).";
                errorDiv.classList.remove("d-none");
                return;
            }

            try {
                const form = document.getElementById("profileUpdateForm");
                const formData = new FormData(form);
                
                const response = await fetch(form.action, {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();
                console.log("Server response:", result);

                if (result.success) {
                    userProfile.first_name = newFirstName;
                    userProfile.last_name = newLastName;
                    userProfile.username = newUsername;
                    userProfile.email = newEmail;
                    userProfile.avatar = newAvatar || 'https://via.placeholder.com/100.png';
                    
                    refreshProfileUI();
                    const modal = bootstrap.Modal.getInstance(document.getElementById("manageProfileModal"));
                    modal.hide();
                    alert("Το προφίλ ενημερώθηκε επιτυχώς!");
                } else {
                    errorDiv.textContent = "Σφάλμα: " + result.error;
                    errorDiv.classList.remove("d-none");
                }
            } catch (error) {
                console.error("Fetch error:", error);
                errorDiv.textContent = "Σφάλμα σύνδεσης με τον server: " + error.message;
                errorDiv.classList.remove("d-none");
            }
        }

        function search() {
            const query = document.getElementById("searchInput").value.trim().toLowerCase();
            const searchResultsContainer = document.getElementById("searchResults");
            searchResultsContainer.innerHTML = "";

            if (!query) {
                searchResultsContainer.innerHTML = `<p class="text-muted">Please enter a search term.</p>`;
                return;
            }

            let results = [];

            playlists.forEach((playlist, pIndex) => {
                playlist.songs.forEach((song, sIndex) => {
                    if (song.toLowerCase().includes(query)) {
                        results.push({
                            playlistName: playlist.name,
                            songTitle: song,
                            pIndex,
                            sIndex
                        });
                    }
                });
            });

            if (results.length === 0) {
                searchResultsContainer.innerHTML = `<p class="text-muted">No songs found matching "${query}".</p>`;
                return;
            }

            const list = document.createElement("ul");
            list.className = "list-group";

            results.forEach(result => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";
                li.innerHTML = `
                    <span><strong>${result.songTitle}</strong> <em>(in ${result.playlistName})</em></span>
                    <button class="btn btn-sm btn-outline-danger" onclick="toggleFavorite('${result.songTitle}')">
                        ${userProfile.favorites.includes(result.songTitle) ? '💔' : '❤️'}
                    </button>
                `;
                list.appendChild(li);
            });

            searchResultsContainer.appendChild(list);
        }


        function logout() {
            window.location.href = 'logout.php';
        }

        function search(){
            document.getElementById('result').style.display = 'block';
            alert("sindie search funcrion");
        }
