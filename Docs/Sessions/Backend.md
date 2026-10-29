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

## 4. Rekomendowane Kolejne Kroki
1. **Frontend Developer:**
   - Rozbudowa interakcji w warstwie klienta (np. dynamiczny komponent wyboru gwiazdek dla ocen w `film.php`, walidacja JavaScript w czasie rzeczywistym przed wysłaniem formularza).
   - Opracowanie podstron dodawania filmów (`add_movie.php`), aktorów (`add_actor.php`) i przypisywania obsady (`assign_actor.php`).
2. **QA Asystent:**
   - Przeprowadzenie testów formularza recenzji (weryfikacja walidacji granicznej: ocena < 1, ocena > 10, puste pola).
   - Weryfikacja odporności na próby wstrzykiwania kodu `<script>` i znaków specjalnych HTML.

