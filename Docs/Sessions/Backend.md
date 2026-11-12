# RAPORT Z SESJI: BACKEND DEVELOPER & DATA ANALYST
**Data sesji:** 2026-10-29  
**Rola wykonująca:** Backend Developer (łącząc rolę Data Analyst)  
**Nadzór:** Główny Agent (Orchestrator)  
**Status zadania:** Zakończone sukcesem  

---

## 1. Zakres Wykonanych Prac

### 1.1 Model Bazy Danych i Skrypty SQL (`database/schema.sql`, `database/seed.sql`)
- Zaprojektowano i utworzono zoptymalizowany schemat bazy danych `cinehub_db` zgodnie ze specyfikacją INF.03 z użyciem silnika **InnoDB** i kodowania **utf8mb4_unicode_ci**.
- Zdefiniowano 4 kluczowe tabele:
  1. `filmy` – dane podstawowe filmów (tytuł, reżyser, gatunek, rok, czas trwania, opis, plakat).
  2. `aktorzy` – kartoteka aktorów (imię, nazwisko, data urodzenia, zdjęcie, biografia).
  3. `obsada` – tabela asocjacyjna dla relacji wiele-do-wielu (M:N) pomiędzy filmami a aktorami, zawierająca pole `rola` (postać odgrywana przez aktora) oraz klucze obce z regułami `ON DELETE CASCADE ON UPDATE CASCADE` i indeksem unikalnym `uq_film_aktor_rola`.
  4. `recenzje` – tabela opinii i ocen w skali 1–10 z kluczem obcym do filmu (`ON DELETE CASCADE`).
- Jako **Data Analyst** dodano indeksy optymalizujące najczęstsze zapytania i złączenia: `idx_filmy_gatunek`, `idx_filmy_rok`, `idx_obsada_film`, `idx_obsada_aktor`, `idx_recenzje_film`, `idx_recenzje_ocena`.
- Przygotowano skrypt `database/seed.sql` z reprezentatywnym zbiorem danych początkowych (Incepcja, Pulp Fiction, Matrix, Interstellar, obsada DiCaprio, Travolta, Jackson, Reeves, McConaughey oraz recenzje).

### 1.2 Konfiguracja Połączenia PDO (`config/database.php`)
- Zaimplementowano bezpieczne połączenie PDO z obsługą zmiennych środowiskowych (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) oraz domyślnymi wartościami lokalnymi.
- Włączono tryb rzucania wyjątków (`PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`) oraz domyślne pobieranie tablic asocjacyjnych (`PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`).
- Zastosowano wyłączenie emulacji przygotowanych zapytań (`PDO::ATTR_EMULATE_PREPARES => false`) dla maksymalnego bezpieczeństwa przed SQL Injection na poziomie sterownika bazy.

### 1.3 Moduł Pomocniczy i Bezpieczeństwo (`includes/functions.php`, `includes/header.php`, `includes/footer.php`)
- Wdrożono funkcję `e()` opartą na `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` zapobiegającą atakom typu **XSS (Cross-Site Scripting)**.
- Wdrożono mechanizm komunikatów sesyjnych **Flash Messages** (`setFlash()`, `getFlash()`).
- Stworzono modularny nagłówek i stopkę strony.

### 1.4 Logika Strony Głównej (`index.php`)
- Zaimplementowano analityczne zapytanie SQL agregujące dane z tabel `filmy`, `obsada` i `recenzje`:
  - `ROUND(AVG(r.ocena), 1) AS srednia_ocen` (średnia ocen społeczności),
  - `COUNT(DISTINCT r.id) AS liczba_recenzji` (liczba recenzji),
  - `COUNT(DISTINCT o.id) AS liczba_aktorow` (liczba powiązanych aktorów w obsadzie).
- Zaimplementowano dynamiczne filtrowanie po tytule/reżyserze/opisie oraz gatunku, a także elastyczne sortowanie (rok malejąco/rosnąco, najwyższa ocena, tytuł A-Z).
- Zabezpieczono aplikację przed awarią przy braku połączenia z bazą (przyjazny komunikat diagnostyczny dla środowiska egzaminacyjnego INF.03).

