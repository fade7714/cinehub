# RAPORT Z SESJI: INTEGRACJA I ARCHITEKTURA BAZY DANYCH (DBA)
**Data sesji:** 2026-11-12  
**Rola wykonująca:** Administrator Bazy Danych (DBA)  
**Nadzór:** Główny Agent (Orchestrator)  
**Status integracji:** Zgodność schematu i stabilność połączenia potwierdzona w środowisku deweloperskim  

---

## 1. Cel Działań Administratora Bazy Danych
Rola Administratora Bazy Danych (DBA) została powołana w celu nadzoru nad techniczną, architektoniczną i logiczną integracją bazy danych z projektem CineHub. 

> **Zasada rozgraniczenia ról:**  
> DBA **nie wchodzi w rolę Content Agenta** – nie bada ani nie weryfikuje wartości poszczególnych rekordów filmowych czy biografii aktorów. Zadaniem DBA jest weryfikacja protokołów połączenia, uprawnień, poprawności schematu DDL, ograniczeń integralności, indeksów, silnika składowania oraz logiki operacji bazodanowych realizowanych przez aplikację PHP.

---

## 2. Diagnoza i Konfiguracja Połączenia PDO (`config/database.php`)

Podczas inspekcji środowiska wykonawczego przeprowadzono audyt warstwy łączności aplikacji z serwerem bazodanowym:
1. **Analiza protokołów połączeń (Socket vs TCP/IP):**
   - W domyślnej konfiguracji serwer MariaDB (wersja 11.8.8) na maszynie deweloperskiej działa w trybie lokalnym z wyłączonym nasłuchem sieciowym TCP (`bind-address` / `skip-networking`), akceptując żądania wyłącznie przez gniazdo uniksowe (`/var/lib/mysql/mysql.sock`).
   - W pliku `config/database.php` parametr `DB_HOST` ustawiony był na adres `127.0.0.1`. W bibliotece klienta MySQL/PDO podanie adresu IP zmusza sterownik do próby połączenia przez TCP na porcie 3306, co skutkowało błędem:
     `SQLSTATE[HY000] [2002] Connection refused`.
   - **Interwencja DBA:** Przestawiono domyślny `DB_HOST` z `127.0.0.1` na `localhost`. Zgodnie ze specyfikacją sterownika `pdo_mysql`, wartość `localhost` powoduje automatyczne i transparentne użycie bezpiecznego gniazda uniksowego.
2. **Uwierzytelnienie i konfiguracja PDO:**
   - Skonfigurowano parametry: baza `cinehub_db`, użytkownik `root`, hasło `root`, kodowanie `utf8mb4`.
   - Zweryfikowano parametry sterownika:
     - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` (natychmiastowe rzucanie wyjątków przy anomaliach SQL),
     - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC` (bezpieczne tablice asocjacyjne),
     - `PDO::ATTR_EMULATE_PREPARES => false` (natywne zapytania przygotowane na poziomie serwera bazy),
     - `SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci` (obsługa pełnego zestawu znaków międzynarodowych i polskich znaków diakrytycznych).
3. **Wsparcie dla środowisk testowych (CI):**
   - Zachowano wsparcie dla zmiennej środowiskowej `DB_DRIVER=sqlite` dla zautomatyzowanych testów integracyjnych w kontenerach bez aktywnej usługi MariaDB.

---

## 3. Audyt Schematu i Integralności DDL (`database/schema.sql`)

Sprawdzono poprawność definicji tabel pod kątem reguł normalizacji (3NF) oraz wymogów egzaminacyjnych kwalifikacji **INF.03**:

```text
       +-------------------+       1 : N       +-------------------+
       |       filmy       |-------------------|     recenzje      |
       +-------------------+                   +-------------------+
                 | 1
                 |
                 | N
       +-------------------+
       |      obsada       |  (Tabela asocjacyjna relacji M:N z rolą)
       +-------------------+
                 | N
                 |
                 | 1
       +-------------------+
       |      aktorzy      |
       +-------------------+
```

### 3.1 Weryfikacja Integralności Referencyjnej i Ograniczeń
1. **Tabela `filmy`:**
   - Klucz główny: `id INT AUTO_INCREMENT PRIMARY KEY`.
   - Indeksy wydajnościowe: `idx_filmy_gatunek (gatunek)`, `idx_filmy_rok (rok_premiery)`.
2. **Tabela `aktorzy`:**
   - Klucz główny: `id INT AUTO_INCREMENT PRIMARY KEY`.
   - Indeks wyszukiwania: `idx_aktorzy_nazwisko (nazwisko)`.
3. **Tabela `obsada` (Relacja M:N):**
   - Klucze obce:
     - `fk_obsada_film` -> `filmy(id) ON DELETE CASCADE ON UPDATE CASCADE`,
     - `fk_obsada_aktor` -> `aktorzy(id) ON DELETE CASCADE ON UPDATE CASCADE`.
   - Ochrona przed redundancją: `UNIQUE KEY uq_film_aktor_rola (film_id, aktor_id, rola)`.
   - Indeksy złączeń: `idx_obsada_film (film_id)`, `idx_obsada_aktor (aktor_id)`.
