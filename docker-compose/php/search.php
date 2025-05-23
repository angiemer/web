<?php
require_once "ggl.php";
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Search Results - Melofy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Αποτελέσματα αναζήτησης</h1>

    <?php if ($searchResponse): ?>
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
</body>
</html>
