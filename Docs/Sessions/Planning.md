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

## 4. Kolejne Kroki
1. **Backend Developer:**
   - Utworzenie skryptów DDL i DML: `database/schema.sql` oraz `database/seed.sql`.
   - Zaimplementowanie klasy/pliku połączenia PDO (`config/database.php`).
   - Implementacja logiki pobierania i zapisywania filmów, aktorów, obsady i recenzji.
2. **Frontend Developer:**
   - Stworzenie struktury widoków i komponentów (`header.php`, `footer.php`, formularze, karty filmowe).
   - Opracowanie stylizacji CSS (nowoczesny motyw kinowy Dark Mode).
   - Opracowanie skryptów walidacji i dynamicznego systemu oceniania w JavaScript.
3. **QA Asystent:**
   - Weryfikacja zgodności implementacji z `Docs/Specs.md`.

