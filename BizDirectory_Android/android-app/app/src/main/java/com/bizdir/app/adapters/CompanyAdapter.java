package com.bizdir.app.adapters;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Filter;
import android.widget.Filterable;
import android.widget.ImageView;
import android.widget.TextView;

import com.bizdir.app.R;
import com.bizdir.app.models.Company;

import java.util.ArrayList;
import java.util.List;

public class CompanyAdapter extends BaseAdapter implements Filterable {

    private final Context context;
    private List<Company> originalList;
    private List<Company> filteredList;
    private CompanyFilter filter;

    public CompanyAdapter(Context context, List<Company> companies) {
        this.context = context;
        this.originalList = new ArrayList<>(companies);
        this.filteredList = new ArrayList<>(companies);
    }

    public void updateData(List<Company> newList) {
        this.originalList = new ArrayList<>(newList);
        this.filteredList = new ArrayList<>(newList);
        notifyDataSetChanged();
    }

    @Override
    public int getCount() { return filteredList.size(); }

    @Override
    public Company getItem(int position) { return filteredList.get(position); }

    @Override
    public long getItemId(int position) { return filteredList.get(position).getId(); }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        ViewHolder holder;

        if (convertView == null) {
            convertView = LayoutInflater.from(context)
                    .inflate(R.layout.item_company, parent, false);
            holder = new ViewHolder();
            holder.logo    = convertView.findViewById(R.id.imgLogo);
            holder.name    = convertView.findViewById(R.id.tvName);
            holder.address = convertView.findViewById(R.id.tvAddress);
            holder.phone   = convertView.findViewById(R.id.tvPhone);
            holder.website = convertView.findViewById(R.id.tvWebsite);
            convertView.setTag(holder);
        } else {
            holder = (ViewHolder) convertView.getTag();
        }

        Company c = filteredList.get(position);
        holder.name.setText(c.getName());
        holder.address.setText(c.getAddress());
        holder.phone.setText(c.getPhone().isEmpty() ? "—" : c.getPhone());
        holder.website.setText(c.getWebsite().isEmpty() ? "—" : c.getWebsite());
        // Default logo icon; replace with image loading library for real logos
        holder.logo.setImageResource(R.drawable.ic_business);

        return convertView;
    }

    @Override
    public Filter getFilter() {
        if (filter == null) filter = new CompanyFilter();
        return filter;
    }

    // -------------------------------------------------------
    // ViewHolder pattern
    // -------------------------------------------------------
    static class ViewHolder {
        ImageView logo;
        TextView name, address, phone, website;
    }

    // -------------------------------------------------------
    // Filter implementation
    // -------------------------------------------------------
    private class CompanyFilter extends Filter {
        @Override
        protected FilterResults performFiltering(CharSequence constraint) {
            FilterResults results = new FilterResults();
            if (constraint == null || constraint.length() == 0) {
                results.values = new ArrayList<>(originalList);
                results.count  = originalList.size();
            } else {
                String query = constraint.toString().toLowerCase().trim();
                List<Company> filtered = new ArrayList<>();
                for (Company c : originalList) {
                    if (c.getName().toLowerCase().contains(query)) {
                        filtered.add(c);
                    }
                }
                results.values = filtered;
                results.count  = filtered.size();
            }
            return results;
        }

        @SuppressWarnings("unchecked")
        @Override
        protected void publishResults(CharSequence constraint, FilterResults results) {
            filteredList = (List<Company>) results.values;
            notifyDataSetChanged();
        }
    }
}
