package com.bizdir.app.activities;

import android.os.Bundle;
import android.text.TextUtils;
import android.view.MenuItem;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;

import com.bizdir.app.R;
import com.bizdir.app.api.ApiClient;
import com.bizdir.app.models.Company;

import java.util.ArrayList;
import java.util.List;

public class AddCompanyActivity extends AppCompatActivity {

    private EditText etName, etAddress, etLatitude, etLongitude,
                     etEmail, etPhone, etWebsite;
    private CheckBox cbIndustry, cbEntertainment, cbEducation, cbServices;
    private ApiClient apiClient;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_add_company);

        // Toolbar with back button
        Toolbar toolbar = findViewById(R.id.toolbarAdd);
        setSupportActionBar(toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setTitle("Додади компанија");
        }

        apiClient = new ApiClient();

        // Bind views
        etName          = findViewById(R.id.etName);
        etAddress       = findViewById(R.id.etAddress);
        etLatitude      = findViewById(R.id.etLatitude);
        etLongitude     = findViewById(R.id.etLongitude);
        etEmail         = findViewById(R.id.etEmail);
        etPhone         = findViewById(R.id.etPhone);
        etWebsite       = findViewById(R.id.etWebsite);
        cbIndustry      = findViewById(R.id.cbIndustry);
        cbEntertainment = findViewById(R.id.cbEntertainment);
        cbEducation     = findViewById(R.id.cbEducation);
        cbServices      = findViewById(R.id.cbServices);
        Button btnSave  = findViewById(R.id.btnSave);

        btnSave.setOnClickListener(v -> saveCompany());
    }

    private void saveCompany() {
        String name    = etName.getText().toString().trim();
        String address = etAddress.getText().toString().trim();
        String latStr  = etLatitude.getText().toString().trim();
        String lngStr  = etLongitude.getText().toString().trim();
        String email   = etEmail.getText().toString().trim();
        String phone   = etPhone.getText().toString().trim();
        String website = etWebsite.getText().toString().trim();

        // Basic validation
        if (TextUtils.isEmpty(name)) {
            etName.setError("Задолжително поле");
            etName.requestFocus();
            return;
        }
        if (TextUtils.isEmpty(address)) {
            etAddress.setError("Задолжително поле");
            etAddress.requestFocus();
            return;
        }

        // Parse coordinates
        double latitude = 0.0, longitude = 0.0;
        try {
            if (!latStr.isEmpty()) latitude  = Double.parseDouble(latStr);
            if (!lngStr.isEmpty()) longitude = Double.parseDouble(lngStr);
        } catch (NumberFormatException e) {
            Toast.makeText(this, "Невалидни координати", Toast.LENGTH_SHORT).show();
            return;
        }

        // Build categories string
        List<String> selectedCats = new ArrayList<>();
        if (cbServices.isChecked())      selectedCats.add("Services");
        if (cbEntertainment.isChecked()) selectedCats.add("Entertainment");
        if (cbIndustry.isChecked())      selectedCats.add("Industry");
        if (cbEducation.isChecked())     selectedCats.add("Education");

        if (selectedCats.isEmpty()) {
            Toast.makeText(this, "Изберете барем една категорија", Toast.LENGTH_SHORT).show();
            return;
        }

        String categories = TextUtils.join(",", selectedCats);

        Company company = new Company(0, name, address, latitude, longitude,
                                      email, phone, website, categories);

        // Disable button to prevent double-submit
        Button btnSave = findViewById(R.id.btnSave);
        btnSave.setEnabled(false);
        btnSave.setText("Зачувување...");

        apiClient.addCompany(company, new ApiClient.SaveCallback() {
            @Override
            public void onSuccess(String message) {
                Toast.makeText(AddCompanyActivity.this,
                        "Компанијата е успешно зачувана!", Toast.LENGTH_LONG).show();
                setResult(RESULT_OK);
                finish();
            }

            @Override
            public void onError(String error) {
                Toast.makeText(AddCompanyActivity.this,
                        "Грешка: " + error, Toast.LENGTH_LONG).show();
                btnSave.setEnabled(true);
                btnSave.setText("Зачувај");
            }
        });
    }

    @Override
    public boolean onOptionsItemSelected(@NonNull MenuItem item) {
        if (item.getItemId() == android.R.id.home) {
            finish();
            return true;
        }
        return super.onOptionsItemSelected(item);
    }
}
