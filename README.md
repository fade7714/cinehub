# 🎬 CineHub – Internetowa Baza Filmowa

> **Aplikacja internetowa i relacyjna baza danych przygotowana w standardzie Kwalifikacji Zawodowej INF.03**  
> *Tworzenie i administrowanie stronami i bazami danych oraz aplikacjami internetowymi*

---

## 📌 Spis Treści
1. [O Projekcie i Przeznaczenie Aplikacji](#-o-projekcie-i-przeznaczenie-aplikacji)
2. [Główne Funkcjonalności](#-główne-funkcjonalności)
3. [Architektura Systemu i Bazy Danych](#-architektura-systemu-i-bazy-danych)
4. [Cykl Działania Aplikacji (User Journey)](#-cykl-działania-aplikacji-user-journey)
5. [Workflow Zespołu Wirtualnych Agentów](#-workflow-zespołu-wirtualnych-agentów)
6. [Struktura Katalogów](#-struktura-katalogów)
7. [Instrukcja Instalacji i Uruchomienia](#-instrukcja-instalacji-i-uruchomienia)
8. [Zautomatyzowane Testy Jakości (QA)](#-zautomatyzowane-testy-jakości-qa)
9. [Bezpieczeństwo i Dobre Praktyki](#-bezpieczeństwo-i-dobre-praktyki)

---

## 📖 O Projekcie i Przeznaczenie Aplikacji

**CineHub** to nowoczesna, w pełni responsywna aplikacja webowa napisana w czystym **PHP 8 (PDO)** z wykorzystaniem relacyjnej bazy danych **MySQL / MariaDB** oraz autorskiego frontendu w technologiach **HTML5, CSS3 (Dark Mode)** i **Vanilla JavaScript**.

Aplikacja została stworzona w celu kompleksowego zarządzania katalogiem dzieł kinematografii, obsadą aktorską oraz recenzjami widzów. Baza danych została domyślnie zasilona autentycznymi dziełami **polskiego kina** (*Dzień świra*, *Chłopaki nie płaczą*, *Kiler*, *Boże Ciało*, *Zimna wojna*, *Psy*, *Znachor*, *Sami swoi*), wybitnymi postaciami aktorskimi oraz merytorycznymi recenzjami.

Projekt stanowi wzorcową realizację wymagań egzaminacyjnych kwalifikacji **INF.03**, łącząc wysokie standardy inżynierii oprogramowania (architektura modularna, brak zewnętrznych frameworków, pełne bezpieczeństwo) z estetycznym i ergonomicznym interfejsem użytkownika.

Dokumentacja projektu jest dostępna również w formacie czystego tekstu w pliku [`README.txt`](README.txt).  
Szczegółowy plan projektu oraz założenia techniczne sporządzone przez Plannera znajdują się w raporcie sesyjnym [`Docs/Sessions/Planning.md`](Docs/Sessions/Planning.md) oraz w specyfikacji [`Docs/Specs.md`](Docs/Specs.md).

---

## ✨ Główne Funkcjonalności

- 🎞️ **Katalog i Wyszukiwarka Filmów:**
  - Przeglądanie filmów w postaci nowoczesnych kart filmowych z plakatami, rocznikami, gatunkami, czasem trwania i odznakami ocen społeczności.
  - Elastyczne wyszukiwanie pełnotekstowe (po tytule, reżyserze i opisie).
  - Filtrowanie według gatunku oraz wielowariantowe sortowanie (rok malejąco/rosnąco, najwyższa ocena, tytuł A-Z).
- 🎬 **Szczegóły Filmu i Obsada (Relacja M:N):**
  - Karta filmu z pełnym opisem fabuły, reżyserią i parametrami technicznymi.
  - Wyświetlanie powiązanej obsady aktorskiej (zdjęcia, imiona, nazwiska oraz odgrywane postacie).
  - Klikalne odnośniki prowadzące bezpośrednio do profili aktorów.
- ⭐ **System Recenzji i Dynamiczna Agregacja Ocen:**
  - Możliwość wystawienia recenzji z oceną w skali 1–10.
  - Autorski komponent interaktywnego wyboru gwiazdek z podglądem słownym oceny w czasie rzeczywistym.
  - Dynamiczne przeliczanie średniej arytmetycznej ocen i statystyk recenzji bez iloczynów kartezjańskich.
  - Zabezpieczenie przed duplikowaniem wpisów przy odświeżaniu strony (**wzorzec PRG – Post/Redirect/Get**).
- 🎭 **Baza i Profile Aktorów:**
  - Katalog twórców z biografiami, datami urodzenia i liczbą filmów w bazie.
  - Dedykowany profil aktora z pełną filmografią i odgrywanymi rolami.
- ➕ **Moduł Administracyjny:**
  - Dodawanie nowego filmu do bazy (`add_movie.php`).
  - Rejestracja profilu aktora (`add_actor.php`).
  - Przypisywanie ról w obsadzie filmu (`assign_actor.php`) z wykrywaniem i zapobieganiem duplikatom.

---

## 🏛️ Architektura Systemu i Bazy Danych

### Model Relacyjny Bazy Danych (ERD)

Aplikacja opiera się na 4 znormalizowanych tabelach połączonych kluczami obcymi z kaskadowością (`ON DELETE CASCADE ON UPDATE CASCADE`):

```text
       +-------------------+       1 : N       +-------------------+
       |       FILMY       |-------------------|     RECENZJE      |
       +-------------------+                   +-------------------+
                 | 1
                 |
                 | N
       +-------------------+
       |      OBSADA       |  (Tabela asocjacyjna relacji M:N z atrybutem rola)
       +-------------------+
                 | N
                 |
                 | 1
       +-------------------+
       |      AKTORZY      |
       +-------------------+
```

- **`filmy`:** `id`, `tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`, `created_at`.
- **`aktorzy`:** `id`, `imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`, `created_at`.
- **`obsada`:** `id`, `film_id` (FK), `aktor_id` (FK), `rola` (nazwa postaci), `created_at`. Unikalny indeks `(film_id, aktor_id, rola)` uniemożliwia dublowanie przypisań.
- **`recenzje`:** `id`, `film_id` (FK), `autor`, `ocena` (CHECK 1-10), `tresc`, `created_at`.

---

## 🔄 Cykl Działania Aplikacji (User Journey)

Aplikacja prowadzi użytkownika przez intuicyjny, spójny cykl interakcji:

```text
[ Strona Główna: index.php ]
       │
       ├──► Filtrowanie / Wyszukiwanie filmów
       │
       ▼
[ Karta Filmu: film.php?id=X ]
       │
       ├──► Zapoznanie się z opisem fabuły i średnią oceną
       ├──► Kliknięcie aktora w obsadzie ──► [ Profil Aktora: actor.php?id=Y ]
       │                                            │
       │                                            └──► Przeglądanie filmografii aktora
       │
       ├──► Wypełnienie formularza recenzji (autor, gwiazdki 1-10, treść)
       │         │
       │         ▼ (POST)
       │    [ Walidacja PHP + Zapis PDO ]
       │         │
       │         ▼ (Redirect HTTP 302 / Flash Message)
       └──► Przeładowanie film.php z komunikatem sukcesu i zaktualizowaną średnią ocen!
```

---

## 🤖 Workflow Zespołu Wirtualnych Agentów

Projekt został zrealizowany w innowacyjnej metodyce orkiestracji wieloagentowej nadzorowanej przez **Głównego Agenta** według ścisłych procedur opisanych w [`Docs/workflow.md`](Docs/workflow.md). Każda rola odpowiadała za precyzyjnie wyznaczony obszar inżynierii i raportowała wyniki w dedykowanym pliku w [`Docs/Sessions/`](Docs/Sessions/):

```text
 ┌────────────────────────────────────────────────────────────────────────┐
 │                      GŁÓWNY AGENT (ORCHESTRATOR)                       │
 └───────────────────────────────────┬────────────────────────────────────┘
                                     │
       ┌─────────────────────────────┼─────────────────────────────┐
       ▼                             ▼                             ▼
 1. PLANNER                 2. BACKEND DEVELOPER          3. FRONTEND DEVELOPER
 (Wymagania, architektura,  (DDL, PDO, zapytania         (CSS Dark Mode, RWD,
  Specs.md & Mermaid,        bez iloczynu, film.php POST)  gwiazdki ocen, JS)
  plan projektu)                     │                             │
       │                             │                             │
       └─────────────────────────────┼─────────────────────────────┘
                                     │
       ┌─────────────────────────────┼─────────────────────────────┐
       ▼                             ▼                             ▼
 4. ADMINISTRATOR BAZY       5. CONTENT AGENT              6. QA ASYSTENT
    DANYCH (DBA)             (Zasilenie bazy danymi        (Audyt 21 testów,
 (Integracja bazy, sockety,   polskiego kina seed.sql)      optymalizacja SQL,
  DDL, klucze, kaskady)              │                      stabilność systemu)
       │                             │                             │
       └─────────────────────────────┴─────────────────────────────┘
                                     │
                                     ▼
                      7. AGENT DS. DOKUMENTACJI GITHUB
             (Dokumentacja README.md, README.txt, Final-Report.md)
```

### Role i Odpowiedzialności w Projekcie:
1. **Główny Agent (Orchestrator):** Koordynacja prac zespołu, pilnowanie wytycznych kwalifikacji INF.03, przełączanie ról i egzekwowanie zasady bezwzględnego raportowania.
2. **Planner:** Analiza wymagań, przygotowanie oficjalnej specyfikacji technicznej [`Docs/Specs.md`](Docs/Specs.md), diagramów architektury i ERD w notacji Mermaid oraz harmonogramu prac. **Szczegółowy plan projektu i założenia architektoniczne opisano w raporcie Plannera:** [`Docs/Sessions/Planning.md`](Docs/Sessions/Planning.md).
3. **Backend Developer & Data Analyst:** Zaprojektowanie struktury tabel InnoDB, bezpieczna konfiguracja PDO, zapytania z pre-agregacją eliminujące iloczyn kartezjański, obsługa formularza recenzji we wzorcu PRG. Raport: [`Docs/Sessions/Backend.md`](Docs/Sessions/Backend.md).
4. **Frontend Developer:** Stworzenie stylów CSS w motywie kinowym Dark Mode (`frontend/css/style.css`), semantyka HTML5, skrypt interakcji i walidacji `frontend/js/main.js`. Raport: [`Docs/Sessions/Frontend.md`](Docs/Sessions/Frontend.md).
5. **Administrator Bazy Danych (DBA):** Odpowiada za techniczną i logiczną integrację bazy danych z projektem: weryfikację połączenia (PDO, DSN, obsługa gniazda unix socket i fallbacku TCP, uprawnienia), poprawność i integralność struktury tabel (klucze główne, klucze obce, indeksy wydajnościowe, reguły ON DELETE CASCADE, ograniczenia CHECK) oraz spójność DDL.  
   > ⚠️ **Kluczowa zasada:** DBA *nie wchodzi w rolę Content Agenta* – nie weryfikuje ani nie ocenia pojedynczych rekordów danych, lecz wyłącznie techniczną i logiczną integrację bazy z kodem.  
   Raport: [`Docs/Sessions/Database-integrity.md`](Docs/Sessions/Database-integrity.md).
6. **Content Agent:** Zebranie i wprowadzenie autentycznych danych z historii polskiego kina: 8 filmów, 19 aktorów, 21 ról w obsadzie, 17 recenzji (`database/seed.sql`). Raport: [`Docs/Sessions/Content.md`](Docs/Sessions/Content.md).
7. **QA Asystent:** Rygorystyczny audyt całego kodu, wykrycie i zlecenie poprawek (podstrony aktorów, eliminacja ostrzeżeń CLI, dostępność formularzy), stworzenie i wdrożenie automatycznego pakietu 21 testów integracyjnych (`tests/qa_test.php`). Raport: [`Docs/Sessions/QA.md`](Docs/Sessions/QA.md).
8. **Agent ds. Dokumentacji GitHub:** Zebranie raportów cząstkowych, opracowanie dokumentu podsumowującego [`Docs/Final-Report.md`](Docs/Final-Report.md), stworzenie niniejszego przewodnika `README.md`, wygenerowanie oficjalnego pliku tekstowego [`README.txt`](README.txt) oraz sformatowanie merytorycznej komendy `git commit`. Raport: [`Docs/Sessions/Github-Doc.md`](Docs/Sessions/Github-Doc.md).

---

## 📂 Struktura Katalogów

```text
cinehub/
├── Docs/
│   ├── workflow.md              # Instrukcja orkiestracji zespołowej i 7 podagentów
│   ├── Specs.md                 # Specyfikacja techniczna i funkcjonalna z diagramami Mermaid
│   ├── Final-Report.md          # Pełny raport końcowy z realizacji projektu
│   └── Sessions/                # Dziennik sesji poszczególnych ról (ściśle przypisane pliki)
│       ├── Planning.md          # Raport Plannera (PLAN PROJEKTU)
│       ├── Backend.md           # Raport Backend Developera & Data Analysta
│       ├── Frontend.md          # Raport Frontend Developera
│       ├── Database-integrity.md# Raport Administratora Bazy Danych (DBA)
│       ├── Content.md           # Raport Content Agenta
│       ├── QA.md                # Raport QA Asystenta
│       ├── Github-Doc.md        # Raport Agenta ds. Dokumentacji GitHub
│       └── Verification-Audit.md# Raport całościowego audytu stanu projektu
├── config/
│   └── database.php             # Konfiguracja połączenia PDO (localhost / socket / TCP)
├── database/
│   ├── schema.sql               # Bezpieczna struktura tabel DDL (CREATE TABLE IF NOT EXISTS)
│   ├── seed.sql                 # Idempotentne zasilenie danymi polskiego kina (DML bez TRUNCATE)
│   └── reset.sql                # Opcjonalny, destrukcyjny skrypt resetu deweloperskiego (DROP TABLE)
├── frontend/
│   ├── css/
│   │   └── style.css            # Kinowy motyw Dark Mode (CSS Grid, Flexbox, RWD)
│   └── js/
│       └── main.js              # Interaktywny selektor ocen, licznik znaków, walidacja
├── includes/
│   ├── functions.php            # Funkcje pomocnicze: e() dla XSS, flash messages, formatowanie
│   ├── header.php               # Wspólny nagłówek HTML5 z nawigacją i alertami Flash
│   └── footer.php               # Wspólna stopka strony HTML5 ze skryptem JS
├── tests/
│   └── qa_test.php              # Zautomatyzowany pakiet 21 testów integracyjnych (DDL, DML, CHECK)
├── index.php                    # Strona główna: katalog filmów, filtry, wyszukiwarka
├── film.php                     # Szczegóły filmu, obsada, recenzje i formularz opinii
├── actors.php                   # Katalog aktorów z wyszukiwarką
├── actor.php                    # Profil aktora i jego filmografia (M:N)
├── add_movie.php                # Formularz dodawania nowego filmu
├── add_actor.php                # Formularz dodawania nowego aktora
├── assign_actor.php             # Formularz przypisania roli w obsadzie
├── README.md                    # Niniejsza oficjalna dokumentacja projektu w Markdown
└── README.txt                   # Oficjalna dokumentacja projektu w formacie tekstowym
```

---

## 🚀 Instrukcja Instalacji i Uruchomienia

### Wymagania Środowiskowe:
- PHP w wersji 8.0 lub nowszej (z rozszerzeniami `pdo` oraz `pdo_mysql` lub `pdo_sqlite`).
- Serwer bazodanowy: **MySQL 8.0.16+** lub **MariaDB 10.3+**.  
  > ⚠️ **Ważna uwaga o wersji MySQL:** Schemat bazy danych CineHub wymaga pełnego egzekwowania ograniczeń integralności `CHECK (ocena BETWEEN 1 AND 10)`. Wersje MySQL starsze niż 8.0.16 (w tym MySQL 5.7) ignorują ograniczenia `CHECK`, akceptując błędne dane na poziomie silnika SQL. Wersje MariaDB egzekwują `CHECK` począwszy od wydania 10.2.1+.
- Przeglądarka internetowa obsługująca nowoczesny standard CSS Grid i ES6.

### Krok 1: Klonowanie Repozytorium
```bash
git clone https://github.com/twoj-login/cinehub.git
cd cinehub
```

### Krok 2: Bezpieczne Przygotowanie Bazy Danych
Zaloguj się do serwera MySQL / MariaDB i zaimportuj strukturę oraz dane początkowe:
```bash
# Utworzenie bazy i tabel (skrypt bezpieczny – CREATE TABLE IF NOT EXISTS; nie usuwa istniejących tabel ani danych)
mysql -u root -p < database/schema.sql

# Zasilenie bazy polskimi filmami i obsadą (skrypt w pełni idempotentny – nie czyści tabel i nie tworzy duplikatów)
mysql -u root -p < database/seed.sql
```

> ⚠️ **Opcjonalny reset deweloperski (UWAGA - kasuje dane):**  
> Jeśli celowo chcesz wyczyścić bazę i usunąć wszystkie tabele w celach deweloperskich, skorzystaj z oddzielnego skryptu `database/reset.sql`:  
> `mysql -u root -p cinehub_db < database/reset.sql`  
> Skrypt ten zawiera instrukcje `DROP TABLE` i **nie może być stosowany** podczas normalnego przygotowania ani aktualizacji projektu.

### Krok 3: Konfiguracja Połączenia
Domyślne parametry w `config/database.php` są skonfigurowane pod standardowe środowisko szkolne:
- **Host:** `localhost` (w systemie Linux automatycznie wykorzystuje szybkie gniazdo uniksowe `/var/lib/mysql/mysql.sock`; w systemie Windows lub Dockerze kieruje na port 3306).
- **Użytkownik:** `root`
- **Hasło:** `root` (w środowiskach takich jak XAMPP domyślnie puste – w takim przypadku ustaw `export DB_PASS=""` lub zmień w pliku).
- **Baza danych:** `cinehub_db`

Możesz je dostosować przekazując zmienne środowiskowe:
```bash
export DB_HOST="localhost"
export DB_NAME="cinehub_db"
export DB_USER="root"
export DB_PASS="root"     # lub DB_PASS="" dla XAMPP
```
Dla połączeń wymuszających TCP (np. kontener Docker):
```bash
export DB_HOST="127.0.0.1"
export DB_PORT="3306"
```

### Krok 4: Uruchomienie Aplikacji
Uruchom wbudowany serwer deweloperski PHP w katalogu głównym projektu:
```bash
php -S localhost:8000
```
Otwórz przeglądarkę internetową i przejdź pod adres:  
👉 **http://localhost:8000**

---

## 🧪 Zautomatyzowane Testy Jakości (QA)

Projekt posiada wbudowany, niezależny pakiet testów integracyjnych [`tests/qa_test.php`](tests/qa_test.php).

### Zakres i Ograniczenia Testów:
- **Co jest testowane:**
  - Weryfikacja rzeczywistego pliku DDL [`database/schema.sql`](database/schema.sql) (klucze obce, kaskady `ON DELETE CASCADE`, ograniczenia `CHECK`, brak instrukcji destrukcyjnych).
  - Weryfikacja idempotencji DDL (ponowne nałożenie schematu nie kasuje danych użytkownika).
  - Weryfikacja rzeczywistego pliku DML [`database/seed.sql`](database/seed.sql) (brak `TRUNCATE`, brak duplikacji rekordów przy powtórnym uruchomieniu).
  - Dynamiczne wiązanie relacji M:N (obsada) i 1:N (recenzje) bez polegania na stałych identyfikatorach.
  - Egzekwowanie ograniczeń `CHECK` w silniku SQLite oraz na izolowanej, tymczasowej bazie MariaDB (o ile lokalny serwer jest dostępny).
  - Ochrona przed atakami XSS (funkcja pomocnicza `e()`).
- **Czego pakiet NIE testuje automatycznie (wymagana weryfikacja ręczna):**
  - Pakiet testowy **nie wykonuje żądań HTTP** ani nie symuluje pełnego stosu przeglądarkowego.
  - Przepływy interfejsu (obsługa formularzy POST w przeglądarce, wzorzec PRG, komunikaty sesyjne Flash, estetyka Dark Mode i responsywność RWD) należy zweryfikować ręcznie w przeglądarce pod adresem `http://localhost:8000`.

### Uruchomienie Testów:
```bash
php tests/qa_test.php
```

Pakiet zwraca kod zakończenia `0` w przypadku zaliczenia wszystkich asercji lub kod `1` w razie wykrycia jakichkolwiek błędów. Testy tworzą wyłącznie tymczasowe zasoby (plik `test_cinehub_tmp.sqlite` oraz ewentualną tymczasową bazę MariaDB), które są natychmiast usuwane po zakończeniu.

Oczekiwany rezultat wykonania:
```text
=== ROZPOCZĘCIE TESTÓW AUDYTU QA & CONTENT DLA CINEHUB ===
--- TEST 1: Weryfikacja Schematu i Połączenia PDO --- [PASS]
--- TEST 2: Zasilenie Danymi z pliku database/seed.sql --- [PASS]
--- TEST 3: Weryfikacja Agregacji i Pobierania Filmów --- [PASS]
--- TEST 4: Weryfikacja Szczegółów Filmu i Obsady --- [PASS]
--- TEST 5: Weryfikacja Walidacji i Dodawania Nowej Recenzji --- [PASS]
--- TEST 6: Weryfikacja Wielu Filmów dla Aktora (Relacja M:N) --- [PASS]
--- TEST 7: Weryfikacja Ochrony Przed XSS --- [PASS]
--- TEST 8: Weryfikacja Formularzy add_movie i add_actor --- [PASS]
--- TEST 9: Weryfikacja Przypisywania Obsady i Braku Duplikatów --- [PASS]
=== PODSUMOWANIE TESTÓW QA & CONTENT ===
Zaliczono: 21 / 21
Błędy: 0
```

---

## 🛡️ Bezpieczeństwo i Dobre Praktyki

1. **Prepared Statements (Ochrona przed SQL Injection):**
   Każde zapytanie z parametrami wejściowymi pochodzącymi od użytkownika korzysta z parametryzacji PDO:
   ```php
   $stmt = $pdo->prepare("SELECT * FROM filmy WHERE id = :id");
   $stmt->execute(['id' => $filmId]);
   ```
2. **Escapowanie danych wyjściowych (Ochrona przed XSS):**
   Każdy ciąg tekstowy renderowany w widokach przechodzi przez funkcję pomocniczą `e()`:
   ```php
   function e(?string $value): string {
       return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
   }
   ```
3. **Wzorzec Post/Redirect/Get (PRG):**
   Formularze po pomyślnym zapisie wykonują przekierowanie nagłówkiem HTTP `Location: ...` oraz ustawiają komunikat w sesji (`setFlash()`), co uniemożliwia ponowne wysłanie danych przy odświeżeniu okna przeglądarki.
4. **Precyzja matematyczna i wydajność SQL:**
   Zastosowano podzapytania agregujące (`derived tables`) eliminujące powstawanie iloczynów kartezjańskich przy jednoczesnym pobieraniu aktorów i recenzji.

