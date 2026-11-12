-- =======================================================
-- Baza Danych: cinehub_db (Kwalifikacja INF.03)
-- Zestaw Danych Początkowych (Seed Data) - Polskie Kino
-- Rola: Content Agent
--
-- BEZPIECZEŃSTWO DANYCH I IDEMPOTENTNOŚĆ:
--   Skrypt NIE usuwa ani nie czyści tabel (brak TRUNCATE / DROP).
--   Nie nadpisuje ani nie duplikuje istniejących rekordów.
--   Klucze powiązań (obsada, recenzje) są wyznaczane DYNAMICZNIE
--   na podstawie tytułu filmu i nazwiska aktora (brak stałych ID),
--   dzięki czemu obsada zawsze łączy właściwe rekordy, nawet gdy
--   w bazie istnieją już inne filmy lub inne numery AUTO_INCREMENT.
-- =======================================================

USE `cinehub_db`;

-- -------------------------------------------------------
-- 1. Tabela: filmy (8 kultowych polskich dzieł kinematografii)
-- -------------------------------------------------------
INSERT INTO `filmy` (`tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`)
SELECT 'Dzień świra', 'Marek Koterski', 'Komediodramat', 2002, 93, 
'Zgorzkniały, sfrustrowany polonista i rozwodnik Adaś Miauczyński zmaga się z natręctwami codziennego życia, samotnością oraz absurdem otaczającej go polskiej rzeczywistości. Każda z pozoru błaha czynność urasta do rangi życiowego dramatu.',
'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?auto=format&fit=crop&w=800&q=80'
WHERE NOT EXISTS (SELECT 1 FROM `filmy` WHERE `tytul` = 'Dzień świra' AND `rezyser` = 'Marek Koterski');

INSERT INTO `filmy` (`tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`)
SELECT 'Chłopaki nie płaczą', 'Olaf Lubaszenko', 'Komedia kryminalna', 2000, 96, 
'Młody, wrażliwy skrzypek Kuba Brenner oraz jego nieśmiały kolega przypadkowo wplątują się w mafijne porachunki stołecznych gangsterów Bolca, Freda i Gruchy po nieudanej transakcji w hotelowym pokoju.',
'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=800&q=80'
WHERE NOT EXISTS (SELECT 1 FROM `filmy` WHERE `tytul` = 'Chłopaki nie płaczą' AND `rezyser` = 'Olaf Lubaszenko');

INSERT INTO `filmy` (`tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`)
SELECT 'Kiler', 'Juliusz Machulski', 'Komedia sensacyjna', 1997, 104, 
'Poczciwy warszawski taksówkarz Jurek Kiler zostaje omyłkowo wzięty przez policję za bezwzględnego płatnego zabójcę o tym samym pseudonimie. Gdy trafia do więzienia, mafijny boss Siara organizuje brawurową ucieczkę.',
'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=800&q=80'
WHERE NOT EXISTS (SELECT 1 FROM `filmy` WHERE `tytul` = 'Kiler' AND `rezyser` = 'Juliusz Machulski');

INSERT INTO `filmy` (`tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`)
SELECT 'Boże Ciało', 'Jan Komasa', 'Dramat', 2019, 115, 
'Dwudziestoletni Daniel po wyjściu z zakładu poprawczego marzy o wstąpieniu do seminarium. Pod wpływem zbiegu okoliczności zostaje wzięty za młodego księdza w małym podkarpackim miasteczku, wnosząc świeżą energię do straumatyzowanej społeczności.',
'https://images.unsplash.com/photo-1543857778-c4a1a3e0b2eb?auto=format&fit=crop&w=800&q=80'
WHERE NOT EXISTS (SELECT 1 FROM `filmy` WHERE `tytul` = 'Boże Ciało' AND `rezyser` = 'Jan Komasa');

INSERT INTO `filmy` (`tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`)
SELECT 'Zimna wojna', 'Paweł Pawlikowski', 'Melodramat', 2018, 88, 
'Zrealizowana w czerni i bieli epicka opowieść o trudnej i niemożliwej miłości tancerki Zuli i kompozytora Wiktora na tle zimnowojennej Europy lat 50. i 60. – od powojennej Polski, przez Berlin i Jugosławię, po paryskie kluby jazzowe.',
'https://images.unsplash.com/photo-1518676590629-3dcbd9c5a5c9?auto=format&fit=crop&w=800&q=80'
WHERE NOT EXISTS (SELECT 1 FROM `filmy` WHERE `tytul` = 'Zimna wojna' AND `rezyser` = 'Paweł Pawlikowski');

