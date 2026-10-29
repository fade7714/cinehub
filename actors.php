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
$szukaj = trim((string)($_GET['szukaj'] ?? ''));

try {
    $pdo = getDbConnection();

    $sql = "
        SELECT 
            a.id,
            a.imie,
            a.nazwisko,
            a.data_urodzenia,
            a.zdjecie,
            a.biografia,
            COUNT(o.id) AS liczba_filmow
        FROM aktorzy a
        LEFT JOIN obsada o ON a.id = o.aktor_id
        WHERE 1=1
    ";

    $params = [];

    if ($szukaj !== '') {
        $sql .= " AND (a.imie LIKE :szukaj OR a.nazwisko LIKE :szukaj OR a.biografia LIKE :szukaj)";
        $params['szukaj'] = '%' . $szukaj . '%';
    }

    $sql .= " GROUP BY a.id ORDER BY a.nazwisko ASC, a.imie ASC";

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

    <!-- Wyszukiwarka Aktorów -->
    <div class="filter-card">
        <form method="GET" action="actors.php" class="filter-form">
            <div class="search-input-group">
                <span class="search-icon">🔍</span>
                <input 
                    type="text" 
                    name="szukaj" 
                    class="form-control" 
                    placeholder="Wyszukaj aktora po imieniu, nazwisku..." 
                    value="<?= e($szukaj) ?>"
                >
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Szukaj</button>
                <?php if ($szukaj !== ''): ?>
                    <a href="actors.php" class="btn btn-secondary">Wyczyść</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="results-count">
            <span>Łącznie w bazie: <strong><?= count($aktorzy) ?></strong> <?= count($aktorzy) === 1 ? 'aktor' : 'aktorów' ?></span>
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

