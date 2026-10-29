<?php
/**
 * Wspólny nagłówek strony
 * Rola: Frontend Developer
 */
if (!isset($pageTitle)) {
    $pageTitle = 'CineHub - Baza Filmów i Seriali';
}
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | CineHub</title>
    <!-- Główne style CSS przygotowane przez Frontend Developera -->
    <link rel="stylesheet" href="frontend/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">🎬</span>
                <span class="logo-text">Cine<span>Hub</span></span>
                <span class="logo-badge">INF.03</span>
            </a>
            <nav class="nav-links">
                <a href="index.php" class="nav-link <?= $currentScript === 'index.php' || $currentScript === 'film.php' ? 'active' : '' ?>">
                    🎞️ Filmy
                </a>
                <a href="actors.php" class="nav-link <?= $currentScript === 'actors.php' || $currentScript === 'actor.php' ? 'active' : '' ?>">
                    🎭 Aktorzy
                </a>
                <a href="add_movie.php" class="nav-link <?= $currentScript === 'add_movie.php' ? 'active' : '' ?>">
                    ➕ Dodaj Film
                </a>
                <a href="add_actor.php" class="nav-link <?= $currentScript === 'add_actor.php' ? 'active' : '' ?>">
                    ➕ Dodaj Aktora
                </a>
                <a href="assign_actor.php" class="nav-link <?= $currentScript === 'assign_actor.php' ? 'active' : '' ?>">
                    🔗 Przypisz Rolę
                </a>
            </nav>
        </div>
    </header>

    <main class="container main-content">
        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                <span class="alert-icon"><?= $flash['type'] === 'success' ? '✅' : '⚠️' ?></span>
                <div class="alert-body">
                    <?= e($flash['message']) ?>
                </div>
            </div>
        <?php endif; ?>
