<?php
// Initialisierung: Array für Artikel
$articles = [];

// Überprüfen, ob das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Eingabedaten aus dem Formular
    $title = htmlspecialchars($_POST['title']); // Titel des Artikels
    $image = 'images/' . basename($_FILES['image']['name']); // Pfad des Bildes
    $imageTmp = $_FILES['image']['tmp_name']; // Temporärer Bildpfad
    $content = htmlspecialchars($_POST['content']); // Inhalt des Artikels

    // Bild hochladen
    if (move_uploaded_file($imageTmp, $image)) {
        echo "<h2>Artikel erfolgreich erstellt</h2>";
    } else {
        echo "<h2>Fehler beim Hochladen des Bildes</h2>";
    }

    // Artikel in das Array einfügen
    $articles[] = [
        'title' => $title,
        'image' => $image,
        'content' => $content
    ];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neuen Artikel erstellen</title>
    <style>
        /* Stil für das Formular und die Anzeige */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input[type="text"], input[type="file"], textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
        }
        textarea {
            height: 150px;
        }
        .news-article-layout {
            border: 1px solid #ddd;
            padding: 20px;
            margin-top: 30px;
        }
        .article-header img {
            width: 100%;
            max-width: 400px;
        }
        .article-body p {
            font-size: 16px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <h1>Neuen Artikel erstellen</h1>

    <!-- Formular zur Artikel-Erstellung -->
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Überschrift:</label>
        <input type="text" name="title" id="title" required><br><br>
        
        <label for="image">Bild hochladen:</label>
        <input type="file" name="image" id="image" accept="image/*" required><br><br>
        
        <label for="content">Text:</label>
        <textarea name="content" id="content" rows="6" required></textarea><br><br>
        
        <input type="submit" value="Artikel erstellen">
    </form>

    <hr>

    <h2>Erstellte Artikel:</h2>

    <!-- Anzeige der Artikel -->
    <?php if (!empty($articles)): ?>
        <?php foreach ($articles as $article): ?>
            <article class="news-article-layout">
                <div class="article-header">
                    <h2><?php echo $article['title']; ?></h2>
                    <img src="<?php echo $article['image']; ?>" alt="<?php echo $article['title']; ?>" class="article-image-small">
                </div>
                <div class="article-body">
                    <p><?php echo $article['content']; ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Keine Artikel vorhanden.</p>
    <?php endif; ?>
</body>
</html>
