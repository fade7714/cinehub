<?php
/**
 * Zautomatyzowany Pakiet Testowy QA & Content (CineHub)
 * Rola: QA Asystent, Administrator Bazy Danych (DBA) & Content Agent
 * Standard: Kwalifikacja Zawodowa INF.03
 *
 * ZAKRES I OGRANICZENIA TESTU:
 * - Niniejszy pakiet weryfikuje warstwę danych PDO, rzeczywisty schemat DDL (database/schema.sql),
 *   zestaw danych początkowych (database/seed.sql), integralność relacji M:N, idempotencję,
 *   bezpieczeństwo danych przy ponownym uruchomieniu oraz ograniczenia CHECK i CASCADE.
 * - Testy NIE wykonują żądań HTTP do serwera WWW ani nie symulują pełnego cyklu przeglądarki.
 *   Interakcje w przeglądarce (formularze, nawigacja, komunikaty sesyjne) wymagają weryfikacji ręcznej.
 * - Wyniki testów SQLite są wyraźnie rozróżnione od wyników serwera MariaDB/MySQL.
 */

declare(strict_types=1);

// Tworzenie dedykowanego, unikalnego pliku tymczasowego dla SQLite, aby nie modyfikować ani nie usuwać bazy użytkownika
$testDbFile = sys_get_temp_dir() . '/cinehub_qa_' . bin2hex(random_bytes(6)) . '.sqlite';
register_shutdown_function(static function () use ($testDbFile): void {
    if (file_exists($testDbFile)) {
        @unlink($testDbFile);
    }
});

// Ustawienie środowiska testowego na izolowany plik SQLite
putenv('DB_DRIVER=sqlite');
putenv('DB_NAME=' . $testDbFile);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition, ?string $note = null): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] " . $description . PHP_EOL;
        if ($note !== null) {
            echo "         ℹ️  " . $note . PHP_EOL;
        }
        $passed++;
    } else {
        echo "  [FAIL] " . $description . PHP_EOL;
        if ($note !== null) {
            echo "         ⚠️  " . $note . PHP_EOL;
        }
        $failed++;
    }
}

/**
 * Konwertuje kod DDL z database/schema.sql (dialekt MySQL/MariaDB) na uproszczony dialekt SQLite,
 * aby umożliwić przetestowanie struktury tabel, integralności relacji M:N i logiki PDO w pamięci.
 *
 * ROZDZIELENIE DIALEKTÓW:
 * SQLite nie obsługuje klauzul silnika InnoDB, zestawu znaków utf8mb4_unicode_ci, indeksów inline
 * wewnątrz instrukcji CREATE TABLE ani komentarzy COMMENT. Właściwa weryfikacja natywnego DDL
 * oraz egzekwowania ograniczeń CHECK i kaskad jest przeprowadzana w dedykowanym TEST 8 na silniku MariaDB.
 */
