# plekvetica-2021

Version 3.4.0 - 2023.04.14
- Added: Band of the month feature
- Updated and improved the caching database
- Fixed spelling
- Fixed: Z-Index for overlays
- Fixed image uploader preview not shown

German:
- Hinzugefügt: Bands des Monats feature
- Zwischenspeicher Datenbank aktualisiert
- Schreibfehler behoben
- Behoben: Z-Index bei den Overlays
- Behoben: Bildervorschau beim Bilderuploader wurde nicht angezeigt.


Version 3.3.1 - 2023.04.10
- Fixed: Time not set correctly when timezone was different
- Fixed: Form did not work without flatpickr
- Fixed: Flatpickr is not a requirement anymore
- Added: Recommendations for Spotify artists on Add / Edit Band
- Added: Function to flush the cache by user
- Added: Function to rebuild the cache for a single user
- Cache gets rebuilt every night
- Updated translations, added 8 new language strings
- Added function to get all users with invalid cache
- Added caching support for events loaded via shortcode (Homepage)
- Improved the performance of the image uploader tool

German:
- Behoben: Standard Zeit eines neuen Events wurde falsch angezeigt wenn andere Zeitzone
- Behoben: Formulare funktionierte nicht ohne den Flatpickr (Datumswähler)
- Behoben: Flatpickr ist keine Voraussetung mehr
- Hinzugefügt: Empfehlungen von Shopify link wenn Band hinugefügt / bearbeitet wird
- Hinzugefügt: Funktion um den Cache zu löschen für einen individuellen Nutzer
- Hinzugefügt: Funktion um den Cache zu erstellen für einen individuellen Nutzer
- Cache wird neu generiert jede Nacht
- Übersetzungen wurden überarbeitet, 9 neue Übersetzungen hinzugefügt
- Hinzugefügt: Funktionen um alle Nutzer mit ungültigem Cache zu bekommen
- Caching für events welche mit Shortcode generiert wurden (Homepage)
- Leistung der Galerie Bilder hochladen wurde verbessert

Version 3.3.0 - 2023.03.28
- Fixed: Facebook image poster
- Added: Edit images of gallery @ edit review page

Version 3.2.1 - 2023.03.18
- Fixed: Band review info not sent
- Improved email styling
- Removed subject by default from email

Version 3.2 - 2023.03.18
- Added: New Shortcode to find missing reviews
- Added Band user notification when a review is published
- Added 19 new language strings, 1 updated
- Added email footer for bands template
- Renamed some functions
- Added function to get the users by band id
- Restricted the "Send Event review to organizer" function to plekmanger users
- Fixed: Win tickets text contained None if no conditions set
- Fixed: Style for Team Calendar mobile view

Version 3.1.7 - 2023.03.14
- Fix: Cache wird nun geleert wenn ein review gespeichert wird
- Fix: Style der Kalenderansicht wurde behoben

Version 3.1.6 - 2023.03.05
- Fix: Contextmenu für Fotos funktioniert wieder
- Fix: Konzertfotos wurden nicht nach datum sortiert.

Version 3.1.5 - 2023.03.04
- Fix: Kürzlich hinzugefügte Events waren nicht aktuell
- Hinzugefügt: Caching für kürzlich hinzugefügte events
- Fix: Fataler fehler beim suchen nach Reviews behoben
- Optimiert: Code und Typenüberprüfung 

Version 3.1.3 - 2023.02.26
- Fehlende Event reviews optimiert
- Interne Cache Funktion hinzugefügt

Version 3.0.3 - 2023.02.17
- Fehler beim Entsperren des neuen users behoben.
- Cache für Block inhalte aktiviert

Version 3.0.2 - 2023.02.15
- Fehler behoben welche das nutzen externer Funktonen für Gäste blockierte

Version 3.0.1 - 2023.02.15
- Teammitlieder werden nun in passiv und aktiv unterschieden

