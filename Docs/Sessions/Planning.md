# RAPORT Z SESJI: PLANOWANIE I SPECYFIKACJA ARCHITEKTURY (PLANNER)
**Data sesji:** 2026-10-29  
**Rola wykonująca:** Planner (nadzór: Główny Agent / Orchestrator)  
**Status zadania:** Zakończone sukcesem  

---

## 1. Zakres Wykonanych Prac
- Dokonano analizy wytycznych projektowych dla aplikacji bazodanowej **CineHub** (PHP / MySQL) pod kątem wymogów kwalifikacji zawodowej **INF.03**.
- Zdefiniowano wymagania funkcjonalne dla czterech kluczowych modułów:
  1. **Moduł filmów** (katalog, wyszukiwarka, filtrowanie, widok pojedynczego filmu, formularz dodawania filmu).
  2. **Moduł aktorów** (baza aktorów, profil aktora z filmografią, formularz dodawania nowego aktora).
  3. **Moduł powiązań M:N** (tabela łącząca `movie_actors` z rolą / postacią odgrywaną przez aktora w filmie).
  4. **Moduł recenzji i ocen** (system recenzji, skala 1-10, agregacja i wyliczanie średniej arytmetycznej ocen).
- Zdefiniowano architekturę modularną aplikacji z podziałem na warstwę klienta, kontrolerów/widoków, logiki pomocniczej oraz warstwę danych (PDO + MySQL).
- Opracowano model relacyjny bazy danych wraz z typami danych, kluczami obcymi i regułą kaskadowego usuwania powiązań (`ON DELETE CASCADE`).
- Utworzono dwa diagramy Mermaid:
  - Diagram architektury przepływu danych i komponentów (`flowchart TD`).
  - Diagram relacji encji ERD (`erDiagram`).
- Przygotowano pełny dokument specyfikacji w pliku `Docs/Specs.md`.

---

## 2. Zmodyfikowane i Utworzone Pliki
- `Docs/Specs.md` – Kompletna specyfikacja funkcjonalna i techniczna, standardy kodowania, model ERD i architektura systemu.
- `Docs/Sessions/Planning.md` – Niniejszy raport z sesji planowania.

---

## 3. Zidentyfikowane Wyzwania i Rozwiązania
- **Relacja wiele-do-wielu (M:N):**  
  *Wyzwanie:* Jeden aktor może grać w wielu filmach, a film może mieć wielu aktorów, z dodatkową informacją o nazwie postaci (`role_name`).  
  *Rozwiązanie:* Zastosowanie tabeli asocjacyjnej `movie_actors` z kluczami obcymi do `movies(id)` i `actors(id)` oraz indeksem unikalnym na trójce pól `(movie_id, actor_id, role_name)`.
- **Bezpieczeństwo aplikacji:**  
  *Wyzwanie:* Wymóg ochrony przed podatnościami wskazanymi w standardzie INF.03 (SQL Injection, XSS).  
  *Rozwiązanie:* Zdefiniowanie obowiązku stosowania parametryzowanych zapytań PDO Prepared Statements oraz uniwersalnej funkcji pomocniczej `e()` wykorzystującej `htmlspecialchars` dla każdego renderowanego tekstu.
- **Obliczanie średniej ocen:**  
  *Wyzwanie:* Uniknięcie redundancji danych w tabeli `movies` (np. przechowywanie predefiniowanej kolumny średniej).  
  *Rozwiązanie:* Dynamiczne wyliczanie średniej z wykorzystaniem funkcji agregującej `AVG()` i `LEFT JOIN` na tabeli `reviews`.

---

## 4. Aktualizacja Sesji Planowania: Nowa Rola DBA i Integracja Architektury (2026-11-12)
- **Rozszerzenie zespołu:** Formalne zdefiniowanie roli **Administratora Bazy Danych (DBA)** w `workflow.md` i `Specs.md`. Rola ta odpowiada za techniczną i logiczną integrację bazy danych z projektem (połączenie PDO, socket vs TCP, indeksy, DDL, integralność kaskad), przy zachowaniu wyraźnego rozgraniczenia od Content Agenta.
- **Aktualizacja specyfikacji (`Docs/Specs.md`):** Zsynchronizowano specyfikację techniczną z obecną architekturą, dodając specyfikację łączności bazy przez gniazdo uniksowe oraz opis obowiązków DBA.
- **Korekta diagramów i struktury katalogów w `Docs/Specs.md`:** Zastąpiono przestarzałą strukturę katalogów (`assets/`) rzeczywistą strukturą (`frontend/css/`, `frontend/js/`, `tests/`, kompletną listą sesji w `Docs/Sessions/`), dodano dedykowany diagram Mermaid struktury katalogów i komponentów (`graph TD`), zaktualizowano diagram przepływu architektury systemu (`flowchart TD`) oraz dodano diagram orkiestracji 7 agentów.
- **Polityka raportowania sesji:** Wprowadzono jednoznaczną regułę aktualizacji istniejących plików sesji (`Planning.md`, `Backend.md`, `Frontend.md`, `Content.md`, `QA.md`, `Database-integrity.md`, `Github-Doc.md`).
- **Status planu:** Plan został w pełni zrealizowany i zaktualizowany; projekt jest gotowy do kolejnych etapów rozwojowych.

---

## 5. Aktualizacja Sesji: Bezpieczeństwo Danych, Wersje DBMS i Naprawa Integralności (2026-11-12)
- **Wykonane działania:**
  1. Zidentyfikowano krytyczne ryzyko utraty danych przy ponownym uruchamianiu `schema.sql` i `seed.sql`.
  2. Zaktualizowano `Docs/Specs.md` w zakresie:
     - Wymuszenia bezpieczeństwa DDL: skrypt `schema.sql` opiera się wyłącznie na `CREATE TABLE IF NOT EXISTS` i nie kasuje tabel ani rekordów (brak `DROP TABLE` w standardowej instalacji).
     - Formalnego wydzielenia destrukcyjnego resetu do pliku `database/reset.sql` z wyraźnym ostrzeżeniem deweloperskim.
     - Wprowadzenia wymogu pełnej idempotencji dla `database/seed.sql` bez `TRUNCATE` i bez polegania na stałych identyfikatorach (dynamiczne wiązania kluczy).
     - Skorygowania minimalnej wspieranej wersji MySQL do **8.0.16+** oraz MariaDB do **10.3+** z uwagi na brak egzekwowania ograniczeń `CHECK` w MySQL 5.7.
- **Zmienione pliki:**
  - `Docs/Specs.md`
  - `Docs/Sessions/Planning.md`
- **Wyniki weryfikacji:**
  - Specyfikacja w pełni odzwierciedla bezpieczny, bezdestrukcyjny przepływ instalacji i uruchamiania.
- **Pozostałe kwestie do monitorowania:**
  - Egzekwowanie zasady, aby w środowiskach szkolnych nie uruchamiać skryptów resetu na bazach z danymi egzaminacyjnymi.

---

## 6. Kolejne Kroki
1. **Backend Developer & DBA:** Nadzór nad prawidłowym działaniem połączenia PDO i dynamicznym generowaniem kluczy.
2. **QA Asystent:** Regularne uruchamianie zaktualizowanego pakietu `tests/qa_test.php`.
3. **Agent ds. Dokumentacji GitHub:** Utrzymanie spójności opisów w `README.md`, `README.txt` i `Docs/Final-Report.md`.

