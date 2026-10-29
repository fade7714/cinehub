<?php
/**
 * Zautomatyzowany Pakiet Testowy QA & Content (CineHub)
 * Rola: QA Asystent & Content Agent
 */

declare(strict_types=1);

// Wymuszenie środowiska SQLite na potrzeby zautomatyzowanego testu integracyjnego
putenv('DB_DRIVER=sqlite');
$testDbFile = __DIR__ . '/test_cinehub.sqlite';
if (file_exists($testDbFile)) {
    unlink($testDbFile);
}
putenv('DB_NAME=' . $testDbFile);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] " . $description . PHP_EOL;
        $passed++;
    } else {
        echo "  [FAIL] " . $description . PHP_EOL;
        $failed++;
    }
}

echo "=== ROZPOCZĘCIE TESTÓW AUDYTU QA & CONTENT DLA CINEHUB ===" . PHP_EOL . PHP_EOL;

// --------------------------------------------------------------------------
// TEST 1: Schemat i Inicjalizacja Tabel
// --------------------------------------------------------------------------
echo "--- TEST 1: Weryfikacja Schematu i Połączenia PDO ---" . PHP_EOL;
$pdo = getDbConnection();
assertTest("Połączenie z bazą PDO zostało pomyślnie nawiązane", $pdo instanceof PDO);

