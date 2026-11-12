<?php
/**
 * Baza Aktorów - Lista i Wyszukiwarka (CineHub)
 * Rola: Backend & Frontend Developer
 * Zgodność z INF.03 (Specs.md pkt 2.2)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Baza Aktorów';
$dbError = null;
$aktorzy = [];
$filmyFilter = [];
$szukaj = trim((string)($_GET['szukaj'] ?? ''));
$wybranyFilmId = filter_var($_GET['film_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$sortowanie = trim((string)($_GET['sort'] ?? 'nazwisko_asc'));

try {
    $pdo = getDbConnection();

    // Pobranie listy filmów do filtra dropdown
    $stmtFilmy = $pdo->query("SELECT id, tytul, rok_premiery FROM filmy ORDER BY tytul ASC");
    $filmyFilter = $stmtFilmy->fetchAll(PDO::FETCH_ASSOC);

    $sql = "
        SELECT 
            a.id,
            a.imie,
            a.nazwisko,
            a.data_urodzenia,
            a.zdjecie,
            a.biografia,
            COUNT(DISTINCT o.film_id) AS liczba_filmow
        FROM aktorzy a
        LEFT JOIN obsada o ON a.id = o.aktor_id
        WHERE 1=1
    ";

    $params = [];

    if ($szukaj !== '') {
        $sql .= " AND (
            a.imie LIKE :s_imie 
            OR a.nazwisko LIKE :s_nazw 
            OR CONCAT(a.imie, ' ', a.nazwisko) LIKE :s_full 
            OR a.biografia LIKE :s_bio
            OR EXISTS (
                SELECT 1 FROM obsada o_s 
                JOIN filmy f_s ON o_s.film_id = f_s.id 
                WHERE o_s.aktor_id = a.id 
                AND (f_s.tytul LIKE :s_film OR o_s.rola LIKE :s_rola)
            )
        )";
        $searchTerm = '%' . $szukaj . '%';
        $params['s_imie'] = $searchTerm;
        $params['s_nazw'] = $searchTerm;
        $params['s_full'] = $searchTerm;
        $params['s_bio']  = $searchTerm;
        $params['s_film'] = $searchTerm;
        $params['s_rola'] = $searchTerm;
    }

    if ($wybranyFilmId > 0) {
        $sql .= " AND EXISTS (SELECT 1 FROM obsada o_f WHERE o_f.aktor_id = a.id AND o_f.film_id = :flt_film_id)";
        $params['flt_film_id'] = $wybranyFilmId;
    }

    $sql .= " GROUP BY a.id";

    switch ($sortowanie) {
        case 'nazwisko_desc':
            $sql .= " ORDER BY a.nazwisko DESC, a.imie DESC";
            break;
        case 'filmy_desc':
            $sql .= " ORDER BY liczba_filmow DESC, a.nazwisko ASC";
            break;
        case 'rok_desc':
            $sql .= " ORDER BY a.data_urodzenia IS NULL, a.data_urodzenia DESC";
            break;
        case 'rok_asc':
            $sql .= " ORDER BY a.data_urodzenia IS NULL, a.data_urodzenia ASC";
            break;
        case 'nazwisko_asc':
        default:
            $sql .= " ORDER BY a.nazwisko ASC, a.imie ASC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $aktorzy = $stmt->fetchAll();

} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section">
    <h1 class="hero-title">Katalog Aktorów i Twórców</h1>
    <p class="hero-subtitle">
        Poznaj profile utalentowanych artystów, sprawdź ich biografie oraz powiązane role filmowe.
    </p>
</section>

<?php if ($dbError): ?>
    <div class="alert alert-error" role="alert">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">
            <strong>Błąd połączenia z bazą:</strong> <?= e($dbError) ?>
        </div>
    </div>
<?php else: ?>

    <!-- Wyszukiwarka i Filtry Aktorów -->
    <div class="filter-card">
        <form method="GET" action="actors.php" class="filter-form">
            <div class="search-input-group">
                <span class="search-icon">🔍</span>
                <input 
                    type="text" 
                    name="szukaj" 
                    class="form-control" 
                    placeholder="Wyszukaj aktora, rolę lub film..." 
                    value="<?= e($szukaj) ?>"
                    aria-label="Wyszukaj aktora"
                >
            </div>

            <div class="select-group">
                <select name="film_id" class="form-control" aria-label="Filtruj według filmu">
                    <option value="">Wszystkie produkcje</option>
                    <?php foreach ($filmyFilter as $ff): ?>
                        <option value="<?= (int)$ff['id'] ?>" <?= (int)$wybranyFilmId === (int)$ff['id'] ? 'selected' : '' ?>>
                            🎬 <?= e($ff['tytul']) ?> (<?= (int)$ff['rok_premiery'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="select-group">
                <select name="sort" class="form-control" aria-label="Sortowanie">
                    <option value="nazwisko_asc" <?= $sortowanie === 'nazwisko_asc' ? 'selected' : '' ?>>Nazwisko (A-Z)</option>
                    <option value="nazwisko_desc" <?= $sortowanie === 'nazwisko_desc' ? 'selected' : '' ?>>Nazwisko (Z-A)</option>
                    <option value="filmy_desc" <?= $sortowanie === 'filmy_desc' ? 'selected' : '' ?>>Najwięcej ról w bazie</option>
                    <option value="rok_desc" <?= $sortowanie === 'rok_desc' ? 'selected' : '' ?>>Wiek (Najmłodsi)</option>
                    <option value="rok_asc" <?= $sortowanie === 'rok_asc' ? 'selected' : '' ?>>Wiek (Najstarsi)</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filtruj</button>
                <?php if ($szukaj !== '' || $wybranyFilmId > 0 || $sortowanie !== 'nazwisko_asc'): ?>
                    <a href="actors.php" class="btn btn-secondary">Zresetuj</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="results-count">
            <span>Wyświetlono: <strong><?= count($aktorzy) ?></strong> <?= count($aktorzy) === 1 ? 'aktora' : 'aktorów' ?></span>
            <?php if ($szukaj !== '' || $wybranyFilmId > 0): ?>
                <span>Kryteria: <em><?= e($szukaj ? 'fraza: "' . $szukaj . '"' : '') ?><?= $wybranyFilmId > 0 ? ' [filtr filmu aktywny]' : '' ?></em></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Siatka Aktorów -->
    <?php if (empty($aktorzy)): ?>
        <div class="filter-card" style="text-align: center; padding: 40px;">
            <p>Nie odnaleziono aktorów spełniających podane kryteria.</p>
        </div>
    <?php else: ?>
        <div class="actors-grid">
            <?php foreach ($aktorzy as $aktor): ?>
                <article class="actor-catalog-card">
                    <?php 
                        $zdjecie = !empty($aktor['zdjecie']) ? $aktor['zdjecie'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=400&q=80';
                    ?>
                    <img 
                        src="<?= e($zdjecie) ?>" 
                        alt="<?= e($aktor['imie'] . ' ' . $aktor['nazwisko']) ?>" 
                        class="actor-catalog-photo"
                        loading="lazy"
                    >

                    <div class="actor-catalog-body">
                        <h2 class="actor-catalog-name">
                            <a href="actor.php?id=<?= (int)$aktor['id'] ?>">
                                <?= e($aktor['imie'] . ' ' . $aktor['nazwisko']) ?>
                            </a>
                        </h2>

                        <?php if (!empty($aktor['data_urodzenia'])): ?>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                                🎂 Urodzony/a: <?= date('d.m.Y', strtotime($aktor['data_urodzenia'])) ?>
                            </p>
                        <?php endif; ?>

                        <p class="actor-catalog-bio">
                            <?= !empty($aktor['biografia']) ? e($aktor['biografia']) : 'Brak wprowadzonego życiorysu.' ?>
                        </p>

                        <div class="actor-catalog-footer">
                            <span>🎬 Filmy w bazie: <strong><?= (int)$aktor['liczba_filmow'] ?></strong></span>
                            <a href="actor.php?id=<?= (int)$aktor['id'] ?>" class="btn btn-secondary btn-sm">
                                Profil &rarr;
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

