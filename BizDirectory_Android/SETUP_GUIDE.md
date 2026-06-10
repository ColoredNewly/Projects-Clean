# BizDirectory — Setup Guide

## 1. Backend Setup (PHP + MySQL)

### 1a. Upload files
Upload the entire `backend/` folder to your web server (e.g. via cPanel File Manager or FTP).  
Place them in a path like `public_html/bizdir/`.

### 1b. Create the database
1. Log in to phpMyAdmin (or use the MySQL CLI)
2. Run the contents of `backend/schema.sql`  
   This creates the `bizdir` database and the `companies` table, plus 5 sample rows.

### 1c. Edit `config.php`
Open `backend/config.php` and fill in your credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_actual_db_user');
define('DB_PASS', 'your_actual_db_password');
define('DB_NAME', 'bizdir');
```

### 1d. Test the API
Open your browser and visit:
```
https://your-server.com/bizdir/get_companies.php
```
You should see a JSON array with 5 sample companies.

---

## 2. Android App Setup

### 2a. Open in Android Studio
- Open Android Studio → **Open an existing project**
- Navigate to the `android-app/` folder

### 2b. Update the server URL
Open:
```
app/src/main/java/com/bizdir/app/utils/Constants.java
```
Change:
```java
public static final String BASE_URL = "https://your-server.com/bizdir/";
```
to your actual URL.

### 2c. Add your app icon
Replace the default launcher icons in:
```
app/src/main/res/mipmap-*/ic_launcher.png
app/src/main/res/mipmap-*/ic_launcher_round.png
```
Or use **Android Studio → File → New → Image Asset** to generate icons.

### 2d. Sync & Run
- Click **Sync Project with Gradle Files** (elephant icon)
- Connect your Android device (or start an emulator)
- Click **Run ▶**

---

## 3. Features Overview

| Feature | Where |
|---------|-------|
| Tab navigation (Сервиси / Забава / Индустрија / Едукација) | `MainActivity.java` |
| Swipe between tabs | ViewPager2 — automatic |
| Company list per category | `CategoryFragment.java` + `CompanyAdapter.java` |
| Search within tab | Bottom `EditText` in `fragment_category.xml` |
| Add company form | `AddCompanyActivity.java` |
| Multi-category checkbox | `activity_add_company.xml` |
| Save to remote DB | `ApiClient.addCompany()` → `add_company.php` |
| Load from remote DB | `ApiClient.getCompanies()` → `get_companies.php` |
| 50m proximity Toast | `LocationHelper.java` |

---

## 4. API Reference

### GET /get_companies.php
Returns all companies.  
Optional: `?category=Services`

**Response:**
```json
[
  {
    "id": 1,
    "name": "Техно Сервис",
    "address": "ул. Македонија 12, Скопје",
    "latitude": 41.9981,
    "longitude": 21.4254,
    "email": "info@tehno.mk",
    "phone": "+389 2 311 1111",
    "website": "https://tehno.mk",
    "categories": "Services"
  }
]
```

### POST /add_company.php
**Body (form-urlencoded):**
| Field | Required | Notes |
|-------|----------|-------|
| `name` | ✅ | Company name |
| `address` | ✅ | Full address |
| `latitude` | — | Double |
| `longitude` | — | Double |
| `email` | — | |
| `phone` | — | |
| `website` | — | |
| `categories` | ✅ | Comma-separated: `Services,Education` |

**Response:** `{"success": true, "id": 6, "message": "Company saved successfully"}`

---

## 5. Project Structure
```
android-app/app/src/main/
├── AndroidManifest.xml
├── java/com/bizdir/app/
│   ├── activities/
│   │   ├── MainActivity.java          ← Tabs + toolbar + location
│   │   └── AddCompanyActivity.java    ← Add company form
│   ├── adapters/
│   │   ├── CategoryFragment.java      ← One tab fragment
│   │   ├── CategoryPagerAdapter.java  ← ViewPager2 adapter
│   │   └── CompanyAdapter.java        ← ListView adapter with search filter
│   ├── api/
│   │   └── ApiClient.java             ← HTTP GET + POST
│   ├── models/
│   │   └── Company.java               ← Data model
│   └── utils/
│       ├── Constants.java             ← URLs + categories
│       └── LocationHelper.java        ← FusedLocation + proximity check
└── res/
    ├── layout/
    │   ├── activity_main.xml
    │   ├── activity_add_company.xml
    │   ├── fragment_category.xml
    │   └── item_company.xml
    ├── drawable/
    │   ├── ic_business.xml
    │   └── bg_edit_text.xml
    ├── menu/menu_main.xml
    └── values/
        ├── strings.xml
        ├── colors.xml
        └── styles.xml
```