Version 3.0 - 2023.02.10
- Neue Übersetzungen hinzugefügt
- Verschiedene Texte für die Facebook Promo funktion
- Neuer Akkreditierungsanfrage Manager (Für Veranstalter)
- Neuer Akkreditierungsstatus: Bestätigt mit Vorbehalt
- Funktion fürs automatische befüllen von Newsletterlisten
- Autoren Feld bei "Event bearbeiten" hinzugefügt
- Co-Author Plugin entfernt -> Ersetzt durch interne Funktion.
- Behoben: Es wurden nicht alle Events eines Team-Mitgliedes angezeigt
- Formatierung wird beim einfügen im Event entfernt
- Newsletter Preview hinzugefügt
- Div. Fehler behoben

Version 2.5.0
- Fix: Facebook publisher
- Facebook API integration optimiert
- Fix: Zu viele Events bei der Ticketverlosung-Seite
- Fix: Timetable wird richtig angezeigt, wenn eine Band nach Mitternacht spielt
- Diverse kleine Optimierungen
- Ticket Verlosung in Website integriert
- WIP: Automatische Ticketverlosung auf Facebook via Website


Version 2.4.1
- Fix: Style des Social media Formulars auf Mobilgeräten
- Fix: Bandbild wurde ersetzt nach dem laden der Spotify Daten
- Übersetzungen für Spotify Meldungen hinzugefügt.

Version 2.4.0
- Spotify integriert
- Fehler im Team-Kalender und Accreditations Tool behoben
- Spotify, Youtube und Twitter Link für die Bands hinzugefügt
- Menü im Backend wurde neu strukturiert

Version 2.3.0
- Diverse Fehler in user registration und passwort zurücksetzen fuktionen behoben
- Neue Klasse "Genre" hinzugefügt
- 66 neue Genres hinzugefügt
- Neuer Team Kalender
- Neue Events akkreditieren seite
- Benachrichtignungen wurden optimiert und fehler behoben
- Diverse Fehler beim Event hinzufügen Formular behoben

Version 2.1.1
- Cronjob hinzugefügt um über fehlende Akkreditierungsanfragen zu informiern
- Neue Genres hinzugefügt (42 -> XX)
- Funktion um die Kategorie-Genres mit den ACF Genres abzugleichen
- Gast Autoren erhalten eine Email, wenn sie einen Event einstellen und die Infos, ob noch Detail-angaben fehlen.
- Folloer einer Band werden Benachrichtigt, wenn ein neuer Event mit dessen Band(s) erstellt wrid.
- Diverse kleine Verbesserungen

Version 2.0.1
- Fehler im neuen Formular behoben
- Neuer Shortcode 'plek_event_upcoming_no_akkredi' zum anzeigen fehlender akkreditierungen
- Neuer Shortcode 'plek_event_recently_added' zum anzeigen neuer Events
- Diverse Style anpassungen
- Übersetzungen angepasst

Version 2.0
- Neues "Event eintragen" Formular
- Neues "Event Review schreiben" Formular
- "Band hinzufügen" Formular hinzugefügt
- Diverse Optimierungen und Fixes
- Konzertfotos können nun im Frontend hochgeladen werden
- Akkreditierungen werden nun geloggt

Version 1.1.1

- Registrierung für alle Nutzer geöffnet
- Registirerung als Community, Veranstalter oder Band möglich
- Überarbeitete My-Plekvetica Seite
- Bands können mehrere Bands managen
- Watchlist für Events hinzugefügt
- Bands können nun gefolgt werden und werden im Profil angezeigt.
- Schnelleres Nachladen von weiteren Beiträgen / Seiten
- Neue Seite mit allen Bands
- "Kein Bandlogo"-Bandlogo angepasst
- Style anpassungen
- Diverse Optimierungen und Fixes
- Angepasstes Kontextmenu für die Bilder hinzugefügt
- Benachrichtigungen im My Plekvetica sichtbar
- Einfacheres melden von veralteten Events
 
Version 1.0

- Diverse Bugfixes
- Optimierte Datenbankabfragen
- Mehrseitige Ergebnise bei den Events in der Suche und Review ansicht. Dadurch schnellere Ladezeiten
- My Plekvetica für Veranstalter und Bands
- Rollen für Bands, Veranstalter, Community & Partner hinzugefügt.
- Band bearbeiten Formular
- Benutzereinstellungen Formular (Passwort und Namen ändern)
- Ticketverlosung eingebaut