INSERT INTO `filmy` (`tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`)
SELECT 'Psy', 'Władysław Pasikowski', 'Film sensacyjny', 1992, 104, 
'Początek lat 90. Byli funkcjonariusze aparatu bezpieczeństwa po weryfikacji trafiają do nowo formowanej policji. Podkomisarz Franciszek Maurer staje do bezwzględnej walki z brutalnym gangiem przemytników dowodzonym przez dawnego kolegę.',
'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=800&q=80'
WHERE NOT EXISTS (SELECT 1 FROM `filmy` WHERE `tytul` = 'Psy' AND `rezyser` = 'Władysław Pasikowski');

INSERT INTO `filmy` (`tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`)
SELECT 'Znachor', 'Jerzy Hoffman', 'Melodramat', 1982, 128, 
'Wybitny chirurg profesor Rafał Wilczur traci rodzinę i w wyniku brutalnego pobicia doznaje całkowitej amnezji. Przez lata tuła się po wsiach jako Antoni Kosiba, dokonując cudownych uzdrowień, nie pamiętając kim naprawdę jest.',
'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=800&q=80'
WHERE NOT EXISTS (SELECT 1 FROM `filmy` WHERE `tytul` = 'Znachor' AND `rezyser` = 'Jerzy Hoffman');

INSERT INTO `filmy` (`tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`)
SELECT 'Sami swoi', 'Sylwester Chęciński', 'Komedia', 1967, 78, 
'Zwaśnione od pokoleń kresowe rodziny Kazimierza Pawlaka i Władysława Kargula osiedlają się po II wojnie światowej na Ziemiach Odzyskanych w sąsiadujących gospodarstwach. Ich dawny spór o miedzę i kota odżywa ze zdwojoną siłą.',
'https://images.unsplash.com/photo-1533929736458-ca588d08c8be?auto=format&fit=crop&w=800&q=80'
WHERE NOT EXISTS (SELECT 1 FROM `filmy` WHERE `tytul` = 'Sami swoi' AND `rezyser` = 'Sylwester Chęciński');

