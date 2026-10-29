<?php
/**
 * Dodawanie Nowego Aktora (CineHub)
 * Rola: Backend & Frontend Developer
 * Zgodność z INF.03 (Specs.md pkt 2.2)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Dodaj Nowego Aktora';
$errors = [];
$dbError = null;

$imie = '';
$nazwisko = '';
$data_urodzenia = '';
$zdjecie = '';
$biografia = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imie = trim((string)($_POST['imie'] ?? ''));
    $nazwisko = trim((string)($_POST['nazwisko'] ?? ''));
    $data_urodzenia = trim((string)($_POST['data_urodzenia'] ?? ''));
    $zdjecie = trim((string)($_POST['zdjecie'] ?? ''));
    $biografia = trim((string)($_POST['biografia'] ?? ''));

    if ($imie === '') {
        $errors[] = 'Imię aktora jest wymagane.';
    } elseif (mb_strlen($imie) > 100) {
        $errors[] = 'Imię nie może przekraczać 100 znaków.';
    }

    if ($nazwisko === '') {
        $errors[] = 'Nazwisko aktora jest wymagane.';
    } elseif (mb_strlen($nazwisko) > 100) {
        $errors[] = 'Nazwisko nie może przekraczać 100 znaków.';
    }

    if ($data_urodzenia !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_urodzenia)) {
        $errors[] = 'Podaj datę urodzenia w formacie RRRR-MM-DD.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $sql = "
                INSERT INTO aktorzy (imie, nazwisko, data_urodzenia, zdjecie, biografia)
                VALUES (:imie, :nazwisko, :data_urodzenia, :zdjecie, :biografia)
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'imie'           => $imie,
                'nazwisko'       => $nazwisko,
                'data_urodzenia' => $data_urodzenia !== '' ? $data_urodzenia : null,
                'zdjecie'        => $zdjecie !== '' ? $zdjecie : null,
                'biografia'      => $biografia !== '' ? $biografia : null
            ]);

            $noweId = (int)$pdo->lastInsertId();
            setFlash('success', 'Aktor ' . $imie . ' ' . $nazwisko . ' został pomyślnie dodany do bazy!');
            header('Location: actor.php?id=' . $noweId);
            exit;

        } catch (PDOException $e) {
            $dbError = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="back-nav">
    <a href="actors.php" class="btn btn-secondary btn-sm">&larr; Wróć do bazy aktorów</a>
</div>

<div class="form-box" style="max-width: 700px; margin: 0 auto;">
    <h1 class="form-box-title">Dodaj Nowego Aktora</h1>
    <p class="form-box-subtitle">Zarejestruj profil artysty w bazie CineHub.</p>

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

    <form method="POST" action="add_actor.php">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-row">
                <label for="imie" class="form-label">Imię: <span class="req">*</span></label>
                <input type="text" id="imie" name="imie" class="form-control" placeholder="np. Cillian" value="<?= e($imie) ?>" required>
            </div>
            <div class="form-row">
                <label for="nazwisko" class="form-label">Nazwisko: <span class="req">*</span></label>
                <input type="text" id="nazwisko" name="nazwisko" class="form-control" placeholder="np. Murphy" value="<?= e($nazwisko) ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-row">
                <label for="data_urodzenia" class="form-label">Data urodzenia:</label>
                <input type="date" id="data_urodzenia" name="data_urodzenia" class="form-control" value="<?= e($data_urodzenia) ?>">
            </div>
            <div class="form-row">
                <label for="zdjecie" class="form-label">URL Zdjęcia:</label>
                <input type="url" id="zdjecie" name="zdjecie" class="form-control" placeholder="https://example.com/foto.jpg" value="<?= e($zdjecie) ?>">
            </div>
        </div>

        <div class="form-row">
            <label for="biografia" class="form-label">Biografia / Krótki życiorys:</label>
            <textarea id="biografia" name="biografia" class="form-control" rows="4" placeholder="Krótki opis kariery, nagród i dorobku artysty..."><?= e($biografia) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
            💾 Zapisz Aktora w Bazie
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

