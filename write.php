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
    <link rel="stylesheet" href="css/styles.css">
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
