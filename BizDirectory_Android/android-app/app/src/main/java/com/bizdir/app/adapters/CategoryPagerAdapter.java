package com.bizdir.app.adapters;

import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import androidx.fragment.app.FragmentActivity;
import androidx.viewpager2.adapter.FragmentStateAdapter;

import com.bizdir.app.utils.Constants;

import java.util.ArrayList;
import java.util.List;

public class CategoryPagerAdapter extends FragmentStateAdapter {

    private final List<CategoryFragment> fragments = new ArrayList<>();

    public CategoryPagerAdapter(@NonNull FragmentActivity fa) {
        super(fa);
        // Create one fragment per category
        for (String cat : Constants.CATEGORIES) {
            fragments.add(CategoryFragment.newInstance(cat));
        }
    }

    @NonNull
    @Override
    public Fragment createFragment(int position) {
        return fragments.get(position);
    }

    @Override
    public int getItemCount() {
        return fragments.size();
    }

    public CategoryFragment getFragment(int position) {
        return fragments.get(position);
    }
}
