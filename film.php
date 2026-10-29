<?php
/**
 * Szczegóły Filmu i Obsługa Recenzji (CineHub)
 * Rola: Frontend Developer (integracja z Backendem)
 * Zgodność z INF.03
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$filmId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$dbError = null;
$film = null;
$obsada = [];
$recenzje = [];
$errors = [];

// Zmienne formularza (dla zachowania wpisanych danych przy błędzie)
$formAutor = '';
$formOcena = '10';
$formTresc = '';

if (!$filmId) {
    setFlash('error', 'Nie wybrano prawidłowego identyfikatora filmu.');
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDbConnection();

    // -------------------------------------------------------------------------
    // 1. OBSŁUGA FORMULARZA RECENZJI (METODA POST)
    // -------------------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formAutor = trim((string)($_POST['autor'] ?? ''));
        $formOcenaRaw = $_POST['ocena'] ?? null;
        $formTresc = trim((string)($_POST['tresc'] ?? ''));

        // Walidacja danych
        if ($formAutor === '') {
            $errors[] = 'Podaj swoje imię lub pseudonim.';
        } elseif (mb_strlen($formAutor) > 100) {
            $errors[] = 'Imię lub pseudonim nie może przekraczać 100 znaków.';
        }

        $formOcena = filter_var($formOcenaRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 10]
        ]);

        if ($formOcena === false) {
            $errors[] = 'Wybierz prawidłową ocenę w skali od 1 do 10.';
            $formOcena = '10';
        }

        if ($formTresc === '') {
            $errors[] = 'Wpisz treść swojej recenzji.';
        } elseif (mb_strlen($formTresc) < 5) {
            $errors[] = 'Treść recenzji musi zawierać co najmniej 5 znaków.';
        }

        // Zapis do bazy danych (Prepared Statement przeciwko SQL Injection)
        if (empty($errors)) {
            $insertSql = "
                INSERT INTO recenzje (film_id, autor, ocena, tresc)
                VALUES (:film_id, :autor, :ocena, :tresc)
            ";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                'film_id' => $filmId,
                'autor'   => $formAutor,
                'ocena'   => $formOcena,
                'tresc'   => $formTresc
            ]);

            setFlash('success', 'Twoja recenzja została pomyślnie dodana i uwzględniona w ocenie filmu!');
            // Zastosowanie wzorca PRG (Post/Redirect/Get)
            header("Location: film.php?id=" . $filmId . "#recenzje");
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // 2. POBIERANIE DANYCH FILMU I AGREGACJI OCEN
    // -------------------------------------------------------------------------
    $filmSql = "
        SELECT 
            f.*,
            r.srednia_ocen,
            COALESCE(r.liczba_recenzji, 0) AS liczba_recenzji
        FROM filmy f
        LEFT JOIN (
            SELECT film_id, ROUND(AVG(ocena), 1) AS srednia_ocen, COUNT(id) AS liczba_recenzji
            FROM recenzje
            GROUP BY film_id
        ) r ON f.id = r.film_id
        WHERE f.id = :id
    ";
    $filmStmt = $pdo->prepare($filmSql);
    $filmStmt->execute(['id' => $filmId]);
    $film = $filmStmt->fetch();

    if (!$film) {
        setFlash('error', 'Film o podanym ID nie został odnaleziony w bazie.');
        header('Location: index.php');
        exit;
    }

    // -------------------------------------------------------------------------
    // 3. POBIERANIE OBSADY (RELACJA M:N FILMY <-> AKTORZY)
    // -------------------------------------------------------------------------
    $obsadaSql = "
        SELECT 
            a.id AS aktor_id,
            a.imie,
            a.nazwisko,
            a.zdjecie,
            o.rola
        FROM obsada o
        JOIN aktorzy a ON o.aktor_id = a.id
        WHERE o.film_id = :film_id
        ORDER BY a.nazwisko ASC, a.imie ASC
    ";
    $obsadaStmt = $pdo->prepare($obsadaSql);
    $obsadaStmt->execute(['film_id' => $filmId]);
    $obsada = $obsadaStmt->fetchAll();

    // -------------------------------------------------------------------------
    // 4. POBIERANIE RECENZJI
    // -------------------------------------------------------------------------
    $recenzjeSql = "
        SELECT id, autor, ocena, tresc, created_at
        FROM recenzje
        WHERE film_id = :film_id
        ORDER BY created_at DESC
    ";
    $recenzjeStmt = $pdo->prepare($recenzjeSql);
    $recenzjeStmt->execute(['film_id' => $filmId]);
    $recenzje = $recenzjeStmt->fetchAll();

} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

$pageTitle = $film ? $film['tytul'] : 'Szczegóły Filmu';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Nawigacja powrotna -->
<div class="back-nav">
    <a href="index.php" class="btn btn-secondary btn-sm">
        &larr; Wróć do katalogu filmów
    </a>
</div>

<?php if ($dbError): ?>
    <div class="alert alert-error" role="alert">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">
            <strong>Błąd bazy danych:</strong> <?= e($dbError) ?>
        </div>
    </div>
<?php elseif ($film): ?>

    <!-- Szczegóły Filmu -->
    <article class="movie-detail-view">
        <div class="detail-hero-card">
            <div class="detail-layout">
                <!-- Plakat Filmu -->
                <div class="detail-poster-wrapper">
                    <?php 
                        $plakat = !empty($film['plakat']) ? $film['plakat'] : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80';
                    ?>
                    <img 
                        src="<?= e($plakat) ?>" 
                        alt="Plakat do filmu <?= e($film['tytul']) ?>" 
                        class="detail-poster"
                    >
                </div>

                <!-- Dane i Fabuła -->
                <div class="detail-header-info">
                    <h1><?= e($film['tytul']) ?></h1>

                    <div class="detail-chips-row">
                        <span class="score-chip" title="Średnia ocena filmu">
                            ★ <?= formatRating((float)$film['srednia_ocen']) ?>
                        </span>
                        <span class="info-chip">📅 Premiera: <?= (int)$film['rok_premiery'] ?></span>
                        <span class="info-chip">🎭 Gatunek: <?= e($film['gatunek']) ?></span>
                        <span class="info-chip">⏱️ Czas: <?= (int)$film['czas_trwania'] ?> min</span>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">Reżyseria</div>
                        <p style="font-size: 1.15rem; font-weight: 600; color: #fff;">
                            <?= e($film['rezyser']) ?>
                        </p>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">Zarys Fabuły</div>
                        <p class="detail-synopsis">
                            <?= nl2br(e($film['opis'])) ?>
                        </p>
                    </div>

                    <!-- Obsada Aktorska (Relacja M:N) -->
                    <div class="cast-section">
                        <div class="detail-section-title">
                            Obsada Aktorska (<?= count($obsada) ?>)
                        </div>

                        <?php if (empty($obsada)): ?>
                            <p style="color: var(--text-dark);">Brak informacji o obsadzie aktorskiej dla tego filmu.</p>
                        <?php else: ?>
                            <div class="cast-grid">
                                <?php foreach ($obsada as $aktor): ?>
                                    <div class="actor-card">
                                        <?php 
                                            $zdjecie = !empty($aktor['zdjecie']) ? $aktor['zdjecie'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80';
                                        ?>
                                        <a href="actor.php?id=<?= (int)$aktor['aktor_id'] ?>" title="Zobacz profil aktora">
                                            <img 
                                                src="<?= e($zdjecie) ?>" 
                                                alt="<?= e($aktor['imie'] . ' ' . $aktor['nazwisko']) ?>" 
                                                class="actor-avatar"
                                                loading="lazy"
                                            >
                                        </a>
                                        <div class="actor-info">
                                            <div class="actor-name">
                                                <a href="actor.php?id=<?= (int)$aktor['aktor_id'] ?>" style="color: inherit; text-decoration: none;">
                                                    <?= e($aktor['imie'] . ' ' . $aktor['nazwisko']) ?>
                                                </a>
                                            </div>
                                            <div class="actor-role">jako: <em><?= e($aktor['rola']) ?></em></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sekcja Recenzji Społeczności -->
        <section class="reviews-section" id="recenzje">
            <div class="section-heading">
                <h2>Opinie i Recenzje Społeczności</h2>
            </div>

            <!-- Panel Oceny Zbiorczej -->
            <div class="reviews-scorecard">
                <div class="scorecard-big">
                    <div class="big-rating-number">
                        <?= $film['srednia_ocen'] !== null ? number_format((float)$film['srednia_ocen'], 1) : '–' ?>
                    </div>
                    <div class="big-rating-scale">na 10 punktów</div>
                </div>
                <div class="scorecard-info">
                    <h3>Średnia ocen widzów CineHub</h3>
                    <p>
                        Łącznie wystawiono <strong><?= count($recenzje) ?></strong> <?= count($recenzje) === 1 ? 'recenzję' : 'recenzji' ?>. 
                        Dołącz do dyskusji i podziel się swoją opinią poniżej!
                    </p>
                </div>
            </div>

            <!-- Lista Recenzji -->
            <?php if (empty($recenzje)): ?>
                <div class="filter-card" style="text-align: center; padding: 40px 20px; margin-bottom: 40px;">
                    <p style="font-size: 1.1rem; color: var(--text-muted); margin-bottom: 12px;">
                        Ten film nie ma jeszcze żadnych recenzji.
                    </p>
                    <p style="font-size: 0.95rem; color: var(--text-dark);">
                        Bądź pierwszą osobą, która podzieli się swoimi wrażeniami z seansu!
                    </p>
                </div>
            <?php else: ?>
                <div class="reviews-grid">
                    <?php foreach ($recenzje as $recenzja): ?>
                        <article class="review-item">
                            <div class="review-item-header">
                                <div class="review-user-info">
                                    <div class="user-avatar-placeholder" aria-hidden="true">
                                        <?= mb_strtoupper(mb_substr($recenzja['autor'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="reviewer-name"><?= e($recenzja['autor']) ?></div>
                                        <div class="review-timestamp"><?= date('d.m.Y, H:i', strtotime($recenzja['created_at'])) ?></div>
                                    </div>
                                </div>
                                <div class="review-score-badge" title="Ocena: <?= (int)$recenzja['ocena'] ?>/10">
                                    ★ <?= (int)$recenzja['ocena'] ?> / 10
                                </div>
                            </div>
                            <p class="review-text"><?= nl2br(e($recenzja['tresc'])) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formularz Dodawania Recenzji -->
            <div class="form-box">
                <h3 class="form-box-title">Dodaj Swoją Recenzję</h3>
                <p class="form-box-subtitle">
                    Twoja opinia pomoże innym kinomanom podjąć decyzję o seansie.
                </p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" role="alert">
                        <span class="alert-icon">⚠️</span>
                        <div class="alert-body">
                            <strong>Formularz zawiera błędy:</strong>
                            <ul style="margin-left: 20px; margin-top: 6px;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="film.php?id=<?= (int)$film['id'] ?>#recenzje" id="review-form">
                    <div class="form-row">
                        <label for="autor" class="form-label">
                            Twój pseudonim lub imię: <span class="req">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="autor" 
                            name="autor" 
                            class="form-control" 
                            placeholder="np. Kinomaniak, Jan Kowalski" 
                            value="<?= e($formAutor) ?>" 
                            required 
                            maxlength="100"
                        >
                    </div>

                    <div class="form-row">
                        <label class="form-label">
                            Twoja ocena filmu (w skali 1-10): <span class="req">*</span>
                        </label>

                        <div class="rating-picker-container">
                            <div class="rating-stars-group" role="radiogroup" aria-label="Wybierz ocenę od 1 do 10">
                                <?php for ($i = 10; $i >= 1; $i--): ?>
                                    <input 
                                        type="radio" 
                                        id="star-<?= $i ?>" 
                                        name="ocena" 
                                        value="<?= $i ?>" 
                                        <?= (string)$formOcena === (string)$i ? 'checked' : '' ?>
                                        required
                                    >
                                    <label for="star-<?= $i ?>" title="<?= $i ?> / 10">★</label>
                                <?php endfor; ?>
                            </div>

                            <span class="rating-preview-badge" id="rating-preview-text">
                                ★ 10/10 – Arcydzieło
                            </span>
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="tresc" class="form-label">
                            Treść recenzji: <span class="req">*</span>
                        </label>
                        <textarea 
                            id="tresc" 
                            name="tresc" 
                            class="form-control" 
                            rows="5" 
                            placeholder="Napisz, co najbardziej urzekło Cię w filmie (gra aktorska, efekty, reżyseria, muzyka)..." 
                            required
                        ><?= e($formTresc) ?></textarea>
                        <div class="textarea-counter" id="char-count">0 znaków (min. 5)</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        ✍️ Opublikuj Recenzję
                    </button>
                </form>
            </div>
        </section>
    </article>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
