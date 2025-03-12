<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stadt Adorf</title>
    <!-- Deine zentrale CSS-Datei -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<!-- Header mit Logo und Navigation -->
<header>
    <div class="header-container">
        <img src="images/logo.jpg" alt="Stadt Adorf Logo" class="logo">
        <nav>
            <ul>
                <li><a href="#hero">Startseite</a></li>
                <li><a href="#services">Leistungen</a></li>
                <li><a href="article.php">Aktuelles</a></li>
                <li><a href="#contact">Kontakt</a></li>
                <li><a href="downloads.php">Öffentliche Downloads</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- Hero-Bereich -->
<section id="hero">
    <div class="image-behind-text">
        <img src="images/logo.jpg" alt="Hintergrund" class="background-image">
        <h1>Willkommen bei der kreisfreien Stadt Adorf!</h1>
        p>Ihr zuverlässiger Partner in allen Verwaltungsangelegenheiten.</p>
        <a href="#services" class="btn">Mehr erfahren</a>
        <a href="login.php" class="btn">Login</a>
    </div>
</section>

<!-- Leistungs-Bereich -->
<section id="services">
    <h2>Unsere Leistungen</h2>
    <div class="container">
        <div class="cards">
            <div class="card">
                <h3>Bürgerdienste</h3>
                <p>Informationen zu An- und Ummeldungen, Ausweisen und mehr.</p>
                <a href="#">Mehr erfahren</a>
            </div>
            <div class="card">
                <h3>Wirtschaftsförderung</h3>
                <p>Beratung und Unterstützung für Unternehmen in Adorf.</p>
                <a href="#">Mehr erfahren</a>
            </div>
            <div class="card">
                <h3>Bau und Umwelt</h3>
                <p>Übersicht zu Planungs- und Genehmigungsverfahren.</p>
                <a href="#">Mehr erfahren</a>
            </div>
        </div>
    </div>
</section>

<!-- News-Bereich -->
<section id="news">
    <h2>Aktuelles</h2>
    <div class="container">
        <article>
            <h3>Bert Meier von seinem Hund Bello gebissen</h3>
            <div class="news-content">
                <img src="images/hund.jpg" alt="Hund Bello" class="news-image">
                <p>Bert Meier wurde gestern von seinem Hund Bello gebissen. Er wurde sofort ins Krankenhaus gebracht und behandelt. Glücklicherweise sind die Verletzungen nicht schwerwiegend.</p>
            </div>
            <a href="article.php">Zum Artikel</a>
        </article>
    </div>
</section>

<!-- Kontakt-Bereich -->
<section id="contact">
    <h2>Kontakt</h2>
    <div class="container">
        <p>
            Stadt Adorf<br>
            Musterstraße 1, 91522 Adorf<br>
            Telefon: 0981 123456
        </p>
        <form action="contact_process.php" method="post">
            <input type="text" name="name" placeholder="Ihr Name" required>
            <input type="email" name="email" placeholder="Ihre E-Mail" required>
            <textarea name="message" placeholder="Ihre Nachricht" required></textarea>
            <button type="submit">Senden</button>
        </form>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <p>&copy; 2025 Stadt Adorf. Alle Rechte vorbehalten. | <a href="impressum.php">Impressum</a></p>
    </div>
</footer>
</body>
</html>
