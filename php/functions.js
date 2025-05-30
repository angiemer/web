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
            document.getElementById("playlistsSection").classList.add("d-none");
            document.getElementById("favoritesSection").classList.add("d-none");
            // refreshProfileUI();

            // const profileForm = document.getElementById("profileUpdateForm");
            //     profileForm.addEventListener("submit", async (event) => {
            //         event.preventDefault();
            //         await updateProfile();
            //     });
        });

        function goHome() {
            document.getElementById("homeSection").classList.remove("d-none");
            document.getElementById("playlistsSection").classList.add("d-none");
            document.getElementById("favoritesSection").classList.add("d-none");
            document.getElementById("videos_section").classList.remove("d-none");
        }

        function goToPlaylists() {
            document.getElementById("homeSection").classList.add("d-none");
            document.getElementById("playlistsSection").classList.remove("d-none");
            document.getElementById("favoritesSection").classList.add("d-none");
            document.getElementById("videos_section").classList.add("d-none");

            //renderPlaylists();
        }

        function showFavorites() {
            document.getElementById("homeSection").classList.add("d-none");
            document.getElementById("playlistsSection").classList.add("d-none");
            document.getElementById("favoritesSection").classList.remove("d-none");
            document.getElementById("videos_section").classList.add("d-none");
        }


        function viewProfile() {
            document.getElementById("viewProfileAvatar").src = userAvatar || '';
            document.getElementById("viewProfileName").textContent = (firstName || '') + " " + (lastName || '');
            document.getElementById("viewProfileUsername").textContent = userUsername || '';
            document.getElementById("viewProfileEmail").textContent = userEmail || '';
            
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
                    userProfile.avatar = newAvatar || '';
                    
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

        function logout() {
            window.location.href = 'logout.php';
        }

        function search(){
            document.getElementById('result').style.display = 'block';
            alert("sindie search funcrion");
        }

        