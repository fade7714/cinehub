<?php
/**
 * Konfiguracja połączenia z bazą danych (PDO)
 * Projekt: CineHub (INF.03)
 * Rola: Backend Developer & Data Analyst
 * Zgodność z MySQL (domyślnie) oraz opcjonalnym sterownikiem SQLite dla środowisk testowych
 */

declare(strict_types=1);

// Parametry konfiguracyjne bazy danych
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql');
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'cinehub_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_CHARSET', 'utf8mb4');

/**
 * Zwraca instancję PDO (wzorzec Singleton w zakresie żądania)
 *
 * @return PDO
 * @throws PDOException w przypadku błędu połączenia
 */
function getDbConnection(): PDO {
    static $pdoInstance = null;

    if ($pdoInstance === null) {
        if (DB_DRIVER === 'sqlite') {
            $dsn = 'sqlite:' . DB_NAME;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdoInstance = new PDO($dsn, null, null, $options);
            $pdoInstance->exec('PRAGMA foreign_keys = ON;');
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                $pdoInstance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                throw new PDOException(
                    "Błąd połączenia z bazą danych '" . DB_NAME . "': " . $e->getMessage()
                );
            }
        }
    }

    return $pdoInstance;
}
