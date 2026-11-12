================================================================================
                    CINEHUB - INTERNETOWA BAZA FILMOWA
================================================================================
Standard Kwalifikacji Zawodowej INF.03:
Tworzenie i administrowanie stronami i bazami danych oraz aplikacjami internetowymi
Data wydania / audytu: 12 listopada 2026 r.
Autorzy: Zespół Wirtualnych Agentów pod nadzorem Głównego Agenta (Orchestratora)
================================================================================

1. WSTĘP I PRZEZNACZENIE APLIKACJI
--------------------------------------------------------------------------------
CineHub to nowoczesna, lekka i bezpieczna aplikacja internetowa zintegrowana z
relacyjną bazą danych MySQL / MariaDB, zrealizowana w czystym PHP 8 (PDO) bez
użycia zewnętrznych bibliotek i frameworków.

Głównym celem systemu jest kompleksowe zarządzanie katalogiem dzieł filmowych,
twórców kina (aktorów), relacjami obsadowymi (relacja M:N) oraz recenzjami i
ocenami społeczności. Baza danych została fabrycznie zasilona autentycznymi
dziełami polskiej kinematografii (m.in. "Dzień świra", "Chłopaki nie płaczą",
"Kiler", "Boże Ciało", "Zimna wojna", "Psy", "Znachor", "Sami swoi").

Szczegółowe założenia funkcjonalne, kryteria ukończenia oraz harmonogram
zostały zdefiniowane przez Plannera w dokumentacji specyfikacji:
--> Zobacz pełną specyfikację: Docs/Specs.md
--> Zobacz plan projektu i harmonogram Plannera: Docs/Sessions/Planning.md


2. ARCHITEKTURA APLIKACJI I SYSTEMU
--------------------------------------------------------------------------------
Aplikacja wykorzystuje modularną architekturę warstwową (Model-View-Controller
w podejściu Page Controller / Modular Monolith):

+------------------------------------------------------------------------------+
|                         WARSTWA PREZENTACJI (UI)                             |
|  - HTML5 Semantics, Responsive Web Design (CSS Grid, Flexbox)                |
|  - Autorski kinowy motyw Dark Mode (frontend/css/style.css)                  |
|  - Interaktywny JavaScript bez bibliotek (frontend/js/main.js):             |
|    dynamiczny selektor 10 gwiazdek, kalkulator znaków, walidacja live       |
+------------------------------------------------------------------------------+
                                       |
                                       v  (Żądania HTTP GET / POST)
+------------------------------------------------------------------------------+
|                      WARSTWA APLIKACJI I LOGIKI (PHP 8)                      |
|  - Kontrolery podstron: index.php, film.php, actors.php, actor.php           |
|  - Moduły administracyjne: add_movie.php, add_actor.php, assign_actor.php    |
|  - Współdzielone moduły i szablony: includes/header.php, includes/footer.php  |
|  - Biblioteka funkcji pomocniczych: includes/functions.php                  |
|    * Funkcja e() zapobiegająca atakom Cross-Site Scripting (XSS)             |
|    * Mechanizm komunikatów sesyjnych Flash (setFlash, getFlash)              |
|  - Wzorzec Post/Redirect/Get (PRG) przy dodawaniu recenzji i rekordów        |
+------------------------------------------------------------------------------+
                                       |
                                       v  (PDO Prepared Statements)
+------------------------------------------------------------------------------+
|                   WARSTWA DOSTĘPU DO DANYCH (PDO / SQL)                      |
|  - Konfiguracja i fabryka połączenia: config/database.php                    |
|  - Obsługa połączeń przez gniazdo Unix (socket) oraz TCP z fallbackiem       |
|  - Zapytania z pre-agregacją w podzapytaniach (derived tables)               |
|    eliminujące powstawanie iloczynu kartezjańskiego (Cartesian product)      |
|  - Ochrona przed SQL Injection dzięki pełnej parametryzacji zapytań          |
+------------------------------------------------------------------------------+
                                       |
                                       v  (RDBMS Engine)