### 1.5 Logika Widoku Szczegółów Filmu i Obsługa Recenzji (`film.php`)
- Zaimplementowano pobieranie pełnych danych filmu po identyfikatorze `id`.
- Zaimplementowano pobieranie obsady aktorskiej w relacji M:N (`JOIN aktorzy a ON o.aktor_id = a.id`).
- Zaimplementowano pobieranie i listowanie opinii i ocen.
- Zaimplementowano kompletną obsługę formularza recenzji (metoda POST):
  - Rygorystyczna walidacja po stronie serwera (pseudonim 1–100 znaków, ocena 1–10, treść min. 5 znaków).
  - Bezpieczny zapis z wykorzystaniem parametryzowanego zapytania PDO (**Prepared Statement**).
  - Zastosowanie wzorca **PRG (Post/Redirect/Get)** zapobiegającego powielaniu recenzji przy odświeżaniu strony (F5).

---

## 2. Zmodyfikowane i Utworzone Pliki

| Ścieżka | Typ | Opis |
| :--- | :--- | :--- |
| `database/schema.sql` | Utworzony | Struktura DDL bazy danych (tabele `filmy`, `aktorzy`, `obsada`, `recenzje`, klucze obce, indeksy). |
| `database/seed.sql` | Utworzony | Zestaw danych początkowych (filmy, aktorzy, obsada M:N, recenzje). |
| `config/database.php` | Utworzony | Konfiguracja połączenia PDO z MySQL/MariaDB. |
| `includes/functions.php` | Utworzony | Funkcje pomocnicze: ochrona XSS (`e()`), komunikaty Flash, formatowanie ocen. |
| `includes/header.php` | Utworzony | Wspólny nagłówek HTML5 z nawigacją i alertami. |
| `includes/footer.php` | Utworzony | Wspólna stopka HTML5. |
| `assets/css/style.css` | Utworzony | Bazowa stylizacja kinowa Dark Mode dla kart, tabel i formularzy. |
| `index.php` | Utworzony | Logika katalogu filmów, zapytań analitycznych, wyszukiwania i filtrów. |
| `film.php` | Utworzony | Logika szczegółów filmu, obsady M:N, listy recenzji i obsługi formularza POST. |
| `Docs/Specs.md` | Zaktualizowany | Synchronizacja nazw tabel (`filmy`, `aktorzy`, `obsada`, `recenzje`) i widoku `film.php`. |
| `Docs/Sessions/Backend.md` | Utworzony | Niniejszy raport z sesji. |

---

## 3. Zidentyfikowane Zagadnienia i Zastosowane Rozwiązania

1. **Optymalizacja zapytań agregujących (Data Analyst):**
   - *Problem:* Zliczanie jednoczesne powiązań z tabel `obsada` i `recenzje` w jednym zapytaniu bez `DISTINCT` mogłoby wygenerować iloczyn kartezjański zawyżający liczby.
   - *Rozwiązanie:* Zastosowano `COUNT(DISTINCT o.id)` oraz `COUNT(DISTINCT r.id)` wraz z `ROUND(AVG(r.ocena), 1)`.
2. **Kaskadowe usuwanie powiązań (Integrity Constraints):**
   - *Rozwiązanie:* W tabelach `obsada` i `recenzje` dodano `ON DELETE CASCADE ON UPDATE CASCADE`, co gwarantuje spójność bazy w przypadku usunięcia filmu lub aktora.
3. **Zabezpieczenie przed atakami Injection & XSS:**
   - *Rozwiązanie:* Wszystkie zapytania wykorzystują PDO Prepared Statements z tablicami asocjacyjnymi parametrów, a każdy ciąg tekstowy trafiający do HTML jest filtrowany funkcją `e()`.

---

## 4. Aktualizacja Sesji Backendowej: Optymalizacja Połączenia i Pobierania Danych (2026-11-12)
- **Współpraca z Administratorem Bazy Danych (DBA):**
  - Zaktualizowano domyślny host połączenia w `config/database.php` z `127.0.0.1` na `localhost`, umożliwiając sterownikowi PDO automatyczne korzystanie z gniazda uniksowego serwera MariaDB.
- **Bezpieczne pobieranie parametrów (`filter_var`):**
  - Zastąpiono funkcję `filter_input()` uniwersalną konstrukcją `filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT)` w plikach `film.php`, `actor.php` oraz `assign_actor.php`. Rozwiązuje to problem niedostępności bufora wejściowego w testach CLI i po wewnętrznych przekierowaniach.
- **Zabezpieczenie zmiennych serwerowych:**
  - Dodano bezpieczny odczyt `($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'` w `film.php`, `add_movie.php`, `add_actor.php` i `assign_actor.php`, całkowicie eliminując ostrzeżenia PHP o niezdefiniowanym indeksie tablicy.