-- -------------------------------------------------------
-- 2. Tabela: aktorzy (19 wielkich postaci polskiego kina i teatru)
-- -------------------------------------------------------
INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Marek', 'Kondrat', '1950-10-18', 
'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', 
'Wybitny polski aktor teatralny i filmowy. Zasłynął rolami w Zaklętych rewirach, Smudze cienia, Pułkowniku Kwiatkowskim oraz kreacją Adasia Miauczyńskiego w Dniu świra Marka Koterskiego.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Marek' AND `nazwisko` = 'Kondrat');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Janina', 'Traczykówna', '1930-07-25', 
'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80', 
'Ceniona polska aktorka teatralna i filmowa. Niezapomniana matka Adasia Miauczyńskiego w Dniu świra oraz odtwórczyni ról w licznych spektaklach Teatru Dramatycznego w Warszawie.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Janina' AND `nazwisko` = 'Traczykówna');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Andrzej', 'Grabowski', '1952-03-15', 
'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80', 
'Aktor teatralny, telewizyjny i filmowy. Laureat wielu nagród za role dramatyczne w teatrze oraz filmach takich jak Dzień świra, Pitbull czy Pod Mocnym Aniołem.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Andrzej' AND `nazwisko` = 'Grabowski');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Maciej', 'Stuhr', '1975-06-23', 
'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 
'Aktor filmowy i kabaretowy, felietonista i konferansjer. Grał główne role w Chłopakach nie płaczą, Poranku kojota, Pokłosiu Władysława Pasikowskiego oraz Belfrze.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Maciej' AND `nazwisko` = 'Stuhr');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Cezary', 'Pazura', '1962-06-13', 
'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80', 
'Jeden z najpopularniejszych polskich aktorów filmowych przełomu wieków. Znakomite role w Kilerze, Psach, Chłopakach nie płaczą, Nic śmiesznego oraz 13 posterunku.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Cezary' AND `nazwisko` = 'Pazura');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Michał', 'Milowicz', '1970-09-16', 
'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80', 
'Aktor, wokalista i reżyser. Ikoniczny wykonawca ról w komediach Olafa Lubaszenki: Bolec w Chłopaki nie płaczą oraz Brylant w Poranku kojota.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Michał' AND `nazwisko` = 'Milowicz');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Mirosław', 'Zbrojewicz', '1957-02-25', 
'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=400&q=80', 
'Aktor o charakterystycznym, głębokim głosie i charyzmie. Niezapomniany Grucha z Chłopaków nie płaczą, zagrał również w dziesiątkach produkcji teatralnych i filmowych.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Mirosław' AND `nazwisko` = 'Zbrojewicz');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Janusz', 'Rewiński', '1949-09-16', 
'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=400&q=80', 
'Satyryk, aktor i komik. Legendarny Stefan "Siara" Siarzewski w kultowej dylogii Kiler Juliusza Machulskiego oraz współtwórca Kabaretu Olgi Lipińskiej.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Janusz' AND `nazwisko` = 'Rewiński');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Jerzy', 'Stuhr', '1947-04-18', 
'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=400&q=80', 
'Monumentalna postać polskiej kultury, profesor sztuk teatralnych, reżyser i scenarzysta. Role w Wodzireju, Seksmisji, Kilerze, Dekalogu oraz własnych filmach jak Historie miłosne.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Jerzy' AND `nazwisko` = 'Stuhr');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Katarzyna', 'Figura', '1962-03-22', 
'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80', 
'Jedna z najbardziej wyrazistych polskich aktorek. Laureatka nagród na FPFF w Gdyni za role w Żurku oraz Ubu Królu, a także niezapomniana Ryszarda "Siara" w Kilerze.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Katarzyna' AND `nazwisko` = 'Figura');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Bartosz', 'Bielenia', '1992-05-15', 
'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=400&q=80', 
'Aktor młodego pokolenia o magnetycznym spojrzeniu i wielkim kunszcie. Za rolę Daniela w nominowanym do Oscara filmie Boże Ciało Jana Komasy zdobył międzynarodowe uznanie.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Bartosz' AND `nazwisko` = 'Bielenia');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Joanna', 'Kulig', '1982-06-24', 
'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80', 
'Aktorka filmowa i wokalistka, zdobywczyni Europejskiej Nagrody Filmowej za pierwszoplanową rolę Zuli w Zimnej wojnie Pawła Pawlikowskiego. Członkini Amerykańskiej Akademii Filmowej.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Joanna' AND `nazwisko` = 'Kulig');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Tomasz', 'Kot', '1977-04-21', 
'https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?auto=format&fit=crop&w=400&q=80', 
'Wszechstronny aktor dramatyczny. Laureat Orła za rolę profesora Religi w Bogach oraz gwiazda Zimnej wojny, Skazanego na bluesa i serialu Wielka woda.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Tomasz' AND `nazwisko` = 'Kot');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Bogusław', 'Linda', '1952-06-27', 
'https://images.unsplash.com/photo-1513956589380-bad6acb9b9d4?auto=format&fit=crop&w=400&q=80', 
'Niekwestionowana ikona polskiego kina akcji i kina moralnego niepokoju. Legendarny Franz Maurer w Psach, kreacje w Przypadku Kieślowskiego, Krollu, Sarze i Quo Vadis.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Bogusław' AND `nazwisko` = 'Linda');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Janusz', 'Gajos', '1939-09-23', 
'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80', 
'Jeden z najwybitniejszych polskich aktorów w dziejach. Tytan sceny Teatru Narodowego, genialne role w Przesłuchaniu, Żółtym szaliku, Ucieczce z kina Wolność i Psach.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Janusz' AND `nazwisko` = 'Gajos');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Jerzy', 'Bińczycki', '1937-09-06', 
'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80', 
'Wybitny krakowski aktor Starego Teatru. Ukochał go cały kraj za poruszającą, ciepłą rolę profesora Rafała Wilczura / Antoniego Kosiby w ekranizacji Znachora Jerzego Hoffmana.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Jerzy' AND `nazwisko` = 'Bińczycki');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Anna', 'Dymna', '1951-07-20', 
'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80', 
'Legenda polskiego kina, działaczka społeczna i założycielka fundacji Mimo Wszystko. Wspaniałe role w Znachorze (Marysia), Nie ma mocnych (Ania Pawlakówna) oraz Mistrzu i Małgorzacie.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Anna' AND `nazwisko` = 'Dymna');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Wacław', 'Kowalski', '1916-05-02', 
'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=400&q=80', 
'Mistrz komedii charakterystycznej. Stworzył niezapomnianą postać Kazimierza Pawlaka w trylogii Chęcińskiego (Sami swoi, Nie ma mocnych, Kochaj albo rzuć) oraz Popiołka w Domu.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Wacław' AND `nazwisko` = 'Kowalski');

