<?php
/**
 * Przypisanie Aktora do Filmu (Obsada M:N) (CineHub)
 * Rola: Backend & Frontend Developer
 * Zgodność z INF.03 (Specs.md pkt 2.3)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Przypisz Aktora do Filmu';
$errors = [];
$dbError = null;

$filmy = [];
$aktorzy = [];

$domyslnyFilmId = filter_input(INPUT_GET, 'film_id', FILTER_VALIDATE_INT) ?: 0;
$wybranyFilmId = $domyslnyFilmId;
$wybranyAktorId = 0;
$rola = '';

try {
    $pdo = getDbConnection();

    // Pobranie listy filmów do selecta
    $filmy = $pdo->query("SELECT id, tytul, rok_premiery FROM filmy ORDER BY tytul ASC")->fetchAll();

    // Pobranie listy aktorów do selecta
    $aktorzy = $pdo->query("SELECT id, imie, nazwisko FROM aktorzy ORDER BY nazwisko ASC, imie ASC")->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $wybranyFilmId = filter_input(INPUT_POST, 'film_id', FILTER_VALIDATE_INT) ?: 0;
        $wybranyAktorId = filter_input(INPUT_POST, 'aktor_id', FILTER_VALIDATE_INT) ?: 0;
        $rola = trim((string)($_POST['rola'] ?? ''));

        if ($wybranyFilmId <= 0) {
            $errors[] = 'Wybierz film z listy.';
        }
        if ($wybranyAktorId <= 0) {
            $errors[] = 'Wybierz aktora z listy.';
        }
        if ($rola === '') {
            $errors[] = 'Podaj nazwę odgrywanej postaci / roli.';
        } elseif (mb_strlen($rola) > 150) {
            $errors[] = 'Nazwa roli nie może przekraczać 150 znaków.';
        }

        if (empty($errors)) {
            // Sprawdzenie czy powiązanie już istnieje (pełna kompatybilność ANSI SQL)
            $checkStmt = $pdo->prepare("
                SELECT id FROM obsada 
                WHERE film_id = :film_id AND aktor_id = :aktor_id AND rola = :rola
            ");
            $checkStmt->execute([
                'film_id'  => $wybranyFilmId,
                'aktor_id' => $wybranyAktorId,
                'rola'     => $rola
            ]);

            if ($checkStmt->fetch()) {
                setFlash('info', 'Ta rola jest już przypisana do obsady tego filmu.');
            } else {
                $sql = "INSERT INTO obsada (film_id, aktor_id, rola) VALUES (:film_id, :aktor_id, :rola)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'film_id'  => $wybranyFilmId,
                    'aktor_id' => $wybranyAktorId,
                    'rola'     => $rola
                ]);
                setFlash('success', 'Rola została pomyślnie przypisana do obsady filmu!');
            }

            header('Location: film.php?id=' . $wybranyFilmId);
            exit;
        }
    }

} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="back-nav">
    <a href="index.php" class="btn btn-secondary btn-sm">&larr; Wróć do katalogu filmów</a>
</div>

<div class="form-box" style="max-width: 650px; margin: 0 auto;">
    <h1 class="form-box-title">Przypisz Rolę do Obsady</h1>
    <p class="form-box-subtitle">Połącz aktora z wybranym filmem i określ graną postać (relacja M:N).</p>

    <?php if ($dbError): ?>
        <div class="alert alert-error">
            <strong>Błąd bazy danych:</strong> <?= e($dbError) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Wystąpiły błędy:</strong>
            <ul style="margin-left: 20px; margin-top: 6px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="assign_actor.php<?= $domyslnyFilmId ? '?film_id=' . $domyslnyFilmId : '' ?>">
        <div class="form-row">
            <label for="film_id" class="form-label">Wybierz film: <span class="req">*</span></label>
            <select id="film_id" name="film_id" class="form-control" required>
                <option value="">-- Wybierz film z bazy --</option>
                <?php foreach ($filmy as $f): ?>
                    <option value="<?= (int)$f['id'] ?>" <?= (int)$wybranyFilmId === (int)$f['id'] ? 'selected' : '' ?>>
                        <?= e($f['tytul']) ?> (<?= (int)$f['rok_premiery'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="aktor_id" class="form-label">Wybierz aktora: <span class="req">*</span></label>
            <select id="aktor_id" name="aktor_id" class="form-control" required>
                <option value="">-- Wybierz aktora z bazy --</option>
                <?php foreach ($aktorzy as $a): ?>
                    <option value="<?= (int)$a['id'] ?>" <?= (int)$wybranyAktorId === (int)$a['id'] ? 'selected' : '' ?>>
                        <?= e($a['nazwisko'] . ' ' . $a['imie']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="rola" class="form-label">Nazwa postaci / rola: <span class="req">*</span></label>
            <input type="text" id="rola" name="rola" class="form-control" placeholder="np. J. Robert Oppenheimer" value="<?= e($rola) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
            🔗 Powiąż Aktora z Filmem
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