+------------------------------------------------------------------------------+
|              WARSTWA BAZY DANYCH (MySQL 8.0+ / MariaDB 10.3+)                |
|  - Silnik tabel: InnoDB (pełna transakcyjność i integralność referencyjna)    |
|  - Kodowanie znaków: utf8mb4 / collate utf8mb4_unicode_ci                    |
|  - Relacje kluczy obcych z kaskadowością: ON DELETE CASCADE ON UPDATE CASCADE|
|  - 4 znormalizowane tabele: filmy, aktorzy, obsada, recenzje                 |
+------------------------------------------------------------------------------+


3. MODEL RELACYJNY BAZY DANYCH (STRUKTURA I RELACJE)
--------------------------------------------------------------------------------
Baza danych składa się z 4 ściśle powiązanych tabel w 3. postaci normalnej (3NF):

  [ FILMY ] 1 <---------- N [ RECENZJE ]
     | 1
     |
     | N
  [ OBSADA ] (tabela asocjacyjna relacji M:N z atrybutem rola)
     | N
     |
     | 1
  [ AKTORZY ]

Szczegółowy opis tabel:
1. `filmy`:
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `tytul` VARCHAR(255) NOT NULL (indeksowany)
   - `rezyser` VARCHAR(255) NOT NULL
   - `gatunek` VARCHAR(100) NOT NULL (indeksowany)
   - `rok_premiery` INT NOT NULL (indeksowany)
   - `czas_trwania` INT NOT NULL
   - `opis` TEXT NULL
   - `plakat` VARCHAR(500) NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

2. `aktorzy`:
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `imie` VARCHAR(100) NOT NULL
   - `nazwisko` VARCHAR(100) NOT NULL (indeksowany)
   - `data_urodzenia` DATE NULL
   - `zdjecie` VARCHAR(500) NULL
   - `biografia` TEXT NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

3. `obsada` (Relacja wiele-do-wielu M:N między Filmami a Aktorami):
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `film_id` INT NOT NULL, FOREIGN KEY -> filmy(id) ON DELETE CASCADE
   - `aktor_id` INT NOT NULL, FOREIGN KEY -> aktorzy(id) ON DELETE CASCADE
   - `rola` VARCHAR(255) NOT NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   - Indeks UNIKALNY: UNIQUE KEY uq_film_aktor_rola (film_id, aktor_id, rola)

4. `recenzje` (Relacja jeden-do-wielu 1:N z Filmami):
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `film_id` INT NOT NULL, FOREIGN KEY -> filmy(id) ON DELETE CASCADE
   - `autor` VARCHAR(100) NOT NULL
   - `ocena` TINYINT NOT NULL CHECK (ocena BETWEEN 1 AND 10)
   - `tresc` TEXT NOT NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   - Indeks: idx_recenzje_film (film_id)


4. CYKL DZIAŁANIA APLIKACJI (USER JOURNEY)
--------------------------------------------------------------------------------
1. Wejście na stronę główną (index.php):
   - Użytkownik widzi siatkę filmów pobranych zoptymalizowanym zapytaniem SQL.
   - Może skorzystać z wyszukiwarki tekstowej, filtrowania po gatunku oraz
     sortowania (rok, ocena, tytuł).
2. Wybór filmu i przejście do szczegółów (film.php?id=X):
   - Prezentacja opisu filmu, reżysera, roku, czasu trwania oraz plakatu.
   - Wyświetlenie obsady aktorskiej z podziałem na imię, nazwisko i rolę.
   - Wyświetlenie listy recenzji oraz wyliczonej średniej oceny społeczności.
3. Przeglądanie profilu aktora (actor.php?id=Y):
   - Kliknięcie w nazwisko aktora w karcie filmu przenosi do jego profilu.
   - Widoczna jest biografia oraz pełna filmografia z odnośnikami do filmów.
