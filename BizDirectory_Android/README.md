# BizDirectory - Business Directory Android App

## Project Overview
A business directory Android application that allows users to browse, search, and add businesses organized by categories. Built as part of the Mobile Application Development course.

## Authors
- Bojan Novkovski


## Features
- 📋 **Tab-based navigation** — Services, Entertainment, Industry, Education categories
- 🔍 **Search** — Real-time filtering by company name within each tab
- ➕ **Add companies** — Full form to add new businesses with multi-category selection
- 🗺️ **Geolocation** — Toast notification when within 50m of a listed business
- 🌐 **Remote backend** — PHP + MySQL REST API for data persistence
- 📞 **Rich company listings** — Name, address, phone, website per card

## Tech Stack
| Layer | Technology |
|-------|-----------|
| Mobile App | Android Studio + Java |
| UI | TabLayout + ViewPager2 + ListView |
| Networking | OkHttp / HttpURLConnection |
| Location | Android FusedLocationProviderClient |
| Backend | PHP 8+ |
| Database | MySQL |

## Project Structure
```
BizDirectory/
├── android-app/         # Android Studio project
│   └── app/src/main/
│       ├── java/com/bizdir/app/
│       │   ├── activities/      # MainActivity, AddCompanyActivity
│       │   ├── adapters/        # CompanyAdapter, PagerAdapter
│       │   ├── models/          # Company.java
│       │   ├── utils/           # LocationHelper, Constants
│       │   └── api/             # ApiClient.java
│       └── res/
│           ├── layout/          # All XML layouts
│           ├── menu/            # Menu XML
│           └── values/          # strings, colors, styles
└── backend/             # PHP REST API
    ├── config.php
    ├── get_companies.php
    └── add_company.php
```

## Backend Setup
1. Upload `backend/` folder to your web server
2. Create MySQL database and run `backend/schema.sql`
3. Update `backend/config.php` with your DB credentials
4. Update `Constants.java` in the Android app with your server URL

## References
- [Android ListView Custom Adapter](https://www.journaldev.com/10416/android-listview-with-custom-adapter-example-tutorial)
- [TabLayout + ViewPager](https://www.androidhive.info/2012/05/android-combining-tab-layout-and-list-view/)
- [Android FusedLocationProvider](https://developer.android.com/training/location/retrieve-current)
- [OkHttp](https://square.github.io/okhttp/)
