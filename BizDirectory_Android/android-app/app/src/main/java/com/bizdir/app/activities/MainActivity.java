package com.bizdir.app.activities;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.viewpager2.widget.ViewPager2;

import com.bizdir.app.R;
import com.bizdir.app.adapters.CategoryFragment;
import com.bizdir.app.adapters.CategoryPagerAdapter;
import com.bizdir.app.api.ApiClient;
import com.bizdir.app.models.Company;
import com.bizdir.app.utils.Constants;
import com.bizdir.app.utils.LocationHelper;
import com.google.android.material.tabs.TabLayout;
import com.google.android.material.tabs.TabLayoutMediator;

import java.util.ArrayList;
import java.util.List;

public class MainActivity extends AppCompatActivity {

    private static final int LOCATION_PERMISSION_REQUEST = 100;
    public static final int REQUEST_ADD_COMPANY = 200;

    private CategoryPagerAdapter pagerAdapter;
    private ApiClient apiClient;
    private LocationHelper locationHelper;
    private List<Company> allCompanies = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // Toolbar
        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        // ViewPager2 + TabLayout
        ViewPager2 viewPager = findViewById(R.id.viewPager);
        TabLayout tabLayout  = findViewById(R.id.tabLayout);

        pagerAdapter = new CategoryPagerAdapter(this);
        viewPager.setAdapter(pagerAdapter);
	viewPager.setOffscreenPageLimit(3);

        // Attach tabs to pager with Macedonian labels
        new TabLayoutMediator(tabLayout, viewPager, (tab, position) ->
                tab.setText(Constants.CATEGORY_LABELS_MK[position])
        ).attach();

        // API + Location
        apiClient     = new ApiClient();
        locationHelper = new LocationHelper(this);

        requestLocationPermission();
        loadCompanies();
    }

    // -------------------------------------------------------
    // Toolbar menu
    // -------------------------------------------------------
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(@NonNull MenuItem item) {
        if (item.getItemId() == R.id.action_add) {
            Intent intent = new Intent(this, AddCompanyActivity.class);
            startActivityForResult(intent, REQUEST_ADD_COMPANY);
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    // -------------------------------------------------------
    // Reload list after adding a company
    // -------------------------------------------------------
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == REQUEST_ADD_COMPANY && resultCode == RESULT_OK) {
            loadCompanies();
        }
    }

    // -------------------------------------------------------
    // Fetch companies from server
    // -------------------------------------------------------
    private void loadCompanies() {
        apiClient.getCompanies(new ApiClient.CompaniesCallback() {
            @Override
            public void onSuccess(List<Company> companies) {
                allCompanies = companies;
                // Push data to each fragment
                for (int i = 0; i < Constants.CATEGORIES.length; i++) {
                    CategoryFragment frag = pagerAdapter.getFragment(i);
                    if (frag != null) frag.updateCompanies(allCompanies);
                }
                // Update location helper
                locationHelper.setCompanies(allCompanies);
            }

            @Override
            public void onError(String error) {
                Toast.makeText(MainActivity.this,
                        "Грешка при вчитување: " + error, Toast.LENGTH_LONG).show();
            }
        });
    }

    // -------------------------------------------------------
    // Location permission handling
    // -------------------------------------------------------
    private void requestLocationPermission() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.ACCESS_FINE_LOCATION},
                    LOCATION_PERMISSION_REQUEST);
        } else {
            locationHelper.startTracking();
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions,
                                           @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == LOCATION_PERMISSION_REQUEST) {
            if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                locationHelper.startTracking();
            } else {
                Toast.makeText(this,
                        "Локацијата е потребна за близина известувања", Toast.LENGTH_SHORT).show();
            }
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        locationHelper.stopTracking();
    }
}
