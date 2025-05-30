<?php
session_start();
ob_start(); // Start output buffering

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Make a connection with the Database    
require 'db.php';

// Validating if the user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header('Location: start.php');
//     exit();
// }

// Prepare query for playlist_songs
$playlistSongsQuery = "SELECT playlist_id, song_id FROM playlist_songs";

// Fetch all playlist songs
$playlistSongsStmt = $conn->prepare($playlistSongsQuery);
$playlistSongsStmt->execute();
$playlistSongsStmt->store_result();
$playlistSongsStmt->bind_result($playlist_id, $song_id);

$playlistSongs = [];
while ($playlistSongsStmt->fetch()) {
    $playlistSongs[] = [
        'playlist_id' => $playlist_id,
        'song_id' => $song_id,
    ];
}

// Generate YAML content
$yaml = "playlist_songs:\n";
foreach ($playlistSongs as $item) {
    $yaml .= "  - playlist_id: " . $item['playlist_id'] . "\n";
    $yaml .= "    song_id: " . $item['song_id'] . "\n";
}

// Defining the file path
// $file = '/var/www/html/yaml/playlist-songs-exported.yaml';

// // Save the YAML content to the file
// if (file_put_contents($file, $yaml) !== false) {
//     echo "YAML file successfully created.";
// } else {
//     echo "An error occurred while saving the YAML file.";
// }

// // Saving the YAML content to a file
// $file = 'playlist_songs_exported.yaml';
// if (file_put_contents($file, $yaml) === false) {
//     die('An error occurred while saving the YAML file.');
// }

// Output the YAML content directly to the browser within HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YAML Export</title>
    <style>
        body {
            font-family: monospace;
            background-color: #2e2e2e;
            color: #dcdcdc;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        pre {
            background-color: #1e1e1e;
            border: 1px solid #555;
            padding: 20px;
            max-width: 80%;
            overflow: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            color: #dcdcdc;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .key {
            color: #569cd6;
        }
        .value {
            color: #ce9178;
        }
        .string {
            color: #9cdcfe;
        }
    </style>
</head>
<body>
    <pre id="yamlContent"><?php echo htmlspecialchars($yaml, ENT_QUOTES, 'UTF-8'); ?></pre>
    <button id="downloadButton">Download YAML</button>
    <script>
        document.getElementById('downloadButton').addEventListener('click', function() {
            const yamlContent = `<?php echo addslashes($yaml); ?>`;
            const blob = new Blob([yamlContent], { type: 'application/x-yaml' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'playlist-songs-exported.yaml';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

        // Highlight YAML syntax
        function highlightYAML(yaml) {
            return yaml.replace(/^(\s*)([a-zA-Z_][a-zA-Z0-9_]*):(.*)$/gm, function(match, indent, key, value) {
                return indent + '<span class="key">' + key + '</span>:' + '<span class="value">' + value + '</span>';
            }).replace(/^(\s*)-(.*)$/gm, function(match, indent, item) {
                return indent + '-<span class="string">' + item + '</span>';
            });
        }

        document.getElementById('yamlContent').innerHTML = highlightYAML(document.getElementById('yamlContent').innerHTML);
    </script>
</body>
</html>
<?php
ob_end_flush(); // End output buffering and flush output
?>