4. **Tabela `recenzje`:**
   - Klucz obcy: `fk_recenzje_film` -> `filmy(id) ON DELETE CASCADE ON UPDATE CASCADE`.
   - Ograniczenie wartości: `CHECK (ocena BETWEEN 1 AND 10)` – chroni bazę przed nieprawidłowymi ocenami na poziomie silnika relacyjnego.
   - Indeksy analityczne: `idx_recenzje_film (film_id)`, `idx_recenzje_ocena (ocena)`.

Wszystkie tabele korzystają z silnika transakcyjnego **InnoDB** z kodowaniem **utf8mb4_unicode_ci**.

---

## 4. Logika Integracji Bazy Danych z Backendem

1. **Wyeliminowanie Iloczynu Kartezjańskiego:**
   - Zastąpiono wielokrotne bezpośrednie `LEFT JOIN` podzapytaniami z pre-agregacją (`derived tables`) w plikach `index.php`, `film.php` i `actor.php`. Zapobiega to eksplozji kombinatorycznej wierszy i zapewnia optymalny czas wykonania oraz 100% precyzję wyliczania średniej `AVG(ocena)`.
2. **Zgodność ze standardem ANSI SQL:**
   - W pliku `assign_actor.php` zastąpiono specyficzną dla MySQL składnię `ON DUPLICATE KEY UPDATE` zapytaniem weryfikującym istnienie roli, co zapewnia pełną przenośność kodu.
3. **Prepared Statements:**
   - 100% zapytań we wszystkich plikach aplikacji korzysta z parametryzowanych zapytań PDO, co gwarantuje pełną ochronę przed atakami typu SQL Injection.

---

## 5. Aktualizacja Sesji DBA: Ochrona Przed Utratą Danych i Wersje DBMS (2026-11-12)

### 5.1 Zakres Wykonanych Działań
1. **Zabezpieczenie skryptu instalacyjnego DDL (`database/schema.sql`):**
   - Usunięto destrukcyjne instrukcje `DROP TABLE IF EXISTS` ze standardowej procedury instalacyjnej.
   - Wprowadzono klauzule `CREATE TABLE IF NOT EXISTS` dla wszystkich 4 tabel (`filmy`, `aktorzy`, `obsada`, `recenzje`).
   - Dodano precyzyjny nagłówek dokumentacyjny wyjaśniający, że skrypt służy do świeżej instalacji i nie stanowi narzędzia migracji.
2. **Wydzielenie operacji resetowania bazy (`database/reset.sql`):**
   - Utworzono dedykowany skrypt `database/reset.sql` zawierający sekwencję `SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS ...; SET FOREIGN_KEY_CHECKS = 1;` oraz ponowne utworzenie czystych tabel.
   - Opatrzono skrypt widocznym ostrzeżeniem o bezpowrotnym kasowaniu danych. Skrypt został całkowicie odseparowany od standardowej instrukcji instalacyjnej w `README.md`.
3. **Doprecyzowanie konfiguracji PDO (`config/database.php`):**
   - Zaktualizowano definicję `DB_PASS`: `getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root'`, co umożliwia przekazanie jawnie pustego hasła (`export DB_PASS=""`) w środowiskach typu XAMPP.
   - Pozostawiono `DB_HOST=localhost` gwarantujący automatyczne korzystanie z gniazda uniksowego w systemach Linux oraz portu 3306 w Windows/Docker.
4. **Analiza egzekwowania ograniczeń `CHECK` a wersje serwera MySQL / MariaDB:**
   - Przeprowadzono testy na lokalnym serwerze MariaDB 11.8.8 (baza ephemeral `cinehub_qa_tmp_*`). Potwierdzono, że silnik odrzuca oceny spoza zakresu 1-10 (`SQLSTATE[23000] CHECK constraint failed`).
   - Potwierdzono, że wersje MySQL starsze niż 8.0.16 (w tym MySQL 5.7) ignorują ograniczenia `CHECK`, dlatego oficjalną minimalną wersją projektu zadeklarowano **MySQL 8.0.16+** oraz **MariaDB 10.3+**.

### 5.2 Zmienione i Utworzone Pliki
- `database/schema.sql` – zmodyfikowany (usunięcie DROP TABLE, dodanie IF NOT EXISTS).
- `database/reset.sql` – utworzony (dedykowany, ostrzeżony reset bazy).
- `config/database.php` – zmodyfikowany (obsługa pustego hasła i dokumentacja socket/TCP).
- `Docs/Sessions/Database-integrity.md` – zaktualizowany niniejszy raport.

### 5.3 Wyniki Weryfikacji
- `tests/qa_test.php`: Testy 1, 2, 3 i 8 potwierdziły poprawność DDL, idempotencję oraz egzekwowanie `CHECK` na silniku MariaDB.
- Ponowne nałożenie `schema.sql` nie usunęło rekordu testowego użytkownika.

### 5.4 Pozostałe Kwestie
- W standardowej procedurze wdrożeniowej nie wolno łączyć `reset.sql` z normalnym uruchomieniem.

---

## 6. Podsumowanie Wdrożeniowe
- **Połączenie z bazą:** Aktywne, stabilne, domyślna konfiguracja szkolna spójna z `README.md`.
- **Stan schematu:** Bezpieczny dla danych użytkownika (brak DROP TABLE w instalacji, CREATE TABLE IF NOT EXISTS).
- **Kolejne kroki:** Okresowy audyt spójności indeksów i relacji przy rozszerzaniu bazy o nowe tabele.