4. Dodanie recenzji (formularz POST na film.php):
   - Użytkownik podaje podpis, wybiera ocenę (1-10 gwiazdek) i wpisuje treść.
   - Po wysłaniu następuje walidacja po stronie PHP.
   - Po pomyślnym zapisie w bazie następuje przekierowanie HTTP 302 (PRG)
     oraz wyświetlenie powiadomienia Flash o pomyślnym dodaniu opinii.
5. Zarządzanie bazą (panele dodawania):
   - add_movie.php: Dodawanie nowych dzieł kinematografii do bazy.
   - add_actor.php: Rejestracja nowych artystów w bazie.
   - assign_actor.php: Powiązywanie aktora z filmem i nadanie roli.


5. WORKFLOW ZESPOŁU WIRTUALNYCH AGENTÓW
--------------------------------------------------------------------------------
Projekt zrealizowano zgodnie z rygorystycznym workflow opisanym w Docs/workflow.md,
nadzorowanym przez Głównego Agenta (Orchestratora). Każdy podagent realizował
ściśle określone zadania i dokumentował je w swoim dedykowanym pliku sesji:

1. PLANNER:
   - Zakres: Analiza wymagań, stworzenie specyfikacji technicznej i diagramów Mermaid.
   - Plik sesji: Docs/Sessions/Planning.md
   - Odniesienie do planu: Zobacz szczegółowy harmonogram, architekturę i założenia
     w pliku Docs/Sessions/Planning.md oraz Docs/Specs.md.

2. BACKEND DEVELOPER & DATA ANALYST:
   - Zakres: Logika PHP, zapytania PDO, obsługa formularzy, filtracja i ochrona danych,
     zapytania SQL z pre-agregacją zapobiegające powstawaniu iloczynu kartezjańskiego.
   - Plik sesji: Docs/Sessions/Backend.md

3. FRONTEND DEVELOPER:
   - Zakres: Kinowy interfejs Dark Mode, semantyka HTML5, responsywność CSS Grid/Flexbox,
     skrypty Vanilla JS do obsługi gwiazdek ocen i kalkulatora znaków.
   - Plik sesji: Docs/Sessions/Frontend.md

4. ADMINISTRATOR BAZY DANYCH (DBA):
   - Zakres: Techniczna i logiczna integracja bazy danych z projektem. Weryfikacja
     połączenia PDO (obsługa socket unix oraz TCP localhost), integralność referencyjna,
     indeksy wydajnościowe, reguły ON DELETE CASCADE, ograniczenia CHECK.
   - KLUCZOWE OGRANICZENIE: DBA nie wchodzi w rolę Content Agenta – nie weryfikuje
     ani nie modyfikuje pojedynczych wartości rekordów, skupiając się w 100% na
     architekturze, spójności i logice bazy danych.
   - Plik sesji: Docs/Sessions/Database-integrity.md

5. CONTENT AGENT:
   - Zakres: Zasilenie bazy realistycznymi danymi polskiego kina (8 filmów, 19 aktorów,
     21 ról w obsadzie, 17 recenzji). Przygotowanie pliku database/seed.sql.
   - Plik sesji: Docs/Sessions/Content.md

6. QA ASYSTENT:
   - Zakres: Testy kodu, audyt bezpieczeństwa (SQLi, XSS, CSRF/PRG), weryfikacja
     kryteriów Specs.md, stworzenie i uruchomienie 21 zautomatyzowanych testów
     integracyjnych (tests/qa_test.php).
   - Plik sesji: Docs/Sessions/QA.md

7. AGENT DS. DOKUMENTACJI GITHUB:
   - Zakres: Przygotowanie raportu końcowego Docs/Final-Report.md, niniejszego pliku
     README.txt, aktualizacja README.md oraz sformatowanie merytorycznej komendy git commit.
   - Plik sesji: Docs/Sessions/Github-Doc.md


