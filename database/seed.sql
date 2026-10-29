-- =======================================================
-- Baza Danych: cinehub_db (Kwalifikacja INF.03)
-- Zestaw Danych Początkowych (Seed Data) - Polskie Kino
-- Rola: Content Agent
-- =======================================================

USE `cinehub_db`;

-- Wyłączenie kluczy obcych na czas czyszczenia tabel
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `recenzje`;
TRUNCATE TABLE `obsada`;
TRUNCATE TABLE `aktorzy`;
TRUNCATE TABLE `filmy`;
SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------
-- 1. Tabela: filmy (8 kultowych polskich dzieł kinematografii)
-- -------------------------------------------------------
INSERT INTO `filmy` (`id`, `tytul`, `rezyser`, `gatunek`, `rok_premiery`, `czas_trwania`, `opis`, `plakat`) VALUES
(1, 'Dzień świra', 'Marek Koterski', 'Komediodramat', 2002, 93, 
'Zgorzkniały, sfrustrowany polonista i rozwodnik Adaś Miauczyński zmaga się z natręctwami codziennego życia, samotnością oraz absurdem otaczającej go polskiej rzeczywistości. Każda z pozoru błaha czynność urasta do rangi życiowego dramatu.',
'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?auto=format&fit=crop&w=800&q=80'),

(2, 'Chłopaki nie płaczą', 'Olaf Lubaszenko', 'Komedia kryminalna', 2000, 96, 
'Młody, wrażliwy skrzypek Kuba Brenner oraz jego nieśmiały kolega przypadkowo wplątują się w mafijne porachunki stołecznych gangsterów Bolca, Freda i Gruchy po nieudanej transakcji w hotelowym pokoju.',
'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=800&q=80'),

(3, 'Kiler', 'Juliusz Machulski', 'Komedia sensacyjna', 1997, 104, 
'Poczciwy warszawski taksówkarz Jurek Kiler zostaje omyłkowo wzięty przez policję za bezwzględnego płatnego zabójcę o tym samym pseudonimie. Gdy trafia do więzienia, mafijny boss Siara organizuje brawurową ucieczkę.',
'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=800&q=80'),

(4, 'Boże Ciało', 'Jan Komasa', 'Dramat', 2019, 115, 
'Dwudziestoletni Daniel po wyjściu z zakładu poprawczego marzy o wstąpieniu do seminarium. Pod wpływem zbiegu okoliczności zostaje wzięty za młodego księdza w małym podkarpackim miasteczku, wnosząc świeżą energię do straumatyzowanej społeczności.',
'https://images.unsplash.com/photo-1543857778-c4a1a3e0b2eb?auto=format&fit=crop&w=800&q=80'),

(5, 'Zimna wojna', 'Paweł Pawlikowski', 'Melodramat', 2018, 88, 
'Zrealizowana w czerni i bieli epicka opowieść o trudnej i niemożliwej miłości tancerki Zuli i kompozytora Wiktora na tle zimnowojennej Europy lat 50. i 60. – od powojennej Polski, przez Berlin i Jugosławię, po paryskie kluby jazzowe.',
'https://images.unsplash.com/photo-1518676590629-3dcbd9c5a5c9?auto=format&fit=crop&w=800&q=80'),

(6, 'Psy', 'Władysław Pasikowski', 'Film sensacyjny', 1992, 104, 
'Początek lat 90. Byli funkcjonariusze aparatu bezpieczeństwa po weryfikacji trafiają do nowo formowanej policji. Podkomisarz Franciszek Maurer staje do bezwzględnej walki z brutalnym gangiem przemytników dowodzonym przez dawnego kolegę.',
'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=800&q=80'),

(7, 'Znachor', 'Jerzy Hoffman', 'Melodramat', 1982, 128, 
'Wybitny chirurg profesor Rafał Wilczur traci rodzinę i w wyniku brutalnego pobicia doznaje całkowitej amnezji. Przez lata tuła się po wsiach jako Antoni Kosiba, dokonując cudownych uzdrowień, nie pamiętając kim naprawdę jest.',
'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=800&q=80'),

(8, 'Sami swoi', 'Sylwester Chęciński', 'Komedia', 1967, 78, 
'Zwaśnione od pokoleń kresowe rodziny Kazimierza Pawlaka i Władysława Kargula osiedlają się po II wojnie światowej na Ziemiach Odzyskanych w sąsiadujących gospodarstwach. Ich dawny spór o miedzę i kota odżywa ze zdwojoną siłą.',
'https://images.unsplash.com/photo-1533929736458-ca588d08c8be?auto=format&fit=crop&w=800&q=80');

