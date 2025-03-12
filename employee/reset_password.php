<?php


// Zugriff nur für Admins
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingegebener Wert (Username oder E-Mail)
    $searchTerm = trim($_POST['search_term']);

    if (empty($searchTerm)) {
        $error = "Bitte geben Sie einen Benutzernamen oder eine E-Mail ein.";
    } else {
        // Nutzer finden, der citizen ODER employee ist
        // (Wenn du auch Admin-Passwörter zurücksetzen möchtest, entferne das role-Filter.)
        $stmt = $pdo->prepare("
            SELECT * 
            FROM users
            WHERE (username = :term OR email = :term)
              AND (role = 'citizen' OR role = 'employee')
            LIMIT 1
        ");
        $stmt->execute([':term' => $searchTerm]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            $error = "Kein passender Bürger/Mitarbeiter gefunden (oder Rolle ist Admin).";
        } else {
            // Neues Einmal-Passwort generieren
            $randomPassword = bin2hex(random_bytes(4)); // 8 Hex-Zeichen
            $hashedPass     = password_hash($randomPassword, PASSWORD_DEFAULT);

            // Update in Datenbank
            $updStmt = $pdo->prepare("
                UPDATE users
                SET password = :pw, failed_logins = 0
                WHERE id = :uid
            ");
            $updStmt->execute([
                ':pw'  => $hashedPass,
                ':uid' => $targetUser['id']
            ]);

            $message = "Das Passwort für "
                . htmlspecialchars($targetUser['username'] ?? $targetUser['email'])
                . " (Rolle: " . htmlspecialchars($targetUser['role'])
                . ") wurde zurückgesetzt.<br>Neues Einmal-Passwort: <strong>"
                . htmlspecialchars($randomPassword) . "</strong>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Passwort zurücksetzen (Admin)</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="form-container">
    <h2>Passwort zurücksetzen (Bürger oder Mitarbeiter)</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="../dashboard.php?page=reset_password" method="post">
        <label for="search_term">
            Benutzername oder E-Mail des Nutzers:
        </label>
        <input type="text" name="search_term" id="search_term" required>

        <button type="submit" class="btn">Passwort zurücksetzen</button>
    </form>
</div>
</body>
</html>