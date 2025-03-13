<?php

if (!isset($_SESSION['user'])) {
    // Kein Zugriff, wenn nicht eingeloggt
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];

// Meldungen
$error   = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = trim($_POST['old_password']);
    $newPassword = trim($_POST['new_password']);
    $confirm     = trim($_POST['confirm_password']);

    // 1) Validierung
    if (empty($oldPassword) || empty($newPassword) || empty($confirm)) {
        $error = "Bitte alle Felder ausfüllen.";
    } elseif ($newPassword !== $confirm) {
        $error = "Die neuen Passwörter stimmen nicht überein.";
    } else {
        // 2) Datenbank-Verbindung
        $dsn    = "mysql:host=localhost;dbname=adorf_website;charset=utf8";
        $dbUser = "praxisblockDB";
        $dbPass = "kcntmXThr9y3XhCZwGA.";

        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Datenbankfehler: " . $e->getMessage());
        }

        // 3) Prüfen, ob das alte Passwort korrekt ist
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :uid LIMIT 1");
        $stmt->execute([':uid' => $user['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['password'])) {
            $error = "Fehler: Benutzerkonto nicht gefunden.";
        } else {
            // Altes Passwort prüfen
            if (!password_verify($oldPassword, $row['password'])) {
                $error = "Das alte Passwort ist falsch.";
            } else {
                // 4) Neues Passwort hashen und speichern
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

                $upd = $pdo->prepare("UPDATE users SET password = :pw WHERE id = :uid");
                $upd->execute([
                    ':pw'  => $newHash,
                    ':uid' => $user['id']
                ]);

                // Optional: failed_logins auf 0 setzen
                $updFailed = $pdo->prepare("UPDATE users SET failed_logins = 0 WHERE id = :uid");
                $updFailed->execute([':uid' => $user['id']]);

                $message = "Passwort erfolgreich geändert.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Passwort ändern</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="form-container">
    <h2>Passwort ändern</h2>

    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="dashboard.php?page=change_password" method="post">
        <label for="old_password">Altes Passwort:</label>
        <input type="password" name="old_password" id="old_password" required>

        <label for="new_password">Neues Passwort:</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Neues Passwort wiederholen:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit" class="btn">Speichern</button>
    </form>
</div>
</body>
</html>