-- -------------------------------------------------------
-- 2. Tabela: aktorzy (Wielkie postacie polskiego kina i teatru)
-- -------------------------------------------------------
INSERT INTO `aktorzy` (`id`, `imie`, `nazwisko`, `data_urodzenia`, `zdjecie`, `biografia`) VALUES
(1, 'Marek', 'Kondrat', '1950-10-18', 
'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', 
'Wybitny polski aktor teatralny i filmowy. Zasłynął rolami w Zaklętych rewirach, Smudze cienia, Pułkowniku Kwiatkowskim oraz kreacją Adasia Miauczyńskiego w Dniu świra Marka Koterskiego.'),

(2, 'Janina', 'Traczykówna', '1930-07-25', 
'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80', 
'Ceniona polska aktorka teatralna i filmowa. Niezapomniana matka Adasia Miauczyńskiego w Dniu świra oraz odtwórczyni ról w licznych spektaklach Teatru Dramatycznego w Warszawie.'),

(3, 'Andrzej', 'Grabowski', '1952-03-15', 
'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80', 
'Aktor teatralny, telewizyjny i filmowy. Laureat wielu nagród za role dramatyczne w teatrze oraz filmach takich jak Dzień świra, Pitbull czy Pod Mocnym Aniołem.'),

(4, 'Maciej', 'Stuhr', '1975-06-23', 
'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 
'Aktor filmowy i kabaretowy, felietonista i konferansjer. Grał główne role w Chłopakach nie płaczą, Poranku kojota, Pokłosiu Władysława Pasikowskiego oraz Belfrze.'),

(5, 'Cezary', 'Pazura', '1962-06-13', 
'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80', 
'Jeden z najpopularniejszych polskich aktorów filmowych przełomu wieków. Znakomite role w Kilerze, Psach, Chłopakach nie płaczą, Nic śmiesznego oraz 13 posterunku.'),

(6, 'Michał', 'Milowicz', '1970-09-16', 
'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80', 
'Aktor, wokalista i reżyser. Ikoniczny wykonawca ról w komediach Olafa Lubaszenki: Bolec w Chłopaki nie płaczą oraz Brylant w Poranku kojota.'),

(7, 'Mirosław', 'Zbrojewicz', '1957-02-25', 
'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=400&q=80', 
'Aktor o charakterystycznym, głębokim głosie i charyzmie. Niezapomniany Grucha z Chłopaków nie płaczą, zagrał również w dziesiątkach produkcji teatralnych i filmowych.'),

(8, 'Janusz', 'Rewiński', '1949-09-16', 
'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=400&q=80', 
'Satyryk, aktor i komik. Legendarny Stefan "Siara" Siarzewski w kultowej dylogii Kiler Juliusza Machulskiego oraz współtwórca Kabaretu Olgi Lipińskiej.'),

(9, 'Jerzy', 'Stuhr', '1947-04-18', 
'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=400&q=80', 
'Monumentalna postać polskiej kultury, profesor sztuk teatralnych, reżyser i scenarzysta. Role w Wodzireju, Seksmisji, Kilerze, Dekalogu oraz własnych filmach jak Historie miłosne.'),

(10, 'Katarzyna', 'Figura', '1962-03-22', 
'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80', 
'Jedna z najbardziej wyrazistych polskich aktorek. Laureatka nagród na FPFF w Gdyni za role w Żurku oraz Ubu Królu, a także niezapomniana Ryszarda "Siara" w Kilerze.'),

(11, 'Bartosz', 'Bielenia', '1992-05-15', 
'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=400&q=80', 
'Aktor młodego pokolenia o magnetycznym spojrzeniu i wielkim kunszcie. Za rolę Daniela w nominowanym do Oscara filmie Boże Ciało Jana Komasy zdobył międzynarodowe uznanie.'),

(12, 'Joanna', 'Kulig', '1982-06-24', 
'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80', 
'Aktorka filmowa i wokalistka, zdobywczyni Europejskiej Nagrody Filmowej za pierwszoplanową rolę Zuli w Zimnej wojnie Pawła Pawlikowskiego. Członkini Amerykańskiej Akademii Filmowej.'),