INSERT INTO `aktorzy` (`imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`)
SELECT 'Władysław', 'Hańcza', '1905-05-18', 
'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 
'Mistrz słowa i gry aktorskiej. Władysław Kargul w Samych swoich, Maciej Boryna w Chłopach oraz Janusz Radziwiłł w Potopie Jerzego Hoffmana.'
WHERE NOT EXISTS (SELECT 1 FROM `aktorzy` WHERE `imie` = 'Władysław' AND `nazwisko` = 'Hańcza');

-- -------------------------------------------------------
-- 3. Tabela: obsada (Dynamiczne wiązanie relacji M:N na podstawie tytułu i nazwiska)
-- -------------------------------------------------------
-- Dzień świra
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Adaś Miauczyński'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Dzień świra' AND f.rezyser = 'Marek Koterski' AND a.imie = 'Marek' AND a.nazwisko = 'Kondrat'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Adaś Miauczyński');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Mama Adasia'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Dzień świra' AND f.rezyser = 'Marek Koterski' AND a.imie = 'Janina' AND a.nazwisko = 'Traczykówna'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Mama Adasia');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Sąsiad Rączka'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Dzień świra' AND f.rezyser = 'Marek Koterski' AND a.imie = 'Andrzej' AND a.nazwisko = 'Grabowski'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Sąsiad Rączka');

-- Chłopaki nie płaczą
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Kuba Brenner'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Chłopaki nie płaczą' AND f.rezyser = 'Olaf Lubaszenko' AND a.imie = 'Maciej' AND a.nazwisko = 'Stuhr'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Kuba Brenner');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Fred'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Chłopaki nie płaczą' AND f.rezyser = 'Olaf Lubaszenko' AND a.imie = 'Cezary' AND a.nazwisko = 'Pazura'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Fred');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Bolec'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Chłopaki nie płaczą' AND f.rezyser = 'Olaf Lubaszenko' AND a.imie = 'Michał' AND a.nazwisko = 'Milowicz'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Bolec');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Grucha'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Chłopaki nie płaczą' AND f.rezyser = 'Olaf Lubaszenko' AND a.imie = 'Mirosław' AND a.nazwisko = 'Zbrojewicz'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Grucha');

-- Kiler
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Jurek Kiler'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Kiler' AND f.rezyser = 'Juliusz Machulski' AND a.imie = 'Cezary' AND a.nazwisko = 'Pazura'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Jurek Kiler');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Stefan "Siara" Siarzewski'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Kiler' AND f.rezyser = 'Juliusz Machulski' AND a.imie = 'Janusz' AND a.nazwisko = 'Rewiński'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Stefan "Siara" Siarzewski');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Komisarz Ryba'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Kiler' AND f.rezyser = 'Juliusz Machulski' AND a.imie = 'Jerzy' AND a.nazwisko = 'Stuhr'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Komisarz Ryba');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Ryszarda "Siara" Siarzewska'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Kiler' AND f.rezyser = 'Juliusz Machulski' AND a.imie = 'Katarzyna' AND a.nazwisko = 'Figura'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Ryszarda "Siara" Siarzewska');

-- Boże Ciało
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Daniel / ksiądz Tomasz'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Boże Ciało' AND f.rezyser = 'Jan Komasa' AND a.imie = 'Bartosz' AND a.nazwisko = 'Bielenia'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Daniel / ksiądz Tomasz');

-- Zimna wojna
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Zula Lichoń'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Zimna wojna' AND f.rezyser = 'Paweł Pawlikowski' AND a.imie = 'Joanna' AND a.nazwisko = 'Kulig'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Zula Lichoń');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Wiktor Warski'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Zimna wojna' AND f.rezyser = 'Paweł Pawlikowski' AND a.imie = 'Tomasz' AND a.nazwisko = 'Kot'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Wiktor Warski');

