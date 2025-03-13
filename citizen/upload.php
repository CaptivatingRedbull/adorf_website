<?php
// Nur eingeloggte Bürger dürfen hier zugreifen
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'citizen') {
    header("Location: ../login.php");
    exit;
}

$citizen = $_SESSION['user'];

// Datenbankverbindung
$dsn    = "mysql:host=localhost;dbname=adorf_website;charset=utf8";
$dbUser = "praxisblockDB";
$dbPass = "kcntmXThr9y3XhCZwGA.";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}

$message = '';
$error   = '';

// Prüfen, ob ein Upload gelöscht werden soll
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    // Löschvorgang nur bei Einträgen, die dem Bürger gehören
    $stmt = $pdo->prepare("DELETE FROM uploads WHERE id = :id AND citizen_id = :citizen_id");
    if ($stmt->execute([':id' => $deleteId, ':citizen_id' => $citizen['id']]) && $stmt->rowCount() > 0) {
        $message = "Upload (ID: $deleteId) wurde entfernt.";
    } else {
        $error = "Konnte den Upload nicht entfernen oder Eintrag existiert nicht.";
    }
}

// Mitarbeiterliste laden, damit der Bürger aus einer Select-Liste wählen kann.
$stmt = $pdo->prepare("
    SELECT id, username, first_name, last_name
    FROM users
    WHERE role IN ('employee','admin')
    ORDER BY username
");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = (int)($_POST['employee_id'] ?? 0);

    // Datei vorhanden?
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Bitte wählen Sie eine Datei aus.";
    }
    // Mitarbeiter-ID validieren
    elseif ($employeeId <= 0) {
        $error = "Bitte wählen Sie einen Mitarbeiter aus.";
    } else {
        // Dateityp prüfen
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($_FILES['file']['type'], $allowedTypes)) {
            $error = "Nur PDF, JPG oder PNG sind erlaubt.";
        } else {
            // Datei hochladen
            $uploadDir = __DIR__ . '/../files/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $origName   = basename($_FILES['file']['name']);
            $newName    = time() . '_' . $origName;  // Eindeutiger Dateiname
            $targetPath = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                // In DB eintragen
                $stmtUp = $pdo->prepare("
                    INSERT INTO uploads (file_name, file_path, citizen_id, target_employee) 
                    VALUES (:fileName, :filePath, :citizenId, :empId)
                ");
                $stmtUp->execute([
                    ':fileName'    => $origName,
                    ':filePath'    => $newName,
                    ':citizenId'   => $citizen['id'],
                    ':empId'       => $employeeId
                ]);
                $message = "Datei erfolgreich hochgeladen und zugewiesen.";
            } else {
                $error = "Fehler beim Hochladen der Datei.";
            }
        }
    }
}

// Alle Uploads des aktuellen Bürgers laden
$stmt = $pdo->prepare("
    SELECT u.*, us.username AS employee_username
    FROM uploads u
    JOIN users us ON u.target_employee = us.id
    WHERE u.citizen_id = :citizen_id
    ORDER BY u.upload_date DESC
");
$stmt->execute([':citizen_id' => $citizen['id']]);
$uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Datei Upload</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="form-container">
    <h2>Datei an Mitarbeiter senden</h2>

    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="../dashboard.php?page=upload" method="post" enctype="multipart/form-data">
        <label for="employee_id">Mitarbeiter wählen:</label>
        <select name="employee_id" id="employee_id" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($employees as $emp): ?>
                <?php
                $displayEmp = $emp['username'];
                if ($emp['first_name'] || $emp['last_name']) {
                    $displayEmp .= ' (' . $emp['first_name'] . ' ' . $emp['last_name'] . ')';
                }
                ?>
                <option value="<?php echo $emp['id']; ?>">
                    <?php echo htmlspecialchars($displayEmp); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="file">Datei (PDF, JPG, PNG):</label>
        <input type="file" name="file" id="file" accept=".pdf,image/jpeg,image/png" required>

        <button type="submit" class="btn">Hochladen</button>
    </form>

    <!-- Tabelle mit allen vorhandenen Uploads des Bürgers -->
    <?php if (count($uploads) > 0): ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Dateiname</th>
                <th>Hochgeladen am</th>
                <th>Zugewiesen an</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($uploads as $upload): ?>
                <tr>
                    <td><?php echo $upload['id']; ?></td>
                    <td><?php echo htmlspecialchars($upload['file_name']); ?></td>
                    <td><?php echo htmlspecialchars($upload['upload_date']); ?></td>
                    <td><?php echo htmlspecialchars($upload['employee_username']); ?></td>
                    <td>
                        <a class="delete-link"
                           href="../dashboard.php?page=upload&amp;delete_id=<?php echo $upload['id']; ?>"
                           onclick="return confirm('Diesen Upload wirklich löschen?');">
                            Löschen
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Keine Uploads vorhanden.</p>
    <?php endif; ?>
</div>
</body>
</html>