(13, 'Tomasz', 'Kot', '1977-04-21', 
'https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?auto=format&fit=crop&w=400&q=80', 
'Wszechstronny aktor dramatyczny. Laureat Orła za rolę profesora Religi w Bogach oraz gwiazda Zimnej wojny, Skazanego na bluesa i serialu Wielka woda.'),

(14, 'Bogusław', 'Linda', '1952-06-27', 
'https://images.unsplash.com/photo-1513956589380-bad6acb9b9d4?auto=format&fit=crop&w=400&q=80', 
'Niekwestionowana ikona polskiego kina akcji i kina moralnego niepokoju. Legendarny Franz Maurer w Psach, kreacje w Przypadku Kieślowskiego, Krollu, Sarze i Quo Vadis.'),

(15, 'Janusz', 'Gajos', '1939-09-23', 
'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80', 
'Jeden z najwybitniejszych polskich aktorów w dziejach. Tytan sceny Teatru Narodowego, genialne role w Przesłuchaniu, Żółtym szaliku, Ucieczce z kina Wolność i Psach.'),

(16, 'Jerzy', 'Bińczycki', '1937-09-06', 
'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80', 
'Wybitny krakowski aktor Starego Teatru. Ukochał go cały kraj za poruszającą, ciepłą rolę profesora Rafała Wilczura / Antoniego Kosiby w ekranizacji Znachora Jerzego Hoffmana.'),

(17, 'Anna', 'Dymna', '1951-07-20', 
'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80', 
'Legenda polskiego kina, działaczka społeczna i założycielka fundacji Mimo Wszystko. Wspaniałe role w Znachorze (Marysia), Nie ma mocnych (Ania Pawlakówna) oraz Mistrzu i Małgorzacie.'),

(18, 'Wacław', 'Kowalski', '1916-05-02', 
'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=400&q=80', 
'Mistrz komedii charakterystycznej. Stworzył niezapomnianą postać Kazimierza Pawlaka w trylogii Chęcińskiego (Sami swoi, Nie ma mocnych, Kochaj albo rzuć) oraz Popiołka w Domu.'),

(19, 'Władysław', 'Hańcza', '1905-05-18', 
'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 
'Mistrz słowa i gry aktorskiej. Władysław Kargul w Samych swoich, Maciej Boryna w Chłopach oraz Janusz Radziwiłł w Potopie Jerzego Hoffmana.');

-- -------------------------------------------------------
-- 3. Tabela: obsada (Relacje M:N film <-> aktor z nazwą roli)
-- -------------------------------------------------------
INSERT INTO `obsada` (`film_id`, `aktor_id`, `rola`) VALUES
-- Dzień świra (film_id: 1)
(1, 1, 'Adaś Miauczyński'),
(1, 2, 'Mama Adasia'),
(1, 3, 'Sąsiad Rączka'),

-- Chłopaki nie płaczą (film_id: 2)
(2, 4, 'Kuba Brenner'),
(2, 5, 'Fred'),
(2, 6, 'Bolec'),
(2, 7, 'Grucha'),

-- Kiler (film_id: 3)
(3, 5, 'Jurek Kiler'),
(3, 8, 'Stefan "Siara" Siarzewski'),
(3, 9, 'Komisarz Ryba'),
(3, 10, 'Ryszarda "Siara" Siarzewska'),

-- Boże Ciało (film_id: 4)
(4, 11, 'Daniel / ksiądz Tomasz'),

-- Zimna wojna (film_id: 5)
(5, 12, 'Zula Lichoń'),
(5, 13, 'Wiktor Warski'),

-- Psy (film_id: 6)
(6, 14, 'Franciszek "Franz" Maurer'),
(6, 5, 'Waldemar Morawiec "Nowy"'),
(6, 15, 'Gross "Siwy"'),

-- Znachor (film_id: 7)
(7, 16, 'Prof. Rafał Wilczur / Antoni Kosiba'),
(7, 17, 'Maria Jolanta Wilczur'),

-- Sami swoi (film_id: 8)
(8, 18, 'Kazimierz Pawlak'),
(8, 19, 'Władysław Kargul');

