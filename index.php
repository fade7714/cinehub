<?php
/**
 * Strona Główna - Katalog Filmów (CineHub)
 * Rola: Frontend Developer (integracja z Backendem)
 * Zgodność z INF.03
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Katalog Filmów';
$dbError = null;
$filmy = [];
$gatunki = [];

$szukaj = trim((string)($_GET['szukaj'] ?? ''));
$wybranyGatunek = trim((string)($_GET['gatunek'] ?? ''));
$sortowanie = trim((string)($_GET['sort'] ?? 'rok_desc'));

try {
    $pdo = getDbConnection();

    // Pobranie listy unikalnych gatunków do filtra
    $stmtGatunki = $pdo->query("SELECT DISTINCT gatunek FROM filmy ORDER BY gatunek ASC");
    $gatunki = $stmtGatunki->fetchAll(PDO::FETCH_COLUMN);

    // Budowanie zapytania analitycznego z agregacją bez iloczynu kartezjańskiego
    $sql = "
        SELECT 
            f.id,
            f.tytul,
            f.rezyser,
            f.gatunek,
            f.rok_premiery,
            f.czas_trwania,
            f.opis,
            f.plakat,
            COALESCE(o.liczba_aktorow, 0) AS liczba_aktorow,
            COALESCE(r.liczba_recenzji, 0) AS liczba_recenzji,
            r.srednia_ocen
        FROM filmy f
        LEFT JOIN (
            SELECT film_id, COUNT(DISTINCT aktor_id) AS liczba_aktorow
            FROM obsada
            GROUP BY film_id
        ) o ON f.id = o.film_id
        LEFT JOIN (
            SELECT film_id, COUNT(id) AS liczba_recenzji, ROUND(AVG(ocena), 1) AS srednia_ocen
            FROM recenzje
            GROUP BY film_id
        ) r ON f.id = r.film_id
        WHERE 1=1
    ";

    $params = [];

    if ($szukaj !== '') {
        $sql .= " AND (f.tytul LIKE :szukaj OR f.rezyser LIKE :szukaj OR f.opis LIKE :szukaj)";
        $params['szukaj'] = '%' . $szukaj . '%';
    }

    if ($wybranyGatunek !== '') {
        $sql .= " AND f.gatunek = :gatunek";
        $params['gatunek'] = $wybranyGatunek;
    }

    // Opcje sortowania
    switch ($sortowanie) {
        case 'ocena_desc':
            $sql .= " ORDER BY r.srednia_ocen DESC, f.rok_premiery DESC";
            break;
        case 'tytul_asc':
            $sql .= " ORDER BY f.tytul ASC";
            break;
        case 'rok_asc':
            $sql .= " ORDER BY f.rok_premiery ASC";
            break;
        case 'rok_desc':
        default:
            $sql .= " ORDER BY f.rok_premiery DESC, f.id DESC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $filmy = $stmt->fetchAll();

} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Sekcja Hero -->
<section class="hero-section">
    <h1 class="hero-title">Baza Filmowa CineHub</h1>
    <p class="hero-subtitle">
        Przeglądaj wyselekcjonowane tytuły, poznaj obsadę aktorską i sprawdzaj oceny społeczności.
    </p>
</section>

<?php if ($dbError): ?>
    <div class="alert alert-error" role="alert">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">
            <strong>Brak aktywnego połączenia z bazą danych:</strong><br>
            <code><?= e($dbError) ?></code><br><br>
            <em>Upewnij się, że usługa MySQL/MariaDB została uruchomiona, a struktura tabel została zaimportowana z pliku <code>database/schema.sql</code>.</em>
        </div>
    </div>
<?php else: ?>

    <!-- Pasek Filtrów i Wyszukiwania -->
    <div class="filter-card">
        <form method="GET" action="index.php" class="filter-form">
            <div class="search-input-group">
                <span class="search-icon">🔍</span>
                <input 
                    type="text" 
                    name="szukaj" 
                    class="form-control" 
                    placeholder="Wyszukaj tytuł, reżysera lub słowo z opisu..." 
                    value="<?= e($szukaj) ?>"
                    aria-label="Wyszukaj film"
                >
            </div>

            <div class="select-group">
                <select name="gatunek" class="form-control" aria-label="Wybierz gatunek">
                    <option value="">Wszystkie gatunki</option>
                    <?php foreach ($gatunki as $g): ?>
                        <option value="<?= e($g) ?>" <?= $wybranyGatunek === $g ? 'selected' : '' ?>>
                            <?= e($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="select-group">
                <select name="sort" class="form-control" aria-label="Sortowanie">
                    <option value="rok_desc" <?= $sortowanie === 'rok_desc' ? 'selected' : '' ?>>Premiera (Najnowsze)</option>
                    <option value="rok_asc" <?= $sortowanie === 'rok_asc' ? 'selected' : '' ?>>Premiera (Najstarsze)</option>
                    <option value="ocena_desc" <?= $sortowanie === 'ocena_desc' ? 'selected' : '' ?>>Ocena (Najwyższa)</option>
                    <option value="tytul_asc" <?= $sortowanie === 'tytul_asc' ? 'selected' : '' ?>>Tytuł (A-Z)</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filtruj</button>
                <?php if ($szukaj !== '' || $wybranyGatunek !== '' || $sortowanie !== 'rok_desc'): ?>
                    <a href="index.php" class="btn btn-secondary">Wyczyść</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="results-count">
            <span>Wyświetlono: <strong><?= count($filmy) ?></strong> <?= count($filmy) === 1 ? 'film' : 'filmów' ?></span>
            <?php if ($szukaj !== '' || $wybranyGatunek !== ''): ?>
                <span>Kryteria: <em><?= e($szukaj ? 'fraza: "' . $szukaj . '"' : '') ?><?= $wybranyGatunek ? ' gatunek: ' . $wybranyGatunek : '' ?></em></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Siatka Prezentacji Filmów -->
    <?php if (empty($filmy)): ?>
        <div class="filter-card" style="text-align: center; padding: 50px 20px;">
            <p style="font-size: 1.2rem; margin-bottom: 15px;">Nie znaleziono filmów spełniających podane kryteria.</p>
            <a href="index.php" class="btn btn-secondary">Zresetuj filtry</a>
        </div>
    <?php else: ?>
        <div class="movie-grid">
            <?php foreach ($filmy as $film): ?>
                <article class="movie-card">
                    <div class="movie-poster-box">
                        <?php 
                            $plakat = !empty($film['plakat']) ? $film['plakat'] : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80';
                        ?>
                        <img 
                            src="<?= e($plakat) ?>" 
                            alt="Plakat: <?= e($film['tytul']) ?>" 
                            class="movie-poster-img" 
                            loading="lazy"
                        >
                        <div class="movie-badge-rating" title="Średnia ocena widzów">
                            ★ <?= $film['srednia_ocen'] !== null ? number_format((float)$film['srednia_ocen'], 1) : '–' ?>
                        </div>
                    </div>

                    <div class="movie-card-body">
                        <h2 class="movie-card-title">
                            <a href="film.php?id=<?= (int)$film['id'] ?>">
                                <?= e($film['tytul']) ?>
                            </a>
                        </h2>

                        <div class="movie-card-meta">
                            <span class="meta-chip">📅 <?= (int)$film['rok_premiery'] ?></span>
                            <span class="meta-chip">🎭 <?= e($film['gatunek']) ?></span>
                            <span class="meta-chip">⏱️ <?= (int)$film['czas_trwania'] ?> min</span>
                        </div>

                        <p class="movie-card-desc">
                            <?= e($film['opis']) ?>
                        </p>

                        <div class="movie-card-footer">
                            <div class="movie-card-stats">
                                <span title="Obsada">👥 <?= (int)$film['liczba_aktorow'] ?></span>
                                <span title="Recenzje">💬 <?= (int)$film['liczba_recenzji'] ?></span>
                            </div>
                            <a href="film.php?id=<?= (int)$film['id'] ?>" class="btn btn-secondary btn-sm">
                                Szczegóły &rarr;
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
