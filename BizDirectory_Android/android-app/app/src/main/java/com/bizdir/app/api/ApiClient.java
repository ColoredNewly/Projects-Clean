package com.bizdir.app.api;

import android.util.Log;

import com.bizdir.app.models.Company;
import com.bizdir.app.utils.Constants;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

import android.os.Handler;
import android.os.Looper;

public class ApiClient {

    private static final String TAG = "ApiClient";
    private final ExecutorService executor = Executors.newCachedThreadPool();
    private final Handler mainHandler = new Handler(Looper.getMainLooper());

    // -------------------------------------------------------
    // Callback interfaces
    // -------------------------------------------------------
    public interface CompaniesCallback {
        void onSuccess(List<Company> companies);
        void onError(String error);
    }

    public interface SaveCallback {
        void onSuccess(String message);
        void onError(String error);
    }

    // -------------------------------------------------------
    // Fetch companies from server (runs on background thread)
    // -------------------------------------------------------
    public void getCompanies(CompaniesCallback callback) {
        executor.execute(() -> {
            try {
                URL url = new URL(Constants.URL_GET_COMPANIES);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setConnectTimeout(10000);
                conn.setReadTimeout(10000);

                int responseCode = conn.getResponseCode();
                if (responseCode == HttpURLConnection.HTTP_OK) {
                    BufferedReader reader = new BufferedReader(
                            new InputStreamReader(conn.getInputStream()));
                    StringBuilder sb = new StringBuilder();
                    String line;
                    while ((line = reader.readLine()) != null) sb.append(line);
                    reader.close();

                    List<Company> companies = parseCompanies(sb.toString());
                    mainHandler.post(() -> callback.onSuccess(companies));
                } else {
                    mainHandler.post(() -> callback.onError("HTTP error: " + responseCode));
                }
                conn.disconnect();
            } catch (Exception e) {
                Log.e(TAG, "getCompanies error", e);
                mainHandler.post(() -> callback.onError(e.getMessage()));
            }
        });
    }

    // -------------------------------------------------------
    // Save a company to the server (POST)
    // -------------------------------------------------------
    public void addCompany(Company company, SaveCallback callback) {
        executor.execute(() -> {
            try {
                String postData =
                        "name="       + URLEncoder.encode(company.getName(),       "UTF-8") +
                        "&address="   + URLEncoder.encode(company.getAddress(),    "UTF-8") +
                        "&latitude="  + company.getLatitude() +
                        "&longitude=" + company.getLongitude() +
                        "&email="     + URLEncoder.encode(company.getEmail(),      "UTF-8") +
                        "&phone="     + URLEncoder.encode(company.getPhone(),      "UTF-8") +
                        "&website="   + URLEncoder.encode(company.getWebsite(),    "UTF-8") +
                        "&categories="+ URLEncoder.encode(company.getCategories(), "UTF-8");

                URL url = new URL(Constants.URL_ADD_COMPANY);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setConnectTimeout(10000);
                conn.setReadTimeout(10000);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

                OutputStream os = conn.getOutputStream();
                os.write(postData.getBytes("UTF-8"));
                os.flush();
                os.close();

                int responseCode = conn.getResponseCode();
                BufferedReader reader = new BufferedReader(
                        new InputStreamReader(conn.getInputStream()));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) sb.append(line);
                reader.close();

                String responseBody = sb.toString();
                if (responseCode == HttpURLConnection.HTTP_OK) {
                    mainHandler.post(() -> callback.onSuccess(responseBody));
                } else {
                    mainHandler.post(() -> callback.onError("HTTP " + responseCode + ": " + responseBody));
                }
                conn.disconnect();
            } catch (Exception e) {
                Log.e(TAG, "addCompany error", e);
                mainHandler.post(() -> callback.onError(e.getMessage()));
            }
        });
    }

    // -------------------------------------------------------
    // Parse JSON array of companies
    // -------------------------------------------------------
    private List<Company> parseCompanies(String json) throws Exception {
        List<Company> list = new ArrayList<>();
        JSONArray arr = new JSONArray(json);
        for (int i = 0; i < arr.length(); i++) {
            JSONObject obj = arr.getJSONObject(i);
            Company c = new Company(
                    obj.optInt("id", 0),
                    obj.optString("name", ""),
                    obj.optString("address", ""),
                    obj.optDouble("latitude", 0.0),
                    obj.optDouble("longitude", 0.0),
                    obj.optString("email", ""),
                    obj.optString("phone", ""),
                    obj.optString("website", ""),
                    obj.optString("categories", "")
            );
            list.add(c);
        }
        return list;
    }
}