-- -------------------------------------------------------
-- 4. Tabela: recenzje (Opinie, oceny 6-10 i komentarze krytyków/widzów)
-- -------------------------------------------------------
INSERT INTO `recenzje` (`film_id`, `autor`, `ocena`, `tresc`) VALUES
-- Recenzje: Dzień świra
(1, 'Filmoznawca_PL', 10, 'Arcydzieło polskiego kina początku XXI wieku. Koterski dokonał bezlitosnej, a zarazem czułej wiwisekcji polskich kompleksów. Kondrat w roli życia – tragizm i komizm w doskonałej symbiozie.'),
(1, 'Marta_Kino', 9, 'Film, który z wiekiem ogląda się coraz bardziej jak dramat niż komedię. Każdy z nas ma w sobie odrobinę z Adasia Miauczyńskiego. Dialogi przeszły do języka potocznego.'),
(1, 'PiotrK', 8, 'Bardzo gorzka i przenikliwa satyra. Miejscami bolesna w odbiorze, ale pod względem scenariusza i rytmu językowego to absolutny majstersztyk.'),

-- Recenzje: Chłopaki nie płaczą
(2, 'Kinoman90', 10, 'Kultowa komedia, którą potrafię zacytować niemal w całości od deski do deski. Pazura, Milowicz i Zbrojewicz stworzyli galerię postaci, które nigdy się nie zestarzeją.'),
(2, 'Tomasz_W', 9, 'Znakomity timing komediowy, lekkość reżyserii Olafa Lubaszenki i genialna ścieżka dźwiękowa. Polski odpowiednik kina Tarantino w swojskim, warszawskim wydaniu.'),

-- Recenzje: Kiler
(3, 'SiarzewskiFan', 10, 'Siara i Ryba to duet wszech czasów. Machulski w najwyższej reżyserskiej formie. Humor inteligentny, wartka intryga i niesamowita obsada drugoplanowa.'),
(3, 'Recenzent_Filmweb', 8, 'Świetne, bezpretensjonalne kino rozrywkowe lat 90. Rewiński kradnie każdą scenę, a Pazura doskonale odnajduje się w roli poczciwego pechowca.'),

-- Recenzje: Boże Ciało
(4, 'KrytykKatolicki', 9, 'Głęboki, wielowymiarowy traktat o wierze, hipokryzji prowincji i potrzebie przebaczenia. Hipnotyzująca kreacja Bartosza Bieleni – zasłużona nominacja do Oscara.'),
(4, 'Jakub_Film', 9, 'Mocne, bezkompromisowe kino społeczne. Jan Komasa świetnie poprowadził młodych aktorów, a zdjęcia Piotra Sobocińskiego Jr. budują niesamowity, surowy nastrój.'),

-- Recenzje: Zimna wojna
(5, 'ArtHouse_Lover', 10, 'Czysta poezja kadru i dźwięku. Joanna Kulig i Tomasz Kot stworzyli na ekranie magnetyczną chemię. Każdy kadr Pawlikowskiego nadaje się do oprawienia w ramki na ścianie muzeum.'),
(5, 'Magda_M', 9, 'Dwie Złote Palmy w Cannes to za mało! Fenomenalne aranżacje ludowych pieśni Mazowsza przekształcające się w paryski jazz. Muzyka jest tu osobnym bohaterem.'),

-- Recenzje: Psy
(6, 'KlasykaKina', 10, 'Film, który zmienił polskie kino po transformacji ustrojowej. Pasikowski stworzył bezkompromisowe męskie kino noir. Bogusław Linda jako Franz Maurer to pomnik popkultury.'),
(6, 'Michał_PL', 9, 'Mocny, ostry język, wybitna muzyka Michała Lorenca i bezbłędny drugi plan (Gajos i Pazura). Kamień milowy rodzimej sensacji.'),

-- Recenzje: Znachor
(7, 'WzruszonyWidz', 10, 'Ten film to tradycja każdego polskiego domu. Trudno powstrzymać łzy w finałowej scenie na sali sądowej: Proszę państwa, to jest profesor Rafał Wilczur. Niezrównany Bińczycki.'),
(7, 'Ewa_Krakow', 10, 'Klasyka nad klasykami. Piękna historia o szlachetności, miłości ponad podziałami społecznymi i niegasnącej nadziei. Anna Dymna i Jerzy Bińczycki w życiowej formie.'),

-- Recenzje: Sami swoi
(8, 'RetroMania', 10, 'Sąd sądem, a sprawiedliwość musi być po naszej stronie! Wacław Kowalski i Władysław Hańcza stworzyli najsłynniejszy duet sąsiedzki w dziejach kinematografii.'),
(8, 'Grzegorz_65', 9, 'Cudowna, ciepła komedia obyczajowa z Kresami w tle. Pomimo upływu dziesięcioleci film wciąż bawi do łez kolejne pokolenia Polaków.');