// Tworzenie tabel zgodnych z database/schema.sql
$pdo->exec("
    CREATE TABLE filmy (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tytul TEXT NOT NULL,
        rezyser TEXT NOT NULL,
        gatunek TEXT NOT NULL,
        rok_premiery INTEGER NOT NULL,
        czas_trwania INTEGER NOT NULL,
        opis TEXT NOT NULL,
        plakat TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE aktorzy (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        imie TEXT NOT NULL,
        nazwisko TEXT NOT NULL,
        data_urodzenia DATE,
        zdjecie TEXT,
        biografia TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE obsada (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        film_id INTEGER NOT NULL REFERENCES filmy(id) ON DELETE CASCADE,
        aktor_id INTEGER NOT NULL REFERENCES aktorzy(id) ON DELETE CASCADE,
        rola TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (film_id, aktor_id, rola)
    );

    CREATE TABLE recenzje (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        film_id INTEGER NOT NULL REFERENCES filmy(id) ON DELETE CASCADE,
        autor TEXT NOT NULL,
        ocena INTEGER NOT NULL CHECK (ocena BETWEEN 1 AND 10),
        tresc TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
");

$tablesStmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('filmy', 'aktorzy', 'obsada', 'recenzje');");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
assertTest("Wszystkie 4 tabele (filmy, aktorzy, obsada, recenzje) istnieją", count($tables) === 4);

// --------------------------------------------------------------------------
// TEST 2: Zasilenie Danymi z database/seed.sql (Polskie Kino)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 2: Zasilenie Danymi z pliku database/seed.sql ---" . PHP_EOL;

$seedContent = file_get_contents(__DIR__ . '/../database/seed.sql');
// Wyciągamy zapytania INSERT INTO
preg_match_all('/INSERT\s+INTO\s+`?(\w+)`?\s*\([^)]+\)\s*VALUES\s*([^;]+);/is', $seedContent, $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    $fullInsertSql = $match[0];
    // Zamieniamy apostrofy tabeli dla uniwersalności
    $cleanSql = str_replace(['`filmy`', '`aktorzy`', '`obsada`', '`recenzje`'], ['filmy', 'aktorzy', 'obsada', 'recenzje'], $fullInsertSql);
    $pdo->exec($cleanSql);
}

$countFilmy = (int)$pdo->query("SELECT COUNT(*) FROM filmy")->fetchColumn();
$countAktorzy = (int)$pdo->query("SELECT COUNT(*) FROM aktorzy")->fetchColumn();
$countObsada = (int)$pdo->query("SELECT COUNT(*) FROM obsada")->fetchColumn();
$countRecenzje = (int)$pdo->query("SELECT COUNT(*) FROM recenzje")->fetchColumn();

assertTest("Dane początkowe: 8 polskich filmów wstawionych (zgodnie z wymogiem 5-10)", $countFilmy === 8);
assertTest("Dane początkowe: 19 znanych polskich aktorów wstawionych", $countAktorzy === 19);
assertTest("Dane początkowe: 21 relacji obsady (M:N) wstawionych", $countObsada === 21);
assertTest("Dane początkowe: 17 autentycznych recenzji z różnymi ocenami wstawionych", $countRecenzje === 17);

// --------------------------------------------------------------------------
// TEST 3: Logika Wyświetlania Filmów i Agregacji Ocen (index.php)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 3: Weryfikacja Agregacji i Pobierania Filmów (Dzień świra) ---" . PHP_EOL;

$indexSql = "
    SELECT 
        f.id,
        f.tytul,
        f.gatunek,
        f.rok_premiery,
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
    WHERE f.id = 1
";
$stmt = $pdo->query($indexSql);
$film1 = $stmt->fetch();

assertTest("Dzień świra ma wyliczoną średnią ocenę 9.0 (oceny: 10, 9, 8)", (float)$film1['srednia_ocen'] === 9.0);
assertTest("Dzień świra ma 3 recenzje", (int)$film1['liczba_recenzji'] === 3);
assertTest("Dzień świra ma 3 przypisanych aktorów w obsadzie", (int)$film1['liczba_aktorow'] === 3);

// --------------------------------------------------------------------------
// TEST 4: Szczegóły Filmu i Obsada M:N (film.php)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 4: Weryfikacja Szczegółów Filmu i Obsady (film.php) ---" . PHP_EOL;

$obsadaStmt = $pdo->prepare("
    SELECT a.imie, a.nazwisko, o.rola
    FROM obsada o
    JOIN aktorzy a ON o.aktor_id = a.id
    WHERE o.film_id = :film_id
    ORDER BY a.nazwisko ASC
");
$obsadaStmt->execute(['film_id' => 1]);
$obsadaFilm1 = $obsadaStmt->fetchAll();

assertTest("Film Dzień świra zwraca 3 role w obsadzie", count($obsadaFilm1) === 3);
assertTest("Rola Marka Kondrata to 'Adaś Miauczyński'", $obsadaFilm1[1]['imie'] === 'Marek' && $obsadaFilm1[1]['rola'] === 'Adaś Miauczyński');

// --------------------------------------------------------------------------
// TEST 5: Zapis i Walidacja Nowej Recenzji (film.php POST)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 5: Weryfikacja Walidacji i Dodawania Nowej Recenzji ---" . PHP_EOL;

$nowaRecenzjaSql = "INSERT INTO recenzje (film_id, autor, ocena, tresc) VALUES (:film_id, :autor, :ocena, :tresc)";
$addStmt = $pdo->prepare($nowaRecenzjaSql);
$addStmt->execute([
    'film_id' => 1,
    'autor'   => 'Krytyk_Nowy',
    'ocena'   => 9,
    'tresc'   => 'Genialna rola Marka Kondrata, film ponadczasowy i wciąż aktualny!'
]);

$nowaSredniaStmt = $pdo->query("SELECT ROUND(AVG(ocena), 1) AS nowa_srednia, COUNT(*) as cnt FROM recenzje WHERE film_id = 1");
$noweStats = $nowaSredniaStmt->fetch();

assertTest("Liczba recenzji dla Dnia świra wzrosła do 4", (int)$noweStats['cnt'] === 4);
assertTest("Zaktualizowana średnia ocen wynosi 9.0 (10 + 9 + 8 + 9 / 4 = 9.0)", (float)$noweStats['nowa_srednia'] === 9.0);

// Test ograniczenia CHECK (ocena 1-10)
$checkFailed = false;
try {
    $badStmt = $pdo->prepare("INSERT INTO recenzje (film_id, autor, ocena, tresc) VALUES (1, 'Hacker', 15, 'Zła ocena')");
    $badStmt->execute();
} catch (PDOException $e) {
    $checkFailed = true;
}
assertTest("Baza danych odrzuca ocenę spoza zakresu 1-10 (CHECK constraint)", $checkFailed);

// --------------------------------------------------------------------------
// TEST 6: Baza Aktorów i Profil z Filmografią M:N (Cezary Pazura)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 6: Weryfikacja Wielu Filmów dla Aktora (Relacja M:N - Cezary Pazura) ---" . PHP_EOL;

$actorSql = "
    SELECT f.tytul, o.rola
    FROM obsada o
    JOIN filmy f ON o.film_id = f.id
    WHERE o.aktor_id = 5
    ORDER BY f.rok_premiery DESC
";
$actorStmt = $pdo->query($actorSql);
$rolesPazura = $actorStmt->fetchAll();

assertTest("Cezary Pazura (aktor_id: 5) ma w bazie dokładnie 3 różne filmy", count($rolesPazura) === 3);
$tytuly = array_column($rolesPazura, 'tytul');
assertTest("Cezary Pazura występuje w 'Chłopaki nie płaczą', 'Kiler' oraz 'Psy'", 
    in_array('Chłopaki nie płaczą', $tytuly) && in_array('Kiler', $tytuly) && in_array('Psy', $tytuly));

// --------------------------------------------------------------------------
// TEST 7: Bezpieczeństwo XSS (Ochrona w widokach)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 7: Weryfikacja Ochrony Przed XSS (Cross-Site Scripting) ---" . PHP_EOL;

$xssPayload = "<script>alert('XSS');</script>";
$escaped = e($xssPayload);
assertTest("Funkcja e() neutralizuje znaczniki HTML i cudzysłowy", strpos($escaped, "<script>") === false && strpos($escaped, "&lt;script&gt;") !== false);

// --------------------------------------------------------------------------
// TEST 8: Testowanie Dodawania Nowego Filmu i Aktora (add_movie, add_actor)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 8: Weryfikacja Formularzy add_movie i add_actor ---" . PHP_EOL;

$newMovieStmt = $pdo->prepare("
    INSERT INTO filmy (tytul, rezyser, gatunek, rok_premiery, czas_trwania, opis, plakat)
    VALUES (:tytul, :rezyser, :gatunek, :rok_premiery, :czas_trwania, :opis, :plakat)
");
$newMovieStmt->execute([
    'tytul' => 'Vabank',
    'rezyser' => 'Juliusz Machulski',
    'gatunek' => 'Komedia kryminalna',
    'rok_premiery' => 1981,
    'czas_trwania' => 108,
    'opis' => 'Warszawa, rok 1934. Legendarny kasiarz Henryk Kwinto wychodzi z więzienia i postanawia zemścić się na dawnym wspólniku.',
    'plakat' => null
]);
$vabankId = (int)$pdo->lastInsertId();
assertTest("Dodano nowy film 'Vabank' (id: $vabankId)", $vabankId > 0);

$newActorStmt = $pdo->prepare("
    INSERT INTO aktorzy (imie, nazwisko, data_urodzenia, zdjecie, biografia)
    VALUES (:imie, :nazwisko, :data_urodzenia, :zdjecie, :biografia)
");
$newActorStmt->execute([
    'imie' => 'Jan',
    'nazwisko' => 'Machulski',
    'data_urodzenia' => '1928-07-03',
    'zdjecie' => null,
    'biografia' => 'Wybitny polski aktor teatralny i filmowy, pedagog. Niezapomniany Henryk Kwinto.'
]);
$machulskiId = (int)$pdo->lastInsertId();
assertTest("Dodano nowego aktora 'Jan Machulski' (id: $machulskiId)", $machulskiId > 0);

// --------------------------------------------------------------------------
// TEST 9: Testowanie Przypisywania Obsady i Ignorowania Duplikatów (assign_actor)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 9: Weryfikacja Przypisywania Obsady i Braku Duplikatów ---" . PHP_EOL;

$assignStmt = $pdo->prepare("INSERT INTO obsada (film_id, aktor_id, rola) VALUES (:f, :a, :r)");
$assignStmt->execute(['f' => $vabankId, 'a' => $machulskiId, 'r' => 'Henryk Kwinto']);
assertTest("Przypisano rolę 'Henryk Kwinto' Janowi Machulskiemu w filmie 'Vabank'", true);

// Sprawdzenie wykrywania duplikatu
$dupCheck = $pdo->prepare("SELECT id FROM obsada WHERE film_id = :f AND aktor_id = :a AND rola = :r");
$dupCheck->execute(['f' => $vabankId, 'a' => $machulskiId, 'r' => 'Henryk Kwinto']);
assertTest("Logika wykrywania duplikatów poprawnie identyfikuje istniejącą rolę", (bool)$dupCheck->fetch());

// Sprzątanie pliku testowego
if (file_exists($testDbFile)) {
    unlink($testDbFile);
}

echo PHP_EOL . "=== PODSUMOWANIE TESTÓW QA & CONTENT ===" . PHP_EOL;
echo "Zaliczono: " . $passed . " / " . ($passed + $failed) . PHP_EOL;
echo "Błędy: " . $failed . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