6. STRUKTURA PLIKÓW PROJEKTU
--------------------------------------------------------------------------------
cinehub/
├── Docs/
│   ├── workflow.md                 - Instrukcja orkiestracji zespołowej i podagentów
│   ├── Specs.md                    - Oficjalna specyfikacja techniczna z diagramami
│   ├── Final-Report.md             - Raport końcowy z realizacji całego projektu
│   └── Sessions/                   - Dedykowane pliki sesyjne dla każdego agenta
│       ├── Planning.md             - Raport Plannera (PLAN PROJEKTU)
│       ├── Backend.md              - Raport Backend Developera
│       ├── Frontend.md             - Raport Frontend Developera
│       ├── Database-integrity.md   - Raport Administratora Bazy Danych (DBA)
│       ├── Content.md              - Raport Content Agenta
│       ├── QA.md                   - Raport QA Asystenta
│       ├── Github-Doc.md           - Raport Agenta ds. Dokumentacji GitHub
│       └── Verification-Audit.md   - Raport całościowego audytu stanu projektu
├── config/
│   └── database.php                - Połączenie PDO z bazą danych (localhost / socket)
├── database/
│   ├── schema.sql                  - Struktura DDL (filmy, aktorzy, obsada, recenzje)
│   └── seed.sql                    - Zasilenie danymi polskiego kina (DML)
├── frontend/
│   ├── css/
│   │   └── style.css               - Kinowy motyw Dark Mode (CSS Grid, RWD)
│   └── js/
│       └── main.js                 - Obsługa gwiazdek ocen, walidacja live
├── includes/
│   ├── functions.php               - Funkcje pomocnicze (e(), flash messages)
│   ├── header.php                  - Wspólny nagłówek HTML5
│   └── footer.php                  - Wspólna stopka HTML5
├── tests/
│   └── qa_test.php                 - Zestaw 21 zautomatyzowanych testów integracyjnych
├── index.php                       - Katalog filmów z filtrami i wyszukiwarką
├── film.php                        - Karta filmu, obsada, opinie i formularz recenzji
├── actors.php                      - Katalog aktorów
├── actor.php                       - Profil aktora i filmografia (relacja M:N)
├── add_movie.php                   - Formularz rejestracji nowego filmu
├── add_actor.php                   - Formularz rejestracji nowego aktora
├── assign_actor.php                - Formularz przypisania roli w obsadzie
├── README.md                       - Dokumentacja projektu w formacie Markdown
└── README.txt                      - Niniejsza oficjalna dokumentacja tekstowa


7. INSTRUKCJA INSTALACJI I URUCHOMIENIA
--------------------------------------------------------------------------------
Wymagania: PHP 8.0+, serwer MySQL 5.7+ lub MariaDB 10.3+.

Krok 1: Wdrożenie bazy danych:
  mariadb -u root -p cinehub_db < database/schema.sql
  mariadb -u root -p cinehub_db < database/seed.sql

Krok 2: Konfiguracja połączenia:
  W pliku config/database.php upewnij się, że ustawione są właściwe dane dostępowe
  (domyślnie: host 'localhost', baza 'cinehub_db', użytkownik 'root', hasło 'root' lub '').

Krok 3: Uruchomienie testów integracyjnych:
  php tests/qa_test.php

Krok 4: Uruchomienie serwera WWW:
  php -S localhost:8000
  Otwórz w przeglądarce: http://localhost:8000


8. ODNIESIENIA I DOKUMENTACJA PLANOWANIA
--------------------------------------------------------------------------------
Wszystkie założenia architektoniczne, etapy realizacji oraz szczegółowy plan
opracowany przez Plannera znajdują się w pliku:
--> Docs/Sessions/Planning.md

Zsynchronizowany audyt techniczny bazy danych sporządzony przez DBA:
--> Docs/Sessions/Database-integrity.md

Pełne podsumowanie prac zespołu:
--> Docs/Final-Report.md
================================================================================
Koniec pliku README.txt
================================================================================
