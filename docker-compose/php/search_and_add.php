<?php
session_start();

// Έλεγχος σύνδεσης
if (!isset($_SESSION['email'])) {
    echo "🔒 Πρέπει να είστε συνδεδεμένος.";
    exit();
}

$email = $_SESSION['email'];
$video_id = "";
$title = "Temporary title";
$thumbnail = "";

// Αν υπάρχει υποβληθείσα αναζήτηση
if (isset($_GET['query'])) {
    $query = trim($_GET['query']);

    // Αν είναι YouTube link ➜ παίρνουμε video_id
    if (preg_match("/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/", $query, $matches)) {
        $video_id = $matches[1];
        $thumbnail = "https://img.youtube.com/vi/$video_id/hqdefault.jpg";
    }
    // Αλλιώς, εδώ μπορείς να προσθέσεις λειτουργία αναζήτησης με YouTube API
}

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Αναζήτηση και Αγαπημένα</title>
    <style>
        body {
            font-family: Arial;
            max-width: 700px;
            margin: auto;
            padding: 20px;
        }
        input, button {
            padding: 8px;
            margin-top: 10px;
        }
        iframe {
            margin-top: 20px;
            width: 100%;
            max-width: 560px;
            height: 315px;
        }
        .msg {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<h2>🎵 Καλωσήρθες, <?php echo htmlspecialchars($email); ?>!</h2>

<!-- Φόρμα Αναζήτησης -->
<form method="GET" action="search_and_add.php">
    <label>🔍 Εισάγετε YouTube link ή video ID:</label><br>
    <input type="text" name="query" placeholder="https://www.youtube.com/watch?v=...">
    <button type="submit">Αναζήτηση</button>
</form>

<?php if ($video_id): ?>
    <!-- Προεπισκόπηση Βίντεο -->
    <h3>🎬 Προεπισκόπηση Βίντεο</h3>
    <iframe src="https://www.youtube.com/embed/<?php echo $video_id; ?>" frameborder="0" allowfullscreen></iframe>
    
    <!-- Κουμπί Αποθήκευσης -->
    <br>
    <button onclick="saveFavorite('<?php echo $video_id; ?>', '<?php echo addslashes($title); ?>', '<?php echo $thumbnail; ?>')">💾 Προσθήκη στα Αγαπημένα</button>
    <div id="msg" class="msg"></div>
<?php endif; ?>

<script>
function saveFavorite(video_id, title, thumbnail) {
    const formData = new FormData();
    formData.append('video_id', video_id);
    formData.append('title', title);
    formData.append('thumbnail', thumbnail);

    fetch('save_favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById('msg').innerText = data;
    })
    .catch(err => {
        document.getElementById('msg').innerText = '❌ Σφάλμα.';
        console.error(err);
    });
}
</script>

</body>
</html>
