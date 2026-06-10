package com.bizdir.app.utils;

public class Constants {

    // =========================================================
    // IMPORTANT: Replace this with your actual server URL
    // =========================================================
    public static final String BASE_URL = "https://graceful-snare-utmost.ngrok-free.dev/bizdir/";

    public static final String URL_GET_COMPANIES = BASE_URL + "get_companies.php";
    public static final String URL_ADD_COMPANY   = BASE_URL + "add_company.php";

    // Tab categories — order must match tab positions in MainActivity
    public static final String[] CATEGORIES = {
        "Services",
        "Entertainment",
        "Industry",
        "Education"
    };

    // Macedonian labels for display
    public static final String[] CATEGORY_LABELS_MK = {
        "Сервиси",
        "Забава",
        "Индустрија",
        "Едукација"
    };

    // Proximity alert distance in metres
    public static final float PROXIMITY_DISTANCE_M = 50f;
}