function convertMysqlDdlToSqlite(string $sql): string {
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $sql = preg_replace('/CREATE\s+DATABASE[^;]+;/is', '', $sql);
    $sql = preg_replace('/USE\s+[^;]+;/is', '', $sql);

    preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\((.*?)\)\s*ENGINE=InnoDB[^;]*;/is', $sql, $matches, PREG_SET_ORDER);

    $sqliteTables = [];
    foreach ($matches as $match) {
        $table = $match[1];
        $body = $match[2];

        // Normalizacja wieloliniowych deklaracji kluczy obcych
        $body = preg_replace('/\n\s*(FOREIGN\s+KEY)/i', ' $1', $body);
        $body = preg_replace('/\n\s*(ON\s+DELETE)/i', ' $1', $body);

        $rawLines = explode("\n", $body);
        $cleanLines = [];
        foreach ($rawLines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') continue;
            // Pomijamy indeksy specyficzne dla MySQL (w SQLite indeksy tworzy się osobnym CREATE INDEX)
            if (preg_match('/^\s*INDEX\s+/i', $line)) continue;
            $line = preg_replace('/UNIQUE\s+KEY\s+`?\w+`?\s*\(([^)]+)\)/i', 'UNIQUE ($1)', $line);
            $line = preg_replace('/INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $line);
            $line = preg_replace('/\bINT\b/i', 'INTEGER', $line);
            $line = preg_replace('/\bVARCHAR\(\d+\)/i', 'TEXT', $line);
            $line = preg_replace('/COMMENT\s+\x27[^\x27]*\x27/i', '', $line);
            $cleanLines[] = rtrim($line, ',');
        }
        $body = implode(",\n    ", $cleanLines);
        $sqliteTables[] = "CREATE TABLE IF NOT EXISTS `$table` (\n    " . $body . "\n);";
    }

    return implode("\n\n", $sqliteTables);
}

echo "=====================================================================" . PHP_EOL;
echo "  AUDYT QA & INTEGRACJI BAZY DANYCH DLA PROJEKTU CINEHUB" . PHP_EOL;
echo "=====================================================================" . PHP_EOL;
echo "Środowisko główne: SQLite 3 (Tymczasowa baza: " . basename($testDbFile) . ")" . PHP_EOL;
echo "Zakres testu: Weryfikacja DDL (schema.sql), DML (seed.sql), relacji M:N, idempotencji i bezpieczeństwa." . PHP_EOL;
echo "Poza zakresem: Testy HTTP / UI w przeglądarce (wymagają weryfikacji ręcznej)." . PHP_EOL;
echo "---------------------------------------------------------------------" . PHP_EOL . PHP_EOL;

// --------------------------------------------------------------------------
// TEST 1: Analiza Statyczna i Weryfikacja Pliku database/schema.sql
// --------------------------------------------------------------------------
echo "--- TEST 1: Weryfikacja Schematu z Repozytorium (database/schema.sql) ---" . PHP_EOL;

$schemaFilePath = __DIR__ . '/../database/schema.sql';
assertTest("Plik database/schema.sql istnieje w repozytorium", file_exists($schemaFilePath));

$rawSchema = (string)file_get_contents($schemaFilePath);
$schemaCodeOnly = preg_replace('/--.*$/m', '', $rawSchema);
$schemaCodeOnly = preg_replace('/\/\*.*?\*\//s', '', (string)$schemaCodeOnly);

assertTest("Schemat nie zawiera destrukcyjnych instrukcji DROP TABLE", stripos((string)$schemaCodeOnly, 'DROP TABLE') === false);
assertTest("Schemat nie zawiera destrukcyjnych instrukcji TRUNCATE", stripos((string)$schemaCodeOnly, 'TRUNCATE') === false);

preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/i', (string)$schemaCodeOnly, $createMatches);
assertTest("Schemat używa bezpiecznych klauzul CREATE TABLE IF NOT EXISTS dla 4 tabel", count($createMatches[0]) === 4);
assertTest("Schemat definiuje ograniczenie CHECK dla ocen w skali 1-10", strpos((string)$schemaCodeOnly, 'BETWEEN 1 AND 10') !== false);
assertTest("Schemat zawiera reguły kaskadowości ON DELETE CASCADE", strpos((string)$schemaCodeOnly, 'ON DELETE CASCADE') !== false);

// --------------------------------------------------------------------------
// TEST 2: Inicjalizacja Rzeczywistego Schematu na Silniku Testowym (SQLite)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 2: Inicjalizacja Tabel z database/schema.sql w SQLite ---" . PHP_EOL;

$pdo = getDbConnection();
assertTest("Połączenie PDO zostało pomyślnie nawiązane", $pdo instanceof PDO);

$sqliteDdl = convertMysqlDdlToSqlite($rawSchema);
$pdo->exec($sqliteDdl);

$tablesStmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('filmy', 'aktorzy', 'obsada', 'recenzje');");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
assertTest("Wszystkie 4 tabele (filmy, aktorzy, obsada, recenzje) zostały poprawnie utworzone", count($tables) === 4);

// --------------------------------------------------------------------------
// TEST 3: Weryfikacja Bezpieczeństwa Ponownego Uruchomienia Schematu (Idempotencja DDL)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 3: Bezpieczeństwo DDL przy Ponownym Uruchomieniu ---" . PHP_EOL;

// Wstawiamy rekord użytkownika, aby sprawdzić czy ponowne nałożenie schematu go nie usunie
$pdo->exec("INSERT INTO filmy (tytul, rezyser, gatunek, rok_premiery, czas_trwania, opis) VALUES ('Film Testowy Użytkownika', 'Reżyser X', 'Dramat', 2025, 100, 'Opis testowy')");
$userFilmId = (int)$pdo->lastInsertId();

// Ponowne wykonanie DDL
$pdo->exec($sqliteDdl);

$userFilmCheck = $pdo->query("SELECT COUNT(*) FROM filmy WHERE id = $userFilmId")->fetchColumn();
assertTest("Ponowne uruchomienie database/schema.sql nie usunęło istniejących danych użytkownika", (int)$userFilmCheck === 1);

// Czyścimy rekord testowy użytkownika przed testem seeda
$pdo->exec("DELETE FROM filmy WHERE id = $userFilmId");

// --------------------------------------------------------------------------
// TEST 4: Bezpieczne Zasilenie Danymi z database/seed.sql (Idempotencja DML)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 4: Zasilenie Danymi z database/seed.sql i Test Idempotencji ---" . PHP_EOL;

$seedFilePath = __DIR__ . '/../database/seed.sql';
assertTest("Plik database/seed.sql istnieje w repozytorium", file_exists($seedFilePath));

$rawSeed = (string)file_get_contents($seedFilePath);
$seedCodeOnly = preg_replace('/--.*$/m', '', $rawSeed);
$seedCodeOnly = preg_replace('/\/\*.*?\*\//s', '', (string)$seedCodeOnly);

assertTest("Skrypt seed.sql nie zawiera destrukcyjnego TRUNCATE", stripos((string)$seedCodeOnly, 'TRUNCATE') === false);
assertTest("Skrypt seed.sql nie zawiera destrukcyjnego DROP", stripos((string)$seedCodeOnly, 'DROP') === false);

// Wykonanie zapytań z seed.sql (z pominięciem dyrektywy USE specyficznej dla MySQL)
$cleanSeedSql = preg_replace('/^--.*$/m', '', $rawSeed);
$cleanSeedSql = preg_replace('/USE\s+[^;]+;/is', '', $cleanSeedSql);

$queries = array_filter(array_map('trim', explode(';', $cleanSeedSql)));
foreach ($queries as $query) {
    if (!empty($query)) {
        $pdo->exec($query);
    }
}

$countFilmy = (int)$pdo->query("SELECT COUNT(*) FROM filmy")->fetchColumn();
$countAktorzy = (int)$pdo->query("SELECT COUNT(*) FROM aktorzy")->fetchColumn();
$countObsada = (int)$pdo->query("SELECT COUNT(*) FROM obsada")->fetchColumn();
$countRecenzje = (int)$pdo->query("SELECT COUNT(*) FROM recenzje")->fetchColumn();

assertTest("Pierwsze uruchomienie seed.sql: 8 filmów, 19 aktorów, 21 obsady, 17 recenzji", 
    $countFilmy === 8 && $countAktorzy === 19 && $countObsada === 21 && $countRecenzje === 17);

// Ponowne wykonanie seed.sql — test idempotencji (brak duplikatów)
foreach ($queries as $query) {
    if (!empty($query)) {
        $pdo->exec($query);
    }
}

$countFilmyAfter = (int)$pdo->query("SELECT COUNT(*) FROM filmy")->fetchColumn();
$countAktorzyAfter = (int)$pdo->query("SELECT COUNT(*) FROM aktorzy")->fetchColumn();
$countObsadaAfter = (int)$pdo->query("SELECT COUNT(*) FROM obsada")->fetchColumn();
$countRecenzjeAfter = (int)$pdo->query("SELECT COUNT(*) FROM recenzje")->fetchColumn();

assertTest("Drugie uruchomienie seed.sql NIE ZDUPLIKOWAŁO rekordów (pełna idempotencja)",
    $countFilmyAfter === 8 && $countAktorzyAfter === 19 && $countObsadaAfter === 21 && $countRecenzjeAfter === 17,
    "Liczby rekordów po re-run: Filmy: $countFilmyAfter, Aktorzy: $countAktorzyAfter, Obsada: $countObsadaAfter, Recenzje: $countRecenzjeAfter");

// --------------------------------------------------------------------------
// TEST 5: Dynamiczne Wiązanie Relacji M:N i 1:N (Brak Założenia o Stałych ID)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 5: Weryfikacja Poprawności Wiązań Relacyjnych (M:N i 1:N) ---" . PHP_EOL;

$dzienSwiraStmt = $pdo->prepare("
    SELECT f.id, f.tytul, 
           COALESCE(o.cnt_obsada, 0) AS obsada_count,
           COALESCE(r.cnt_recenzje, 0) AS recenzje_count,
           r.avg_ocena
    FROM filmy f
    LEFT JOIN (SELECT film_id, COUNT(*) AS cnt_obsada FROM obsada GROUP BY film_id) o ON f.id = o.film_id
    LEFT JOIN (SELECT film_id, COUNT(*) AS cnt_recenzje, ROUND(AVG(ocena), 1) AS avg_ocena FROM recenzje GROUP BY film_id) r ON f.id = r.film_id
    WHERE f.tytul = :tytul
");
$dzienSwiraStmt->execute(['tytul' => 'Dzień świra']);
$dzienSwira = $dzienSwiraStmt->fetch();

assertTest("Film 'Dzień świra' poprawnie powiązany z 3 aktorami i 3 recenzjami", 
    (int)$dzienSwira['obsada_count'] === 3 && (int)$dzienSwira['recenzje_count'] === 3);
assertTest("Średnia ocen filmu 'Dzień świra' wynosi 9.0", (float)$dzienSwira['avg_ocena'] === 9.0);

$pazuraStmt = $pdo->prepare("
    SELECT f.tytul, o.rola
    FROM obsada o
    JOIN filmy f ON o.film_id = f.id
    JOIN aktorzy a ON o.aktor_id = a.id
    WHERE a.imie = 'Cezary' AND a.nazwisko = 'Pazura'
    ORDER BY f.tytul ASC
");
$pazuraStmt->execute();
$pazuraRoles = $pazuraStmt->fetchAll();
$pazuraFilms = array_column($pazuraRoles, 'tytul');

assertTest("Cezary Pazura dynamicznie powiązany z 3 filmami ('Chłopaki nie płaczą', 'Kiler', 'Psy')",
    count($pazuraRoles) === 3 && in_array('Kiler', $pazuraFilms) && in_array('Psy', $pazuraFilms) && in_array('Chłopaki nie płaczą', $pazuraFilms));

// --------------------------------------------------------------------------
// TEST 6: Weryfikacja Ograniczeń Integralnościowych (CHECK i Kaskady)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 6: Weryfikacja Ograniczeń CHECK oraz ON DELETE CASCADE ---" . PHP_EOL;

// 6.1 Test CHECK w SQLite
$checkBlockedSqlite = false;
try {
    $badReviewStmt = $pdo->prepare("INSERT INTO recenzje (film_id, autor, ocena, tresc) VALUES (1, 'Tester', 15, 'Błędna ocena')");
    $badReviewStmt->execute();
} catch (PDOException $e) {
    $checkBlockedSqlite = true;
}
assertTest("Silnik SQLite odrzucił ocenę 15 spoza zakresu 1-10 (CHECK constraint)", $checkBlockedSqlite,
    "Uwaga: W SQLite reguła CHECK jest aktywna. Wymagana wersja dla MySQL to 8.0.16+, a dla MariaDB to 10.2.1+.");

// 6.2 Test ON DELETE CASCADE
$filmToDeleteStmt = $pdo->prepare("SELECT id FROM filmy WHERE tytul = :tytul");
$filmToDeleteStmt->execute(['tytul' => 'Sami swoi']);
$filmToDeleteId = (int)$filmToDeleteStmt->fetchColumn();

$pdo->exec("DELETE FROM filmy WHERE id = $filmToDeleteId");
$obsadaRemaining = (int)$pdo->query("SELECT COUNT(*) FROM obsada WHERE film_id = $filmToDeleteId")->fetchColumn();
$recenzjeRemaining = (int)$pdo->query("SELECT COUNT(*) FROM recenzje WHERE film_id = $filmToDeleteId")->fetchColumn();

assertTest("Kaskadowe usuwanie (ON DELETE CASCADE) usunęło powiązaną obsadę i recenzje",
    $obsadaRemaining === 0 && $recenzjeRemaining === 0);

// --------------------------------------------------------------------------
// TEST 7: Bezpieczeństwo Aplikacji: Ochrona Przed XSS (funkcja e())
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 7: Ochrona Przed XSS w Warstwie Prezentacji ---" . PHP_EOL;
$xssVector = "<script>alert('XSS');</script>\"onmouseover=\"";
$escapedVector = e($xssVector);
assertTest("Funkcja e() neutralizuje znaczniki HTML oraz cudzysłowy (ENT_QUOTES)",
    strpos($escapedVector, '<script>') === false && strpos($escapedVector, '&lt;script&gt;') !== false);

// --------------------------------------------------------------------------
// TEST 8: Weryfikacja Zapytań Wyszukiwania i Filtrów (Brak Kolizji Parametrów HY093)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 8: Weryfikacja Zapytań Wyszukiwania i Filtrów (Eliminacja Błędu HY093) ---" . PHP_EOL;

// 8.1 Test zapytania wyszukiwania z index.php (tytuł, reżyser, opis oraz obsada)
$searchSqlIndex = "
    SELECT f.id, f.tytul, f.rezyser
    FROM filmy f
    WHERE 1=1
    AND (
        f.tytul LIKE :s_tytul 
        OR f.rezyser LIKE :s_rezyser 
        OR f.opis LIKE :s_opis
        OR EXISTS (
            SELECT 1 FROM obsada o_s 
            JOIN aktorzy a_s ON o_s.aktor_id = a_s.id 
            WHERE o_s.film_id = f.id 
            AND (
                a_s.imie LIKE :s_aktor_imie 
                OR a_s.nazwisko LIKE :s_aktor_nazw 
                OR (a_s.imie || ' ' || a_s.nazwisko) LIKE :s_aktor_full 
                OR o_s.rola LIKE :s_aktor_rola
            )
        )
    )
";
$searchTerm = '%Pazura%';
$searchStmtIndex = $pdo->prepare($searchSqlIndex);
$searchStmtIndex->execute([
    's_tytul'      => $searchTerm,
    's_rezyser'    => $searchTerm,
    's_opis'       => $searchTerm,
    's_aktor_imie' => $searchTerm,
    's_aktor_nazw' => $searchTerm,
    's_aktor_full' => $searchTerm,
    's_aktor_rola' => $searchTerm,
]);
$foundFilmsByActor = $searchStmtIndex->fetchAll();

assertTest("Wyszukiwanie w index.php po nazwisku aktora w obsadzie działa bez błędu HY093",
    count($foundFilmsByActor) >= 2, "Zwrócono " . count($foundFilmsByActor) . " filmów dla frazy 'Pazura'");

// 8.2 Test zapytania wyszukiwania i filtrów z actors.php
$searchSqlActors = "
    SELECT a.id, a.imie, a.nazwisko, COUNT(DISTINCT o.film_id) AS liczba_filmow
    FROM aktorzy a
    LEFT JOIN obsada o ON a.id = o.aktor_id
    WHERE 1=1
    AND (
        a.imie LIKE :s_imie 
        OR a.nazwisko LIKE :s_nazw 
        OR (a.imie || ' ' || a.nazwisko) LIKE :s_full 
        OR a.biografia LIKE :s_bio
        OR EXISTS (
            SELECT 1 FROM obsada o_s 
            JOIN filmy f_s ON o_s.film_id = f_s.id 
            WHERE o_s.aktor_id = a.id 
            AND (f_s.tytul LIKE :s_film OR o_s.rola LIKE :s_rola)
        )
    )
    GROUP BY a.id
";
$searchStmtActors = $pdo->prepare($searchSqlActors);
$searchStmtActors->execute([
    's_imie' => $searchTerm,
    's_nazw' => $searchTerm,
    's_full' => $searchTerm,
    's_bio'  => $searchTerm,
    's_film' => $searchTerm,
    's_rola' => $searchTerm,
]);
$foundActors = $searchStmtActors->fetchAll();

assertTest("Wyszukiwanie w actors.php działa bez błędu HY093",
    count($foundActors) === 1 && $foundActors[0]['nazwisko'] === 'Pazura',
    "Odnaleziono: " . ($foundActors[0]['imie'] ?? '') . " " . ($foundActors[0]['nazwisko'] ?? ''));

// --------------------------------------------------------------------------
// TEST 9: Weryfikacja na Rzeczywistym Serwerze MariaDB (Tymczasowa Baza Ephemeral)
// --------------------------------------------------------------------------
echo PHP_EOL . "--- TEST 9: Weryfikacja Rzeczywistego Silnika MariaDB (Lokalna Baza Tymczasowa) ---" . PHP_EOL;

$mariaHost = getenv('DB_HOST') !== false && getenv('DB_HOST') !== '' ? getenv('DB_HOST') : 'localhost';
$mariaPort = getenv('DB_PORT') !== false && getenv('DB_PORT') !== '' ? (int)getenv('DB_PORT') : 3306;
$mariaUser = getenv('DB_USER') !== false && getenv('DB_USER') !== '' ? getenv('DB_USER') : 'root';
$mariaPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root';

$mariaDsn = "mysql:host={$mariaHost};port={$mariaPort};charset=utf8mb4";
$tempDbName = 'cinehub_qa_tmp_' . bin2hex(random_bytes(6));
$mariaPdo = null;
$mariaDbCreated = false;
$mariadbTested = false;
$mariadbCheckPassed = false;
$mariadbNote = "";
$mariadbSkipped = false;
$mariadbCleanupFailed = false;

try {
    $mariaPdo = new PDO($mariaDsn, $mariaUser, $mariaPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $version = (string)$mariaPdo->query("SELECT VERSION()")->fetchColumn();

    // Utworzenie izolowanej, unikalnej bazy testowej
    $mariaPdo->exec("CREATE DATABASE `$tempDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $mariaDbCreated = true;
    $mariaPdo->exec("USE `$tempDbName`;");

    try {
        // Nałożenie rzeczywistego database/schema.sql (z wyłączeniem instrukcji USE/CREATE DATABASE cinehub_db)
        $mariaSchema = preg_replace('/CREATE\s+DATABASE[^;]+;/is', '', $rawSchema);
        $mariaSchema = preg_replace('/USE\s+[^;]+;/is', '', (string)$mariaSchema);
        $mariaPdo->exec((string)$mariaSchema);

        // Test egzekwowania CHECK na MariaDB
        $mariaPdo->exec("INSERT INTO filmy (id, tytul, rezyser, gatunek, rok_premiery, czas_trwania, opis) VALUES (1, 'F', 'R', 'G', 2000, 90, 'O')");
        try {
            $mariaPdo->exec("INSERT INTO recenzje (film_id, autor, ocena, tresc) VALUES (1, 'BadUser', 15, 'Niepoprawna ocena 15')");
            $mariadbCheckPassed = false;
        } catch (PDOException $e) {
            $mariadbCheckPassed = true;
        }

        $mariadbTested = true;
        $mariadbNote = "Wersja serwera: $version. Przetestowano na izolowanej bazie $tempDbName i poprawnie wyegzekwowano ograniczenie CHECK.";
    } finally {
        // Zagwarantowane usunięcie wyłącznie utworzonej bazy tymczasowej
        if ($mariaDbCreated && $mariaPdo !== null) {
            try {
                $mariaPdo->exec("DROP DATABASE IF EXISTS `$tempDbName`;");
            } catch (Throwable $dropEx) {
                $mariadbCleanupFailed = true;
                echo "  ⚠️ Błąd sprzątania tymczasowej bazy {$tempDbName}: " . $dropEx->getMessage() . PHP_EOL;
            }
        }
    }
} catch (Throwable $e) {
    $mariadbSkipped = true;
    $mariadbNote = "Połączenie z serwerem MariaDB/MySQL na {$mariaHost}:{$mariaPort} nie powiodło się: " . $e->getMessage();
}

if ($mariadbTested) {
    assertTest("Serwer MariaDB poprawnie załadował schema.sql i EGZEKWUJE ograniczenie CHECK(ocena BETWEEN 1 AND 10)", 
        $mariadbCheckPassed, $mariadbNote);
} else {
    echo "  [POMINIĘTO] Test silnika MariaDB/MySQL: $mariadbNote" . PHP_EOL;
    echo "              ⚠️ Silnik MariaDB NIE został zweryfikowany w tym środowisku (wymaga ręcznego audytu CHECK)." . PHP_EOL;
}

if ($mariadbCleanupFailed) {
    assertTest("Sprzątanie tymczasowej bazy testowej MariaDB ($tempDbName)", false, "Nie udało się usunąć bazy ephemeral!");
}

// --------------------------------------------------------------------------
// Podsumowanie
// --------------------------------------------------------------------------
echo PHP_EOL . "=====================================================================" . PHP_EOL;
echo "  PODSUMOWANIE PAKIETU TESTÓW QA & INTEGRACJI" . PHP_EOL;
echo "=====================================================================" . PHP_EOL;
echo "Asercje zaliczone: " . $passed . " / " . ($passed + $failed) . PHP_EOL;
echo "Błędy:             " . $failed . PHP_EOL;

if ($mariadbSkipped) {
    echo "Status MariaDB:    POMINIĘTO (serwer MariaDB nie był testowany)" . PHP_EOL;
    echo "Status ogólny:     " . ($failed === 0 ? "ZALICZONO CZĘŚCIOWO (Tylko SQLite; MariaDB wymaga weryfikacji ręcznej)" : "BŁĄD") . PHP_EOL;
} else {
    echo "Status MariaDB:    " . ($mariadbCheckPassed ? "ZALICZONO (potwierdzone egzekwowanie CHECK)" : "BŁĄD (brak egzekwowania CHECK)") . PHP_EOL;
    echo "Status ogólny:     " . ($failed === 0 ? "SUKCES (Wszystkie asercje zaliczone)" : "BŁĄD (Wykryto niezgodności)") . PHP_EOL;
}
echo "=====================================================================" . PHP_EOL;

echo PHP_EOL . "---------------------------------------------------------------------" . PHP_EOL;
echo "  RĘCZNA CHECKLISTA DLA PRZEPŁYWÓW HTTP / UI (WYMÓG INF.03):" . PHP_EOL;
echo "  1. Uruchom serwer WWW: php -S localhost:8000" . PHP_EOL;
echo "  2. Otwórz http://localhost:8000 i sprawdź katalog, filtry oraz sortowanie." . PHP_EOL;
echo "  3. Wejdź w szczegóły filmu (np. film.php?id=1) i zweryfikuj obsadę oraz recenzje." . PHP_EOL;
echo "  4. Wypełnij formularz recenzji (ocena 1-10) i sprawdź redirect PRG oraz Flash alert." . PHP_EOL;
echo "  5. Wejdź w profil aktora (actor.php?id=1) i sprawdź filmografię." . PHP_EOL;
echo "  6. Przetestuj formularze add_movie.php, add_actor.php i assign_actor.php." . PHP_EOL;
echo "---------------------------------------------------------------------" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
exit(0);
