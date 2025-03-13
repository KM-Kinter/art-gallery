# Art Gallery

## English

### Installation
1. Place the project in your XAMPP's htdocs folder
2. Create a MySQL database named `art_gallery`
3. Import the `database.sql` file into your database
4. Make sure the following directories exist and have write permissions (777):
   - `/uploads/artworks`
   - `/uploads/profiles`
   - `/images`

### Default Login Credentials
- **Admin Account:**
  - Username: admin
  - Password: admin123

### User Roles
1. **Regular User**
   - Can browse artworks
   - Can rate and comment on artworks
   - Can follow artists
   - Can edit their profile

2. **Artist**
   - All regular user features
   - Can upload their artworks
   - Can manage their artwork gallery
   - Has a public artist profile

3. **Admin**
   - All artist features
   - Can manage all users
   - Can approve/reject artworks
   - Can manage categories
   - Can moderate comments

### Using Pre-uploaded Images
The system detected 23 images in the `/uploads/artworks` directory. To use these images:

1. Log in as an artist or admin
2. Go to the upload artwork page
3. Fill in the artwork details:
   - Title
   - Description
   - Category
   - Price (optional)
4. Select one of the pre-uploaded images
5. Submit for approval

### File Requirements
- Maximum file size: 5MB
- Allowed formats: JPG, PNG, GIF
- Recommended image dimensions: 1200x800 pixels
- Images are automatically resized if needed

## Polski

### Instalacja
1. Umieść projekt w folderze htdocs serwera XAMPP
2. Utwórz bazę danych MySQL o nazwie `art_gallery`
3. Zaimportuj plik `database.sql` do bazy danych
4. Upewnij się, że następujące katalogi istnieją i mają uprawnienia do zapisu (777):
   - `/uploads/artworks`
   - `/uploads/profiles`
   - `/images`

### Domyślne dane logowania
- **Konto administratora:**
  - Nazwa użytkownika: admin
  - Hasło: admin123

### Role użytkowników
1. **Zwykły użytkownik**
   - Może przeglądać dzieła sztuki
   - Może oceniać i komentować dzieła
   - Może obserwować artystów
   - Może edytować swój profil

2. **Artysta**
   - Wszystkie funkcje zwykłego użytkownika
   - Może przesyłać swoje dzieła
   - Może zarządzać swoją galerią
   - Posiada publiczny profil artysty

3. **Administrator**
   - Wszystkie funkcje artysty
   - Może zarządzać użytkownikami
   - Może zatwierdzać/odrzucać dzieła
   - Może zarządzać kategoriami
   - Może moderować komentarze

### Korzystanie z przesłanych zdjęć
System wykrył 23 zdjęcia w katalogu `/uploads/artworks`. Aby je wykorzystać:

1. Zaloguj się jako artysta lub administrator
2. Przejdź do strony przesyłania dzieł
3. Wypełnij szczegóły dzieła:
   - Tytuł
   - Opis
   - Kategoria
   - Cena (opcjonalnie)
4. Wybierz jedno z przesłanych zdjęć
5. Wyślij do zatwierdzenia

### Wymagania dotyczące plików
- Maksymalny rozmiar pliku: 5MB
- Dozwolone formaty: JPG, PNG, GIF
- Zalecane wymiary obrazów: 1200x800 pikseli
- Obrazy są automatycznie skalowane w razie potrzeby

### Struktura katalogów
```
projekt/
├── images/              # System images (logo, default avatar)
├── uploads/
│   ├── artworks/       # Artwork images
│   └── profiles/       # User profile pictures
├── css/                # Stylesheets
├── includes/           # PHP includes
└── database.sql        # Database structure
``` 