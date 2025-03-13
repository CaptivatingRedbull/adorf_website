<?php
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

// Aktuell eingeloggter Mitarbeiter oder Admin
$staff = $_SESSION['user'];

// Meldungen
$messageStandard = ''; // Meldung beim Zuweisen eines Standarddownloads
$errorStandard   = '';

$messageUpload = '';   // Meldung beim Hochladen einer neuen Datei
$errorUpload   = '';

// 1. POST-Verarbeitung: Standard-Download zuweisen
if (isset($_POST['action']) && $_POST['action'] === 'assign_standard') {
    $downloadId = (int)($_POST['download_id'] ?? 0);
    $citizenId  = (int)($_POST['citizen_id']  ?? 0);

    if ($downloadId <= 0 || $citizenId <= 0) {
        $errorStandard = "Bitte sowohl einen Standarddownload als auch einen Bürger auswählen.";
    } else {
        // Standard-Download-Datensatz prüfen
        $stmt = $pdo->prepare("SELECT file_name, file_path FROM downloads WHERE id = :id AND is_standard = 1");
        $stmt->execute([':id' => $downloadId]);
        $standardDownload = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$standardDownload) {
            $errorStandard = "Der ausgewählte Download ist nicht (mehr) verfügbar oder kein Standarddownload.";
        } else {
            // Neuen Eintrag für den Bürger anlegen
            $stmt2 = $pdo->prepare("
                INSERT INTO downloads (file_name, file_path, assigned_to, uploaded_by, is_standard)
                VALUES (:file_name, :file_path, :assigned_to, :uploaded_by, 0)
            ");
            $stmt2->execute([
                ':file_name'    => $standardDownload['file_name'],
                ':file_path'    => $standardDownload['file_path'],
                ':assigned_to'  => $citizenId,
                ':uploaded_by'  => $staff['id']
            ]);
            $messageStandard = "Standard-Download wurde erfolgreich zugewiesen.";
        }
    }
}

// 2. POST-Verarbeitung: Neue Datei hochladen und zuweisen
if (isset($_POST['action']) && $_POST['action'] === 'upload_new') {
    $citizenId = (int)($_POST['citizen_id'] ?? 0);

    // Datei-Check
    if ($citizenId <= 0) {
        $errorUpload = "Bitte wählen Sie einen Bürger aus.";
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorUpload = "Bitte wählen Sie eine Datei aus.";
    } else {
        // Optional: Dateityp checken
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($_FILES['file']['type'], $allowedTypes)) {
            $errorUpload = "Nur PDF, JPG oder PNG sind erlaubt.";
        } else {
            // Datei speichern
            $uploadDir = __DIR__ . '/../files/downloads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $origName   = basename($_FILES['file']['name']);
            $newName    = time() . '_' . $origName;  // Eindeutiger Dateiname
            $targetPath = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                // In DB eintragen -> assigned_to = $citizenId, is_standard=0, uploaded_by = staff
                $stmt = $pdo->prepare("
                    INSERT INTO downloads (file_name, file_path, assigned_to, uploaded_by, is_standard)
                    VALUES (:file_name, :file_path, :assigned_to, :uploaded_by, 0)
                ");
                $stmt->execute([
                    ':file_name'    => $origName,
                    ':file_path'    => $newName,
                    ':assigned_to'  => $citizenId,
                    ':uploaded_by'  => $staff['id']
                ]);
                $messageUpload = "Neue Datei wurde hochgeladen und zugewiesen.";
            } else {
                $errorUpload = "Fehler beim Hochladen der Datei.";
            }
        }
    }
}

// Prüfen, ob ein Download gelöscht werden soll
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    // Löschvorgang nur bei Einträgen, die dem aktuellen Mitarbeiter/Admin zugewiesen wurden
    $stmt = $pdo->prepare("DELETE FROM downloads WHERE id = :id AND uploaded_by = :uploaded_by AND is_standard = 0");
    if ($stmt->execute([':id' => $deleteId, ':uploaded_by' => $staff['id']])) {
        if ($stmt->rowCount() > 0) {
            $messageUpload = "Download (ID: $deleteId) wurde erfolgreich gelöscht.";
        } else {
            $errorUpload = "Konnte den Download nicht löschen oder Eintrag existiert nicht.";
        }
    } else {
        $errorUpload = "Fehler beim Löschen des Downloads.";
    }
}

// Standarddownloads laden
$stmt = $pdo->query("SELECT id, file_name FROM downloads WHERE is_standard = 1 ORDER BY file_name");
$standardDownloads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Alle Bürger laden (Rolle 'citizen')
$stmt = $pdo->query("SELECT id, username, first_name, last_name FROM users WHERE role = 'citizen' ORDER BY username");
$citizens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Alle Downloads laden, die der aktuelle Mitarbeiter/Admin zugewiesen hat
$stmt = $pdo->prepare("
    SELECT d.id, d.file_name, d.upload_date, u.username AS citizen_username
    FROM downloads d
    JOIN users u ON d.assigned_to = u.id
    WHERE d.uploaded_by = :uploaded_by AND d.is_standard = 0
    ORDER BY d.upload_date DESC
");
$stmt->execute([':uploaded_by' => $staff['id']]);
$assignedDownloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Download bereitstellen</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<!-- Neue Datei hochladen und zuweisen -->
<div class="form-container">
    <h2>Neue Datei hochladen &amp; zuweisen</h2>
    <?php if ($messageUpload): ?>
        <p class="message"><?php echo htmlspecialchars($messageUpload); ?></p>
    <?php endif; ?>
    <?php if ($errorUpload): ?>
        <p class="error"><?php echo htmlspecialchars($errorUpload); ?></p>
    <?php endif; ?>

    <form action="../dashboard.php?page=provide_download" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_new">

        <label for="citizenSelect2">Bürger auswählen:</label>
        <select name="citizen_id" id="citizenSelect2" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($citizens as $c): ?>
                <?php
                $displayText = $c['username'];
                if ($c['first_name'] || $c['last_name']) {
                    $displayText .= " - " . $c['first_name'] . " " . $c['last_name'];
                }
                ?>
                <option value="<?php echo $c['id']; ?>">
                    <?php echo htmlspecialchars($displayText); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="file">Datei (PDF, JPG, PNG):</label>
        <input type="file" name="file" id="file" accept=".pdf,image/jpeg,image/png" required>

        <button type="submit">Hochladen &amp; zuweisen</button>
    </form>
</div>

<!-- Tabelle mit zugewiesenen Downloads -->
<div class="form-container">
    <h2>Zugewiesene Downloads</h2>
    <?php if (count($assignedDownloads) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dateiname</th>
                    <th>Zugewiesen an</th>
                    <th>Hochgeladen am</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignedDownloads as $download): ?>
                    <tr>
                        <td><?php echo $download['id']; ?></td>
                        <td><?php echo htmlspecialchars($download['file_name']); ?></td>
                        <td><?php echo htmlspecialchars($download['citizen_username']); ?></td>
                        <td><?php echo htmlspecialchars($download['upload_date']); ?></td>
                        <td>
                            <a class="delete-link"
                               href="../dashboard.php?page=provide_download&amp;delete_id=<?php echo $download['id']; ?>"
                               onclick="return confirm('Diesen Download wirklich löschen?');">
                                Löschen
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Keine zugewiesenen Downloads vorhanden.</p>
    <?php endif; ?>
</div>

</body>
</html>
