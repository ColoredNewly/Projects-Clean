package com.bizdir.app.models;

public class Company {
    private int id;
    private String name;
    private String address;
    private double latitude;
    private double longitude;
    private String email;
    private String phone;
    private String website;
    private String categories; // comma-separated e.g. "Services,Entertainment"

    public Company() {}

    public Company(int id, String name, String address, double latitude, double longitude,
                   String email, String phone, String website, String categories) {
        this.id = id;
        this.name = name;
        this.address = address;
        this.latitude = latitude;
        this.longitude = longitude;
        this.email = email;
        this.phone = phone;
        this.website = website;
        this.categories = categories;
    }

    // Getters
    public int getId()           { return id; }
    public String getName()      { return name; }
    public String getAddress()   { return address; }
    public double getLatitude()  { return latitude; }
    public double getLongitude() { return longitude; }
    public String getEmail()     { return email; }
    public String getPhone()     { return phone; }
    public String getWebsite()   { return website; }
    public String getCategories(){ return categories; }

    // Setters
    public void setId(int id)              { this.id = id; }
    public void setName(String name)       { this.name = name; }
    public void setAddress(String address) { this.address = address; }
    public void setLatitude(double lat)    { this.latitude = lat; }
    public void setLongitude(double lng)   { this.longitude = lng; }
    public void setEmail(String email)     { this.email = email; }
    public void setPhone(String phone)     { this.phone = phone; }
    public void setWebsite(String website) { this.website = website; }
    public void setCategories(String c)    { this.categories = c; }

    /** Returns true if this company belongs to the given category */
    public boolean hasCategory(String category) {
        if (categories == null) return false;
        for (String c : categories.split(",")) {
            if (c.trim().equalsIgnoreCase(category)) return true;
        }
        return false;
    }
}
