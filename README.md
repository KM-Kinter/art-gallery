# ArtGallery Online

## Description | Opis

EN: ArtGallery Online is a web-based platform for artists to showcase their work and connect with art enthusiasts. The system features user roles (Admin, Staff, Artists, and Visitors), artwork management, exhibitions, and interactive features like comments and ratings.

PL: ArtGallery Online to internetowa platforma umożliwiająca artystom prezentację swoich prac i nawiązywanie kontaktu z miłośnikami sztuki. System oferuje role użytkowników (Administrator, Personel, Artyści i Odwiedzający), zarządzanie dziełami sztuki, wystawami oraz funkcje interaktywne, takie jak komentarze i oceny.

## Features | Funkcje

EN:
- User authentication and authorization
- Multiple user roles with different permissions
- Artwork upload and management
- Exhibition creation and management
- Rating and commenting system
- Responsive, modern design
- Secure password handling
- User profile management

PL:
- Uwierzytelnianie i autoryzacja użytkowników
- Wiele ról użytkowników z różnymi uprawnieniami
- Przesyłanie i zarządzanie dziełami sztuki
- Tworzenie wystaw i zarządzanie nimi
- System ocen i komentarzy
- Responsywny, nowoczesny design
- Bezpieczne przechowywanie haseł
- Zarządzanie profilami użytkowników

## Installation | Instalacja

EN:
1. Make sure you have XAMPP installed with PHP and MySQL
2. Clone this repository to your XAMPP's htdocs folder
3. Start Apache and MySQL services in XAMPP
4. Import the database:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named 'art_gallery'
   - Import the `database.sql` file
5. Access the application at http://localhost/projekt

PL:
1. Upewnij się, że masz zainstalowany XAMPP z PHP i MySQL
2. Sklonuj to repozytorium do folderu htdocs w XAMPP
3. Uruchom usługi Apache i MySQL w XAMPP
4. Zaimportuj bazę danych:
   - Otwórz phpMyAdmin (http://localhost/phpmyadmin)
   - Utwórz nową bazę danych o nazwie 'art_gallery'
   - Zaimportuj plik `database.sql`
5. Dostęp do aplikacji pod adresem http://localhost/projekt

## Default Admin Account | Domyślne Konto Administratora

EN:
- Username: admin
- Password: admin123

PL:
- Nazwa użytkownika: admin
- Hasło: admin123

## User Roles | Role Użytkowników

EN:
1. Admin:
   - Manage all users
   - Approve/reject artworks
   - Manage exhibitions
   - Full system access

2. Staff (Curators):
   - Manage exhibitions
   - Review artworks
   - Moderate comments

3. Artists:
   - Upload artworks
   - Create/edit their profile
   - Participate in exhibitions

4. Visitors:
   - View artworks
   - Rate and comment
   - Follow artists

PL:
1. Administrator:
   - Zarządzanie wszystkimi użytkownikami
   - Zatwierdzanie/odrzucanie dzieł
   - Zarządzanie wystawami
   - Pełny dostęp do systemu

2. Personel (Kuratorzy):
   - Zarządzanie wystawami
   - Przeglądanie dzieł
   - Moderowanie komentarzy

3. Artyści:
   - Przesyłanie dzieł
   - Tworzenie/edycja profilu
   - Udział w wystawach

4. Odwiedzający:
   - Przeglądanie dzieł
   - Ocenianie i komentowanie
   - Obserwowanie artystów

## Security | Bezpieczeństwo

EN:
- Passwords are hashed using PHP's password_hash()
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars()
- CSRF protection
- Secure session handling

PL:
- Hasła są hashowane przy użyciu password_hash()
- Ochrona przed SQL injection przy użyciu prepared statements
- Ochrona przed XSS przy użyciu htmlspecialchars()
- Ochrona przed CSRF
- Bezpieczne zarządzanie sesjami 