-- Psy
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Franciszek "Franz" Maurer'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Psy' AND f.rezyser = 'Władysław Pasikowski' AND a.imie = 'Bogusław' AND a.nazwisko = 'Linda'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Franciszek "Franz" Maurer');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Waldemar Morawiec "Nowy"'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Psy' AND f.rezyser = 'Władysław Pasikowski' AND a.imie = 'Cezary' AND a.nazwisko = 'Pazura'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Waldemar Morawiec "Nowy"');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Gross "Siwy"'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Psy' AND f.rezyser = 'Władysław Pasikowski' AND a.imie = 'Janusz' AND a.nazwisko = 'Gajos'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Gross "Siwy"');

-- Znachor
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Prof. Rafał Wilczur / Antoni Kosiba'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Znachor' AND f.rezyser = 'Jerzy Hoffman' AND a.imie = 'Jerzy' AND a.nazwisko = 'Bińczycki'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Prof. Rafał Wilczur / Antoni Kosiba');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Maria Jolanta Wilczur'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Znachor' AND f.rezyser = 'Jerzy Hoffman' AND a.imie = 'Anna' AND a.nazwisko = 'Dymna'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Maria Jolanta Wilczur');

-- Sami swoi
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Kazimierz Pawlak'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Sami swoi' AND f.rezyser = 'Sylwester Chęciński' AND a.imie = 'Wacław' AND a.nazwisko = 'Kowalski'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Kazimierz Pawlak');

INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`)
SELECT f.id, a.id, 'Władysław Kargul'
FROM `filmy` f, `aktorzy` a
WHERE f.tytul = 'Sami swoi' AND f.rezyser = 'Sylwester Chęciński' AND a.imie = 'Władysław' AND a.nazwisko = 'Hańcza'
AND NOT EXISTS (SELECT 1 FROM `obsada` o WHERE o.film_id = f.id AND o.aktor_id = a.id AND o.rola = 'Władysław Kargul');

-- -------------------------------------------------------
-- 4. Tabela: recenzje (Dynamiczne wiązanie relacji 1:N na podstawie tytułu filmu)
-- -------------------------------------------------------
-- Dzień świra
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Filmoznawca_PL', 10, 'Arcydzieło polskiego kina początku XXI wieku. Koterski dokonał bezlitosnej, a zarazem czułej wiwisekcji polskich kompleksów. Kondrat w roli życia – tragizm i komizm w doskonałej symbiozie.'
FROM `filmy` f WHERE f.tytul = 'Dzień świra' AND f.rezyser = 'Marek Koterski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Filmoznawca_PL');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Marta_Kino', 9, 'Film, który z wiekiem ogląda się coraz bardziej jak dramat niż komedię. Każdy z nas ma w sobie odrobinę z Adasia Miauczyńskiego. Dialogi przeszły do języka potocznego.'
FROM `filmy` f WHERE f.tytul = 'Dzień świra' AND f.rezyser = 'Marek Koterski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Marta_Kino');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'PiotrK', 8, 'Bardzo gorzka i przenikliwa satyra. Miejscami bolesna w odbiorze, ale pod względem scenariusza i rytmu językowego to absolutny majstersztyk.'
FROM `filmy` f WHERE f.tytul = 'Dzień świra' AND f.rezyser = 'Marek Koterski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'PiotrK');

-- Chłopaki nie płaczą
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Kinoman90', 10, 'Kultowa komedia, którą potrafię zacytować niemal w całości od deski do deski. Pazura, Milowicz i Zbrojewicz stworzyli galerię postaci, które nigdy się nie zestarzeją.'
FROM `filmy` f WHERE f.tytul = 'Chłopaki nie płaczą' AND f.rezyser = 'Olaf Lubaszenko'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Kinoman90');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Tomasz_W', 9, 'Znakomity timing komediowy, lekkość reżyserii Olafa Lubaszenki i genialna ścieżka dźwiękowa. Polski odpowiednik kina Tarantino w swojskim, warszawskim wydaniu.'
FROM `filmy` f WHERE f.tytul = 'Chłopaki nie płaczą' AND f.rezyser = 'Olaf Lubaszenko'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Tomasz_W');

-- Kiler
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'SiarzewskiFan', 10, 'Siara i Ryba to duet wszech czasów. Machulski w najwyższej reżyserskiej formie. Humor inteligentny, wartka intryga i niesamowita obsada drugoplanowa.'
FROM `filmy` f WHERE f.tytul = 'Kiler' AND f.rezyser = 'Juliusz Machulski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'SiarzewskiFan');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Recenzent_Filmweb', 8, 'Świetne, bezpretensjonalne kino rozrywkowe lat 90. Rewiński kradnie każdą scenę, a Pazura doskonale odnajduje się w roli poczciwego pechowca.'
FROM `filmy` f WHERE f.tytul = 'Kiler' AND f.rezyser = 'Juliusz Machulski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Recenzent_Filmweb');

-- Boże Ciało
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'KrytykKatolicki', 9, 'Głęboki, wielowymiarowy traktat o wierze, hipokryzji prowincji i potrzebie przebaczenia. Hipnotyzująca kreacja Bartosza Bieleni – zasłużona nominacja do Oscara.'
FROM `filmy` f WHERE f.tytul = 'Boże Ciało' AND f.rezyser = 'Jan Komasa'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'KrytykKatolicki');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Jakub_Film', 9, 'Mocne, bezkompromisowe kino społeczne. Jan Komasa świetnie poprowadził młodych aktorów, a zdjęcia Piotra Sobocińskiego Jr. budują niesamowity, surowy nastrój.'
FROM `filmy` f WHERE f.tytul = 'Boże Ciało' AND f.rezyser = 'Jan Komasa'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Jakub_Film');

-- Zimna wojna
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'ArtHouse_Lover', 10, 'Czysta poezja kadru i dźwięku. Joanna Kulig i Tomasz Kot stworzyli na ekranie magnetyczną chemię. Każdy kadr Pawlikowskiego nadaje się do oprawienia w ramki na ścianie muzeum.'
FROM `filmy` f WHERE f.tytul = 'Zimna wojna' AND f.rezyser = 'Paweł Pawlikowski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'ArtHouse_Lover');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Magda_M', 9, 'Dwie Złote Palmy w Cannes to za mało! Fenomenalne aranżacje ludowych pieśni Mazowsza przekształcające się w paryski jazz. Muzyka jest tu osobnym bohaterem.'
FROM `filmy` f WHERE f.tytul = 'Zimna wojna' AND f.rezyser = 'Paweł Pawlikowski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Magda_M');

-- Psy
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'KlasykaKina', 10, 'Film, który zmienił polskie kino po transformacji ustrojowej. Pasikowski stworzył bezkompromisowe męskie kino noir. Bogusław Linda jako Franz Maurer to pomnik popkultury.'
FROM `filmy` f WHERE f.tytul = 'Psy' AND f.rezyser = 'Władysław Pasikowski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'KlasykaKina');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Michał_PL', 9, 'Mocny, ostry język, wybitna muzyka Michała Lorenca i bezbłędny drugi plan (Gajos i Pazura). Kamień milowy rodzimej sensacji.'
FROM `filmy` f WHERE f.tytul = 'Psy' AND f.rezyser = 'Władysław Pasikowski'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Michał_PL');

-- Znachor
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'WzruszonyWidz', 10, 'Ten film to tradycja każdego polskiego domu. Trudno powstrzymać łzy w finałowej scenie na sali sądowej: Proszę państwa, to jest profesor Rafał Wilczur. Niezrównany Bińczycki.'
FROM `filmy` f WHERE f.tytul = 'Znachor' AND f.rezyser = 'Jerzy Hoffman'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'WzruszonyWidz');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Ewa_Krakow', 10, 'Klasyka nad klasykami. Piękna historia o szlachetności, miłości ponad podziałami społecznymi i niegasnącej nadziei. Anna Dymna i Jerzy Bińczycki w życiowej formie.'
FROM `filmy` f WHERE f.tytul = 'Znachor' AND f.rezyser = 'Jerzy Hoffman'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Ewa_Krakow');

-- Sami swoi
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'RetroMania', 10, 'Sąd sądem, a sprawiedliwość musi być po naszej stronie! Wacław Kowalski i Władysław Hańcza stworzyli najsłynniejszy duet sąsiedzki w dziejach kinematografii.'
FROM `filmy` f WHERE f.tytul = 'Sami swoi' AND f.rezyser = 'Sylwester Chęciński'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'RetroMania');

INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`)
SELECT f.id, 'Grzegorz_65', 9, 'Cudowna, ciepła komedia obyczajowa z Kresami w tle. Pomimo upływu dziesięcioleci film wciąż bawi do łez kolejne pokolenia Polaków.'
FROM `filmy` f WHERE f.tytul = 'Sami swoi' AND f.rezyser = 'Sylwester Chęciński'
AND NOT EXISTS (SELECT 1 FROM `recenzje` r WHERE r.film_id = f.id AND r.autor = 'Grzegorz_65');
