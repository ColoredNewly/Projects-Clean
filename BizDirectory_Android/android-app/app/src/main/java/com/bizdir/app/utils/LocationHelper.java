package com.bizdir.app.utils;

import android.Manifest;
import android.content.Context;
import android.content.pm.PackageManager;
import android.location.Location;
import android.os.Looper;
import android.widget.Toast;

import androidx.core.app.ActivityCompat;

import com.bizdir.app.models.Company;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.Priority;

import java.util.List;

public class LocationHelper {

    private final Context context;
    private final FusedLocationProviderClient fusedClient;
    private LocationCallback locationCallback;
    private List<Company> companies;

    public LocationHelper(Context context) {
        this.context = context;
        this.fusedClient = LocationServices.getFusedLocationProviderClient(context);
    }

    public void setCompanies(List<Company> companies) {
        this.companies = companies;
    }

    /**
     * Start periodic location updates. Checks proximity to all companies
     * and shows a Toast if within PROXIMITY_DISTANCE_M metres.
     */
    public void startTracking() {
        if (ActivityCompat.checkSelfPermission(context, Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED) {
            return;
        }

        LocationRequest request = new LocationRequest.Builder(
                Priority.PRIORITY_HIGH_ACCURACY, 15_000L) // every 15 seconds
                .setMinUpdateIntervalMillis(10_000L)
                .build();

        locationCallback = new LocationCallback() {
            @Override
            public void onLocationResult(LocationResult result) {
                if (result == null || companies == null) return;
                Location userLoc = result.getLastLocation();
                if (userLoc == null) return;

                for (Company c : companies) {
                    float[] distResults = new float[1];
                    Location.distanceBetween(
                            userLoc.getLatitude(), userLoc.getLongitude(),
                            c.getLatitude(), c.getLongitude(),
                            distResults
                    );
                    if (distResults[0] < Constants.PROXIMITY_DISTANCE_M) {
                        String msg = "📍 Вие сте блиску до: " + c.getName();
                        Toast.makeText(context, msg, Toast.LENGTH_LONG).show();
                    }
                }
            }
        };

        fusedClient.requestLocationUpdates(request, locationCallback, Looper.getMainLooper());
    }

    /** Stop location updates (call in onDestroy / onPause) */
    public void stopTracking() {
        if (locationCallback != null) {
            fusedClient.removeLocationUpdates(locationCallback);
        }
    }
}
