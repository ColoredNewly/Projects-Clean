package com.bizdir.app.adapters;

import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import com.bizdir.app.R;
import com.bizdir.app.models.Company;

import java.util.ArrayList;
import java.util.List;

/**
 * Fragment representing one category tab.
 * Receives the category name via newInstance() and filters companies accordingly.
 */
public class CategoryFragment extends Fragment {

    private static final String ARG_CATEGORY = "category";

    private String category;
    private CompanyAdapter adapter;
    private List<Company> categoryCompanies = new ArrayList<>();
    private TextView tvEmpty;

    public static CategoryFragment newInstance(String category) {
        CategoryFragment f = new CategoryFragment();
        Bundle args = new Bundle();
        args.putString(ARG_CATEGORY, category);
        f.setArguments(args);
        return f;
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            category = getArguments().getString(ARG_CATEGORY);
        }
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_category, container, false);

        ListView listView = view.findViewById(R.id.listViewCompanies);
        EditText etSearch = view.findViewById(R.id.etSearch);
        tvEmpty = view.findViewById(R.id.tvEmpty);

        adapter = new CompanyAdapter(requireContext(), categoryCompanies);
        listView.setAdapter(adapter);

        // Live search
        etSearch.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {
                adapter.getFilter().filter(s);
            }
            @Override public void afterTextChanged(Editable s) {}
        });

        return view;
    }

    /** Called by MainActivity when fresh data arrives from the server */
public void updateCompanies(List<Company> allCompanies) {
    categoryCompanies.clear();
    for (Company c : allCompanies) {
        if (c.hasCategory(category)) {
            categoryCompanies.add(c);
        }
    }
    if (adapter != null) {
        adapter.updateData(categoryCompanies);
        tvEmpty.setVisibility(categoryCompanies.isEmpty() ? View.VISIBLE : View.GONE);
    }
}
}
