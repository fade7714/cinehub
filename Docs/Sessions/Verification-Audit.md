# RAPORT Z SESJI: KOMPLEKSOWY AUDYT STANU PROJEKTU I BAZY DANYCH
**Data audytu:** 2026-11-12  
**Rola wykonująca:** Główny Agent (Orchestrator) & Pełny Zespół Agentów  
**Status weryfikacji:** Zakończona sukcesem – System w pełni sprawny, zsynchronizowany i gotowy do dalszego rozwoju  

---

## 1. Cel i Zakres Weryfikacji
Celem sesji było przeprowadzenie wszechstronnego przeglądu stanu projektu **CineHub** (PHP 8 / MySQL) zgodnie z regułami zawartymi w `Docs/workflow.md`, poprzez weryfikację prac każdego ze specjalistycznych agentów:
1. **Planner:** Zgodność zakresu funkcjonalnego ze specyfikacją [`Docs/Specs.md`](../Specs.md).
2. **Backend Developer & Data Analyst:** Weryfikacja definicji bazy danych ([`database/schema.sql`](../../database/schema.sql)), połączenia PDO ([`config/database.php`](../../config/database.php)) oraz zapytań SQL.
3. **Frontend Developer:** Poprawność struktury widoków, responsywności CSS Dark Mode oraz interakcji JavaScript.
4. **Content Agent:** Integralność danych początkowych ([`database/seed.sql`](../../database/seed.sql)) – baza filmów, aktorów, ról i recenzji.
5. **QA Asystent:** Testy integracyjne, testy na żywym serwerze MariaDB, eliminacja ostrzeżeń i błędów wykonania.
6. **Agent ds. Dokumentacji GitHub:** Spójność dokumentacji [`README.md`](../../README.md) oraz [`Docs/Final-Report.md`](../Final-Report.md) z faktycznym stanem kodu.

---

## 2. Raport ze Sprawdzenia Poszczególnych Ról

### 2.1 Planner – Wymagania i Architektura
- **Kryteria ukończenia:** Wszystkie 4 kluczowe moduły zdefiniowane w `Docs/Specs.md` posiadają pełne odzwierciedlenie w kodzie:
  - Moduł filmów (katalog, wyszukiwarka, filtrowanie, widok pojedynczego filmu, dodawanie) – `index.php`, `film.php`, `add_movie.php`.
  - Moduł aktorów (kartoteka, profil, filmografia, dodawanie) – `actors.php`, `actor.php`, `add_actor.php`.
  - Moduł relacji M:N (obsada, postacie, kaskadowość) – tabela `obsada`, formularz `assign_actor.php`.
  - Moduł recenzji i ocen (skala 1-10, agregacja ocen, walidacja) – tabela `recenzje`, endpoint w `film.php`.
- **Diagramy Mermaid:** Diagram architektury (`flowchart TD`) oraz diagram relacji encji (`erDiagram`) odzwierciedlają fizyczną implementację.

### 2.2 Backend Developer & Data Analyst – Połączenie i Baza Danych
- **Diagnoza połączenia z bazą:**
  - *Zidentyfikowany problem:* W pliku `config/database.php` parametr `DB_HOST` ustawiony był domyślnie na `127.0.0.1`. W lokalnym środowisku Linux serwer MariaDB ma wyłączony nasłuch sieciowy TCP (port 3306) i obsługuje połączenia przez gniazdo uniksowe (`/var/lib/mysql/mysql.sock`). W rezultacie połączenie PDO zwracało błąd `SQLSTATE[HY000] [2002] Connection refused`.
  - *Zastosowane rozwiązanie:* Zaktualizowano domyślny host na `localhost`, co w sterowniku PDO automatycznie kieruje komunikację przez wydajny i bezpośredni unix domain socket, a także poprawnie uwierzytelnia użytkownika `root:root`.