- **Optymalizacja zapytań analitycznych:**
  - Zapytania pobierające filmy i statystyki oparto na podzapytaniach pre-agregujących (`derived tables`), co definitywnie usunęło iloczyn kartezjański i gwarantuje poprawność matematyczną średniej ocen.

---

## 5. Aktualizacja Sesji: Niezależność Identyfikatorów, Walidacja Ocen i Konfiguracja (2026-11-12)

### 5.1 Zakres Wykonanych Prac
1. **Weryfikacja dynamicznych identyfikatorów w kontrolerach:**
   - Przeanalizowano wszystkie skrypty kontrolerów (`index.php`, `film.php`, `actors.php`, `actor.php`, `add_movie.php`, `add_actor.php`, `assign_actor.php`).
   - Potwierdzono, że żaden plik backendowy nie posiada zahardkodowanych numerów `id` – wszystkie operacje bazują na dynamicznych parametrach z bazy danych lub żądań `$_GET`/`$_POST`.
2. **Podwójna linia obrony dla ocen (Defense-in-Depth):**
   - W `film.php` formularz recenzji posiada ścisłą walidację PHP: `filter_var($_POST['ocena'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]])`.
   - W połączeniu z bazodanowym ograniczeniem `CHECK (ocena BETWEEN 1 AND 10)` zapewnia to skuteczną ochronę przed nieprawidłowymi ocenami zarówno na poziomie aplikacji, jak i bazy danych.
3. **Elastyczna konfiguracja połączenia (`config/database.php`):**
   - Zaktualizowano odczyt hasła: `getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root'`, co pozwala bezkonfliktowo uruchamiać projekt w środowiskach z pustym hasłem (np. szkolny XAMPP: `export DB_PASS=""`), zachowując domyślne `root` dla środowisk ze skonfigurowanym kontem administracyjnym.
   - Utrzymano domyślny host `localhost` zapewniający bezpośrednie wykorzystanie gniazda uniksowego w systemach Linux oraz portu 3306 w środowiskach Windows i Docker.

### 5.2 Zmienione Pliki
- `config/database.php` – rozszerzona obsługa parametru hasła i dokumentacja.
- `index.php` – naprawa błędu HY093 (unikalne parametry PDO) i rozszerzenie wyszukiwania o obsadę.
- `actors.php` – naprawa błędu HY093, dodanie filtrowania po filmie i sortowania.
- `assign_actor.php` – obsługa pre-selekcji aktora i filmu oraz kontekstowe przekierowanie.
- `Docs/Sessions/Backend.md` – niniejszy raport.

### 5.3 Wyniki Weryfikacji
- Składnia PHP: 12/12 plików bez jakichkolwiek błędów (`php -l`).
- Testy integracyjne: Wszystkie asercje w `tests/qa_test.php` zakończone sukcesem.
- Renderowanie stron: Wszystkie 7 widoków generuje poprawny kod HTML.

### 5.4 Rozwiązanie Błędu SQLSTATE[HY093] (2026-11-12)
- **Przyczyna:** W `index.php` i `actors.php` pojedynczy parametr nazwany `:szukaj` występował trzykrotnie w klauzuli `WHERE ... LIKE :szukaj`. Przy wyłączonej emulacji prepares (`PDO::ATTR_EMULATE_PREPARES => false`) sterownik MySQL traktuje to jako niezgodność liczby tokenów z liczbą przekazanych zmiennych, rzucając wyjątek `SQLSTATE[HY093] Invalid parameter number`.
- **Rozwiązanie:** Wprowadzono unikalne placeholdery dla każdego warunku (`:s_tytul`, `:s_rezyser`, `:s_opis`, `:s_aktor_imie`, `:s_aktor_nazw`, `:s_aktor_full`, `:s_aktor_rola` w `index.php` oraz `:s_imie`, `:s_nazw`, `:s_full`, `:s_bio`, `:s_film`, `:s_rola` w `actors.php`).
- **Rozszerzenie logiki biznesowej:** Wyszukiwanie w katalogu filmów przeszukuje teraz również nazwiska aktorów i role w obsadzie za pomocą podzapytania `EXISTS`. W `actors.php` dodano możliwość filtrowania po filmie (`film_id`) oraz sortowanie.

---

## 6. Rekomendowane Kolejne Kroki
1. Bieżące monitorowanie zapytań w miarę rozrostu bazy danych.
2. Współpraca z QA Asystentem przy testach regresyjnych.

