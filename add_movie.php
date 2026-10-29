<?php
/**
 * Dodawanie Nowego Filmu (CineHub)
 * Rola: Backend & Frontend Developer
 * Zgodność z INF.03 (Specs.md pkt 2.1)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Dodaj Nowy Film';
$errors = [];
$dbError = null;

$tytul = '';
$rezyser = '';
$gatunek = '';
$rok_premiery = (string)date('Y');
$czas_trwania = '120';
$opis = '';
$plakat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tytul = trim((string)($_POST['tytul'] ?? ''));
    $rezyser = trim((string)($_POST['rezyser'] ?? ''));
    $gatunek = trim((string)($_POST['gatunek'] ?? ''));
    $rokRaw = $_POST['rok_premiery'] ?? null;
    $czasRaw = $_POST['czas_trwania'] ?? null;
    $opis = trim((string)($_POST['opis'] ?? ''));
    $plakat = trim((string)($_POST['plakat'] ?? ''));

    // Walidacja
    if ($tytul === '') {
        $errors[] = 'Tytuł filmu jest wymagany.';
    } elseif (mb_strlen($tytul) > 255) {
        $errors[] = 'Tytuł nie może przekraczać 255 znaków.';
    }

    if ($rezyser === '') {
        $errors[] = 'Podaj imię i nazwisko reżysera.';
    }

    if ($gatunek === '') {
        $errors[] = 'Podaj gatunek filmu.';
    }

    $rok_premiery = filter_var($rokRaw, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1888, 'max_range' => (int)date('Y') + 5]
    ]);
    if ($rok_premiery === false) {
        $errors[] = 'Podaj prawidłowy rok premiery (od 1888 do ' . ((int)date('Y') + 5) . ').';
        $rok_premiery = (string)date('Y');
    }

    $czas_trwania = filter_var($czasRaw, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 1000]
    ]);
    if ($czas_trwania === false) {
        $errors[] = 'Podaj prawidłowy czas trwania filmu w minutach.';
        $czas_trwania = '120';
    }

    if ($opis === '') {
        $errors[] = 'Wpisz opis fabuły filmu.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $sql = "
                INSERT INTO filmy (tytul, rezyser, gatunek, rok_premiery, czas_trwania, opis, plakat)
                VALUES (:tytul, :rezyser, :gatunek, :rok_premiery, :czas_trwania, :opis, :plakat)
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'tytul'        => $tytul,
                'rezyser'      => $rezyser,
                'gatunek'      => $gatunek,
                'rok_premiery' => (int)$rok_premiery,
                'czas_trwania' => (int)$czas_trwania,
                'opis'         => $opis,
                'plakat'       => $plakat !== '' ? $plakat : null
            ]);

            $noweId = (int)$pdo->lastInsertId();
            setFlash('success', 'Film "' . $tytul . '" został pomyślnie dodany do bazy!');
            header('Location: film.php?id=' . $noweId);
            exit;

        } catch (PDOException $e) {
            $dbError = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="back-nav">
    <a href="index.php" class="btn btn-secondary btn-sm">&larr; Wróć do katalogu filmów</a>
</div>

<div class="form-box" style="max-width: 800px; margin: 0 auto;">
    <h1 class="form-box-title">Dodaj Nowy Film do Bazy</h1>
    <p class="form-box-subtitle">Wypełnij poniższy formularz, aby zarejestrować nową produkcję filmową.</p>

    <?php if ($dbError): ?>
        <div class="alert alert-error">
            <strong>Błąd zapisu w bazie danych:</strong> <?= e($dbError) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Wystąpiły błędy w formularzu:</strong>
            <ul style="margin-left: 20px; margin-top: 6px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="add_movie.php">
        <div class="form-row">
            <label for="tytul" class="form-label">Tytuł filmu: <span class="req">*</span></label>
            <input type="text" id="tytul" name="tytul" class="form-control" placeholder="np. Diuna: Część Druga" value="<?= e($tytul) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-row">
                <label for="rezyser" class="form-label">Reżyser: <span class="req">*</span></label>
                <input type="text" id="rezyser" name="rezyser" class="form-control" placeholder="np. Denis Villeneuve" value="<?= e($rezyser) ?>" required>
            </div>
            <div class="form-row">
                <label for="gatunek" class="form-label">Gatunek: <span class="req">*</span></label>
                <input type="text" id="gatunek" name="gatunek" class="form-control" placeholder="np. Sci-Fi, Dramat" value="<?= e($gatunek) ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-row">
                <label for="rok_premiery" class="form-label">Rok premiery: <span class="req">*</span></label>
                <input type="number" id="rok_premiery" name="rok_premiery" class="form-control" value="<?= e((string)$rok_premiery) ?>" min="1888" max="<?= (int)date('Y') + 5 ?>" required>
            </div>
            <div class="form-row">
                <label for="czas_trwania" class="form-label">Czas trwania (minuty): <span class="req">*</span></label>
                <input type="number" id="czas_trwania" name="czas_trwania" class="form-control" value="<?= e((string)$czas_trwania) ?>" min="1" max="1000" required>
            </div>
        </div>

        <div class="form-row">
            <label for="plakat" class="form-label">URL Plakatu (opcjonalnie):</label>
            <input type="url" id="plakat" name="plakat" class="form-control" placeholder="https://example.com/plakat.jpg" value="<?= e($plakat) ?>">
        </div>

        <div class="form-row">
            <label for="opis" class="form-label">Zarys fabuły: <span class="req">*</span></label>
            <textarea id="opis" name="opis" class="form-control" rows="5" placeholder="Wprowadź krótki opis fabuły..." required><?= e($opis) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
            💾 Zapisz Film w Bazie
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

