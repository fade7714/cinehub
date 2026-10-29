<?php
/**
 * Profil Aktora i Filmografia (CineHub)
 * Rola: Backend & Frontend Developer
 * Zgodność z INF.03 (Specs.md pkt 2.2)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$aktorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$dbError = null;
$aktor = null;
$filmografia = [];

if (!$aktorId) {
    setFlash('error', 'Nie wybrano prawidłowego identyfikatora aktora.');
    header('Location: actors.php');
    exit;
}

try {
    $pdo = getDbConnection();

    // Pobranie danych aktora
    $stmt = $pdo->prepare("SELECT * FROM aktorzy WHERE id = :id");
    $stmt->execute(['id' => $aktorId]);
    $aktor = $stmt->fetch();

    if (!$aktor) {
        setFlash('error', 'Aktor o podanym identyfikatorze nie istnieje w bazie.');
        header('Location: actors.php');
        exit;
    }

    // Pobranie filmografii aktora (relacja M:N)
    $filmografiaSql = "
        SELECT 
            f.id AS film_id,
            f.tytul,
            f.gatunek,
            f.rok_premiery,
            f.czas_trwania,
            f.plakat,
            o.rola,
            r.srednia_ocen
        FROM obsada o
        JOIN filmy f ON o.film_id = f.id
        LEFT JOIN (
            SELECT film_id, ROUND(AVG(ocena), 1) AS srednia_ocen
            FROM recenzje
            GROUP BY film_id
        ) r ON f.id = r.film_id
        WHERE o.aktor_id = :aktor_id
        ORDER BY f.rok_premiery DESC
    ";
    $filmografiaStmt = $pdo->prepare($filmografiaSql);
    $filmografiaStmt->execute(['aktor_id' => $aktorId]);
    $filmografia = $filmografiaStmt->fetchAll();

} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

$pageTitle = $aktor ? ($aktor['imie'] . ' ' . $aktor['nazwisko']) : 'Profil Aktora';
require_once __DIR__ . '/includes/header.php';
?>

<div class="back-nav">
    <a href="actors.php" class="btn btn-secondary btn-sm">
        &larr; Wróć do bazy aktorów
    </a>
</div>

<?php if ($dbError): ?>
    <div class="alert alert-error" role="alert">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">
            <strong>Błąd połączenia z bazą:</strong> <?= e($dbError) ?>
        </div>
    </div>
<?php elseif ($aktor): ?>

    <article class="movie-detail-view">
        <div class="detail-hero-card">
            <div class="detail-layout">
                <!-- Zdjęcie Aktora -->
                <div class="detail-poster-wrapper">
                    <?php 
                        $zdjecie = !empty($aktor['zdjecie']) ? $aktor['zdjecie'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=400&q=80';
                    ?>
                    <img 
                        src="<?= e($zdjecie) ?>" 
                        alt="<?= e($aktor['imie'] . ' ' . $aktor['nazwisko']) ?>" 
                        class="detail-poster"
                        style="aspect-ratio: 1/1; object-fit: cover; border-radius: var(--radius-lg);"
                    >
                </div>

                <!-- Dane Aktora -->
                <div class="detail-header-info">
                    <h1><?= e($aktor['imie'] . ' ' . $aktor['nazwisko']) ?></h1>

                    <div class="detail-chips-row">
                        <?php if (!empty($aktor['data_urodzenia'])): ?>
                            <span class="info-chip">🎂 Urodziny: <?= date('d.m.Y', strtotime($aktor['data_urodzenia'])) ?></span>
                        <?php endif; ?>
                        <span class="info-chip">🎬 Ról w bazie CineHub: <?= count($filmografia) ?></span>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">Biografia</div>
                        <p class="detail-synopsis">
                            <?= !empty($aktor['biografia']) ? nl2br(e($aktor['biografia'])) : 'Brak szczegółowej biografii dla tego artysty.' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sekcja Filmografii -->
        <section class="reviews-section">
            <div class="section-heading">
                <h2>Filmografia w Bazie CineHub (<?= count($filmografia) ?>)</h2>
            </div>

            <?php if (empty($filmografia)): ?>
                <div class="filter-card" style="text-align: center; padding: 40px;">
                    <p>Ten aktor nie ma jeszcze przypisanych ról w filmach.</p>
                </div>
            <?php else: ?>
                <div class="movie-grid">
                    <?php foreach ($filmografia as $film): ?>
                        <article class="movie-card">
                            <div class="movie-poster-box" style="padding-top: 120%;">
                                <?php 
                                    $plakat = !empty($film['plakat']) ? $film['plakat'] : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80';
                                ?>
                                <img src="<?= e($plakat) ?>" alt="Plakat: <?= e($film['tytul']) ?>" class="movie-poster-img" loading="lazy">
                                <div class="movie-badge-rating">
                                    ★ <?= $film['srednia_ocen'] !== null ? number_format((float)$film['srednia_ocen'], 1) : '–' ?>
                                </div>
                            </div>

                            <div class="movie-card-body">
                                <h3 class="movie-card-title">
                                    <a href="film.php?id=<?= (int)$film['film_id'] ?>">
                                        <?= e($film['tytul']) ?>
                                    </a>
                                </h3>

                                <div class="movie-card-meta">
                                    <span class="meta-chip">📅 <?= (int)$film['rok_premiery'] ?></span>
                                    <span class="meta-chip">🎭 <?= e($film['gatunek']) ?></span>
                                </div>

                                <p style="font-size: 0.95rem; margin-bottom: 14px; color: var(--gold);">
                                    Rola: <strong><?= e($film['rola']) ?></strong>
                                </p>

                                <div class="movie-card-footer">
                                    <span><?= (int)$film['czas_trwania'] ?> min</span>
                                    <a href="film.php?id=<?= (int)$film['film_id'] ?>" class="btn btn-secondary btn-sm">
                                        Szczegóły filmu &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </article>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

