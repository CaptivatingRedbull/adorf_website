<?php
session_start();

// Datenbankverbindungsdaten
$dsn    = "mysql:host=localhost;dbname=adorf_website;charset=utf8";
$dbUser = "praxisblockDB";
$dbPass = "kcntmXThr9y3XhCZwGA.";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}

// Hier laden wir alle öffentlichen Downloads (is_standard = 1),
// oder falls deine Logik anders ist: Lade alle "public downloads".
$stmt = $pdo->prepare("SELECT * FROM downloads WHERE is_standard = 1 ORDER BY upload_date DESC");
$stmt->execute();
$downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Öffentliche Downloads</title>
    <!-- Gleiche CSS-Datei wie deine anderen Seiten -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<!-- HEADER-BANNER (wie auf index.php & login.php) -->
<header>
    <div class="header-container">
        <img src="images/logo.jpg" alt="Landratsamt Ansbach Logo" class="logo">
        <nav>
            <ul>
                <li><a href="index.php">Startseite</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="form-container">
    <h2>Öffentliche Downloads</h2>
    <?php if (count($downloads) > 0): ?>
        <table class="table">
            <thead>
            <tr>
                <th>Dateiname</th>
                <th>Hochgeladen am</th>
                <th>Download</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($downloads as $dl): ?>
                <tr>
                    <td><?php echo htmlspecialchars($dl['file_name']); ?></td>
                    <td><?php echo htmlspecialchars($dl['upload_date']); ?></td>
                    <td>
                        <!-- Annahme: Dateien liegen in /files/downloads/ -->
                        <a href="files/downloads/<?php echo htmlspecialchars($dl['file_path']); ?>" download>
                            Herunterladen
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Keine öffentlichen Downloads vorhanden.</p>
    <?php endif; ?>
</div>

<!-- Optional: Footer -->
<footer>
    <div class="container">
        <p>&copy; 2025 Stadt Adorf. Alle Rechte vorbehalten. | <a href="impressum.php">Impressum</a></p>
    </div>
</footer>
</body>
</html>