- **Weryfikacja schematu DDL (`database/schema.sql`):**
  - Wykonano import i weryfikację na działającej instancji MariaDB (`cinehub_db`).
  - Wszystkie 4 tabele (`filmy`, `aktorzy`, `obsada`, `recenzje`) zostały utworzone z zachowaniem kluczy obcych (`ON DELETE CASCADE ON UPDATE CASCADE`), indeksów optymalizacyjnych oraz ograniczenia `CHECK (ocena BETWEEN 1 AND 10)`.
- **Optymalizacja zapytań SQL:**
  - Potwierdzono brak iloczynu kartezjańskiego – zapytania w `index.php`, `film.php` i `actor.php` korzystają z pre-agregacji w tabelach pochodnych (`derived tables`), co gwarantuje pełną precyzję średniej ocen i wydajność.

### 2.3 Frontend Developer – Interfejs i Interakcje
- **Style CSS:** Plik `frontend/css/style.css` zapewnia spójny motyw Dark Mode (CSS Grid, Flexbox, Design Tokens, pełne RWD).
- **Skrypty JS:** Plik `frontend/js/main.js` realizuje interaktywny 10-gwiazdkowy system oceniania ze słownym podglądem, dynamiczny licznik znaków recenzji oraz walidację formularza po stronie klienta.
- **Bezpieczeństwo XSS:** Każde pole tekstowe wyprowadzane do HTML jest sanityzowane funkcją `e()` (`htmlspecialchars`).

### 2.4 Content Agent – Zasilenie Bazy Danymi
- **Zawartość `database/seed.sql`:**
  - Zaimportowano i potwierdzono obecność:
    - 8 polskich filmów (*Dzień świra*, *Chłopaki nie płaczą*, *Kiler*, *Boże Ciało*, *Zimna wojna*, *Psy*, *Znachor*, *Sami swoi*),
    - 19 wybitnych polskich aktorów z datami urodzenia, zdjęciami i biografiami,
    - 21 powiązań obsady w relacji M:N z autentycznymi nazwami postaci,
    - 17 recenzji z ocenami w przedziale 8–10 punktów.

### 2.5 QA Asystent – Audyt Testowy i Poprawki
- **Wyeliminowanie ostrzeżeń środowiskowych:**
  - Zastąpiono `filter_input(INPUT_GET/POST, ...)` bezpieczniejszym `filter_var($_GET/... ?? null, ...)` w plikach `film.php`, `actor.php` i `assign_actor.php`, co zapewnia stabilne działanie zarówno w środowisku przeglądarkowym, jak i w testach CLI/PHPUnit.
  - Zabezpieczono odczyt `$_SERVER['REQUEST_METHOD']` operatorem `?? ''` w formularzach POST.
- **Wyniki testów integracyjnych (`tests/qa_test.php`):**
  - Wszystkie asercje jednostkowe i integracyjne zakończone powodzeniem (PASS).
- **Wyniki testów renderowania stron na żywej bazie:**
  - `index.php`: 22 KB (OK)
  - `film.php`: 19 KB (OK)
  - `actor.php`: 4 KB (OK)
  - `actors.php`: 32 KB (OK)
  - `add_movie.php`: 3 KB (OK)
  - `add_actor.php`: 2 KB (OK)
  - `assign_actor.php`: 5 KB (OK)
  - Brak jakichkolwiek błędów, ostrzeżeń lub wyjątków w logach.

### 2.6 Agent ds. Dokumentacji GitHub – Spójność Dokumentów
- Zweryfikowano pliki `README.md`, `Docs/Specs.md`, `Docs/workflow.md` oraz `Docs/Final-Report.md`.
- Wszystkie nazwy plików, tabel, kolumn i instrukcje uruchomienia odpowiadają stanowi faktycznemu w repozytorium.

---

## 3. Zmodyfikowane Pliki w Sesji

| Plik | Zakres Modyfikacji |
| :--- | :--- |
| `config/database.php` | Zmiana domyślnego `DB_HOST` z `127.0.0.1` na `localhost` (obsługa gniazda unix MariaDB). |
| `film.php` | Bezpieczne pobieranie `id` przez `filter_var` oraz ochrona `REQUEST_METHOD`. |
| `actor.php` | Bezpieczne pobieranie `id` przez `filter_var`. |
| `assign_actor.php` | Bezpieczne pobieranie parametrów `filter_var` oraz ochrona `REQUEST_METHOD`. |
| `add_movie.php` | Ochrona `REQUEST_METHOD ?? ''`. |
| `add_actor.php` | Ochrona `REQUEST_METHOD ?? ''`. |
| `Docs/Sessions/Verification-Audit.md` | Niniejszy całościowy raport ze sprawdzenia stanu projektu. |

---

## 4. Dodatkowy Audyt i Rozwiązanie Problemów Krytycznych (2026-11-12)

W ramach kontrolowanej naprawy problemów krytycznych i wysokich zrealizowano:
1. **Ochrona przed usuwaniem danych:**
   - `database/schema.sql` przekształcono na bezpieczny DDL z `CREATE TABLE IF NOT EXISTS` bez `DROP TABLE`.
   - Utworzono odrębny, ostrzeżony skrypt `database/reset.sql` do opcjonalnego resetu deweloperskiego.
   - `database/seed.sql` został oczyszczony z `TRUNCATE` i oparty na zapytaniach warunkowych `WHERE NOT EXISTS` z dynamicznym wyznaczaniem relacji (brak stałych ID).
2. **Uwiarygodnienie pakietu testowego (`tests/qa_test.php`):**
   - Wyeliminowano ręczną kopię schematu SQLite na rzecz wczytywania i testowania rzeczywistego pliku `database/schema.sql`.
   - Dodano testy idempotencji DDL/DML oraz egzekwowania ograniczeń `CHECK` i `CASCADE`.
   - Dołączono test na izolowanej bazie ephemeral MariaDB 11.8 i sprzątanie wyłącznie zasobów tymczasowych.
   - Sprecyzowano zakres: testy PDO/SQL (testy nie symulują stosu HTTP).
3. **Ujednolicenie konfiguracji bazy:**
   - Domyślna konfiguracja `localhost`, `root`, `root` w kodzie i dokumentacji; elastyczne wsparcie dla pustego hasła (`export DB_PASS=""`) oraz TCP (`export DB_HOST="127.0.0.1"`).
4. **Weryfikacja wersji DBMS pod kątem ograniczenia CHECK:**
   - Zaktualizowano minimalne deklarowane wersje na **MySQL 8.0.16+** i **MariaDB 10.3+** z wyjaśnieniem, że MySQL 5.7 ignoruje ograniczenia `CHECK`.
5. **Naprawa Uwag z Testów Manualnych Użytkownika:**
   - **Błąd `SQLSTATE[HY093]`:** Usunięto powielony parametr `:szukaj` w zapytaniach SQL z wyłączoną emulacją preparowania (`PDO::ATTR_EMULATE_PREPARES => false`). Nadano unikalne nazwy placeholderom w `index.php` i `actors.php`.
   - **Wyszukiwarka filmów:** Rozszerzono zapytanie `index.php`, aby uwzględniało także aktorów z obsady i nazwy ról filmowych.
   - **Katalog aktorów (`actors.php`):** Wdrożono wyszukiwarkę tekstową, filtr według filmu (`film_id`) oraz sortowanie (alfabetyczne, wg liczby ról, wg roku urodzenia).
   - **Usprawnienia UX powiązań i recenzji:** Dodano pre-selekcję formularza `assign_actor.php` z poziomu filmu i aktora, przyciski skrótu w profilach oraz natychmiastowy baner sukcesu w sekcji `#recenzje` w `film.php`.
   - **Rozszerzenie testów:** Pakiet `tests/qa_test.php` rozbudowano o TEST 8 (test unikalności parametrów PDO); 23 / 23 asercje PASS.

---

## 5. Wnioski i Gotowość do Dalszej Pracy
Projekt **CineHub** jest spójny pod kątem architektury INF.03, bezpieczny dla danych użytkownika przy standardowym uruchamianiu i stabilny. Zautomatyzowane testy DDL/DML, egzekwowania reguły CHECK oraz bindowania parametrów (23/23) zakończyły się sukcesem. Uwagi z testów manualnych zostały kompleksowo rozwiązane.
