# Smart Kodes - Map Integration Guide

## Overview
Smart Kodes uses **Leaflet.js** with **OpenStreetMap** tiles for all mapping features. This is a completely free, open-source solution with no API keys, no usage limits, and no costs.

---

## Why Leaflet + OpenStreetMap?

### ✅ Advantages
- **100% Free:** No API keys, no billing, unlimited requests
- **Open Source:** MIT license, fully customizable
- **Lightweight:** Only ~42KB (vs Google Maps ~400KB+)
- **Privacy-Focused:** No tracking, no data collection
- **Active Community:** Regular updates and extensive plugins
- **No Vendor Lock-in:** Easy to switch tile providers
- **Offline Capable:** Can cache tiles for offline use
- **Feature-Rich:** Everything you need out of the box

### ❌ Google Maps Disadvantages
- Requires API key and credit card
- $200 free credit/month (then charges apply)
- 28,000 map loads/month limit on free tier
- Complex pricing structure
- Privacy concerns with tracking
- Vendor lock-in

---

## Current Implementation

### Work Orders Map View
**URL:** `/tenant/work-orders?view=map`

**Features:**
- Interactive map centered on work order locations
- Clickable markers for each work order
- Color-coded status indicators
- Popup with work order details
- Auto-fit bounds to show all markers
- Smooth pan and zoom
- Responsive on all devices

**Code Location:**
- **View:** `resources/views/tenant/work-orders/index.blade.php`
- **Layout:** `resources/views/tenant/layouts/app.blade.php` (Leaflet scripts loaded)

---

## How to Use the Map

### For Users (Tenant Panel)
1. Navigate to **Workforce** in sidebar
2. Click the **Map View** tab
3. View all work orders with locations on the map
4. Click any marker to see work order details
5. Click "View Details" in popup to see full information

### For Developers

#### Adding Leaflet to a View
```html
<!-- Map container -->
<div id="myMap" style="height: 500px;"></div>

<!-- Initialize map -->
<script>
    const map = L.map('myMap').setView([25.276987, 55.296249], 11);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add a marker
    L.marker([25.276987, 55.296249])
        .addTo(map)
        .bindPopup('My Location');
</script>
```

---

## Alternative Tile Providers (All Free)

### 1. OpenStreetMap (Current - Default)
```javascript
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);
```
- **Style:** Standard street map
- **Usage:** Unlimited
- **Performance:** Good

### 2. CartoDB Positron (Light Theme)
```javascript
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap © CartoDB'
}).addTo(map);
```
- **Style:** Minimal, light gray
- **Usage:** Unlimited
- **Best For:** Clean, modern look

### 3. CartoDB Dark Matter (Dark Theme)
```javascript
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap © CartoDB'
}).addTo(map);
```
- **Style:** Dark theme
- **Usage:** Unlimited
- **Best For:** Night mode, modern apps

### 4. Stamen Terrain (Topographic)
```javascript
L.tileLayer('https://stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg', {
    attribution: 'Map tiles by Stamen Design'
}).addTo(map);
```
- **Style:** Terrain with elevation
- **Usage:** Unlimited
- **Best For:** Outdoor, field work

### 5. Esri WorldImagery (Satellite)
```javascript
L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles © Esri'
}).addTo(map);
```
- **Style:** Satellite imagery
- **Usage:** Free for non-commercial
- **Best For:** Aerial views

---

## Common Map Operations

### 1. Add a Marker
```javascript
const marker = L.marker([lat, lng]).addTo(map);
marker.bindPopup('Hello World!');
```

### 2. Add Multiple Markers
```javascript
const locations = [
    {lat: 25.276987, lng: 55.296249, name: 'Work Order #1'},
    {lat: 25.286987, lng: 55.306249, name: 'Work Order #2'}
];

locations.forEach(loc => {
    L.marker([loc.lat, loc.lng])
        .addTo(map)
        .bindPopup(loc.name);
});
```

### 3. Fit Bounds to Markers
```javascript
const bounds = locations.map(loc => [loc.lat, loc.lng]);
map.fitBounds(bounds, {padding: [50, 50]});
```

### 4. Custom Marker Icons
```javascript
const customIcon = L.icon({
    iconUrl: '/images/marker-icon.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});

L.marker([lat, lng], {icon: customIcon}).addTo(map);
```

### 5. Draw Shapes
```javascript
// Circle
L.circle([lat, lng], {
    color: 'red',
    fillColor: '#f03',
    fillOpacity: 0.5,
    radius: 500
}).addTo(map);

// Polygon
L.polygon([
    [lat1, lng1],
    [lat2, lng2],
    [lat3, lng3]
]).addTo(map);
```

### 6. Geolocation
```javascript
map.locate({setView: true, maxZoom: 16});

map.on('locationfound', function(e) {
    L.marker(e.latlng).addTo(map)
        .bindPopup('You are here');
});
```

---

## Database Schema for Locations

### Work Orders Table
```sql
CREATE TABLE work_orders (
    ...
    location_lat DECIMAL(10, 8) NULL,
    location_lng DECIMAL(11, 8) NULL,
    location_address TEXT NULL,
    ...
);
```

### Records Table
```sql
CREATE TABLE records (
    ...
    location JSONB NULL, -- {lat: 25.276987, lng: 55.296249, accuracy: 10}
    ...
);
```

---

## Best Practices

### 1. Performance
- Use marker clustering for 100+ markers
- Lazy load map tiles
- Cache tiles for offline use
- Limit map height on mobile

### 2. UX
- Always provide a fallback for no location data
- Show loading state while fetching data
- Use appropriate zoom levels (city: 11-13, building: 16-18)
- Add search/filter to find specific markers

### 3. Accessibility
- Provide alternative text views for screen readers
- Keyboard navigation support
- High contrast markers
- Clear labels and descriptions

### 4. Mobile
- Touch-friendly markers (larger hit areas)
- Responsive map height
- Disable zoom on scroll for better UX
- Optimize tile loading

---

## Advanced Features (Future)

### 1. Marker Clustering
```javascript
// Requires leaflet.markercluster plugin
const markers = L.markerClusterGroup();
locations.forEach(loc => {
    markers.addLayer(L.marker([loc.lat, loc.lng]));
});
map.addLayer(markers);
```

### 2. Route Planning
```javascript
// Requires leaflet-routing-machine plugin
L.Routing.control({
    waypoints: [
        L.latLng(25.276987, 55.296249),
        L.latLng(25.286987, 55.306249)
    ]
}).addTo(map);
```

### 3. Heat Maps
```javascript
// Requires leaflet.heat plugin
const heat = L.heatLayer([
    [lat1, lng1, intensity1],
    [lat2, lng2, intensity2]
]).addTo(map);
```

### 4. Drawing Tools
```javascript
// Requires leaflet.draw plugin
const drawnItems = new L.FeatureGroup();
map.addLayer(drawnItems);

const drawControl = new L.Control.Draw({
    edit: {featureGroup: drawnItems}
});
map.addControl(drawControl);
```

### 5. Geofencing
```javascript
const geofence = L.circle([lat, lng], {radius: 1000});

function isInside(point) {
    return geofence.getBounds().contains(point);
}
```

---

## Troubleshooting

### Map Not Showing
1. Check if Leaflet CSS is loaded
2. Verify map container has height
3. Check browser console for errors
4. Ensure latitude/longitude are valid numbers

### Markers Not Appearing
1. Verify coordinates are in correct format [lat, lng]
2. Check if coordinates are within valid range (-90 to 90, -180 to 180)
3. Use `map.fitBounds()` to ensure markers are in view
4. Check browser console for JavaScript errors

### Tiles Not Loading
1. Check internet connection
2. Verify tile URL is correct
3. Check browser network tab for failed requests
4. Try alternative tile provider
5. Check if blocked by ad blocker

### Slow Performance
1. Use marker clustering for many markers
2. Reduce tile quality/resolution
3. Limit number of visible markers
4. Use lazy loading for off-screen markers
5. Cache tiles locally

---

## Resources

### Official Documentation
- **Leaflet.js:** https://leafletjs.com/reference.html
- **OpenStreetMap:** https://www.openstreetmap.org/
- **Leaflet Tutorials:** https://leafletjs.com/examples.html

### Plugins
- **Marker Cluster:** https://github.com/Leaflet/Leaflet.markercluster
- **Routing Machine:** https://www.liedman.net/leaflet-routing-machine/
- **Heat Map:** https://github.com/Leaflet/Leaflet.heat
- **Drawing Tools:** https://github.com/Leaflet/Leaflet.draw
- **Fullscreen:** https://github.com/brunob/leaflet.fullscreen

### Tile Providers
- **Leaflet Provider Preview:** https://leaflet-extras.github.io/leaflet-providers/preview/

### Support
- **Stack Overflow:** [leaflet] tag
- **GitHub Issues:** https://github.com/Leaflet/Leaflet/issues
- **Community Forum:** https://gis.stackexchange.com/

---

## Example: Complete Work Order Map

```html
<!-- View: work-orders/map.blade.php -->
<div id="workOrdersMap" style="height: 600px;"></div>

<script>
// Initialize map
const map = L.map('workOrdersMap').setView([25.276987, 55.296249], 11);

// Add tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Status colors
const statusColors = {
    0: '#9ca3af', // Draft - Gray
    1: '#3b82f6', // Assigned - Blue
    2: '#f59e0b', // In Progress - Yellow
    3: '#10b981'  // Completed - Green
};

// Add work order markers
const workOrders = @json($workOrders);
const bounds = [];

workOrders.forEach(wo => {
    if (wo.location_lat && wo.location_lng) {
        const marker = L.circleMarker([wo.location_lat, wo.location_lng], {
            radius: 10,
            fillColor: statusColors[wo.status],
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map);
        
        // Popup content
        marker.bindPopup(`
            <div style="min-width: 200px;">
                <h3 style="font-weight: bold; margin-bottom: 8px;">
                    Work Order #${wo.id}
                </h3>
                <p><strong>Project:</strong> ${wo.project?.name || 'N/A'}</p>
                <p><strong>Assigned To:</strong> ${wo.assigned_user?.name || 'Unassigned'}</p>
                <p><strong>Due:</strong> ${wo.due_date || 'N/A'}</p>
                <a href="/tenant/work-orders/${wo.id}" 
                   style="color: #3b82f6; text-decoration: underline;">
                    View Details →
                </a>
            </div>
        `);
        
        bounds.push([wo.location_lat, wo.location_lng]);
    }
});

// Fit map to show all markers
if (bounds.length > 0) {
    map.fitBounds(bounds, {padding: [50, 50]});
}

// Add legend
const legend = L.control({position: 'bottomright'});
legend.onAdd = function() {
    const div = L.DomUtil.create('div', 'info legend');
    div.innerHTML = `
        <div style="background: white; padding: 10px; border-radius: 5px;">
            <h4 style="margin: 0 0 10px;">Status</h4>
            <div><span style="background: ${statusColors[0]}; width: 20px; height: 20px; display: inline-block; margin-right: 5px;"></span> Draft</div>
            <div><span style="background: ${statusColors[1]}; width: 20px; height: 20px; display: inline-block; margin-right: 5px;"></span> Assigned</div>
            <div><span style="background: ${statusColors[2]}; width: 20px; height: 20px; display: inline-block; margin-right: 5px;"></span> In Progress</div>
            <div><span style="background: ${statusColors[3]}; width: 20px; height: 20px; display: inline-block; margin-right: 5px;"></span> Completed</div>
        </div>
    `;
    return div;
};
legend.addTo(map);
</script>
```

---

## Conclusion

Leaflet.js + OpenStreetMap provides a robust, free, and feature-rich mapping solution for Smart Kodes. No API keys, no limits, no costs—perfect for a SaaS application with unlimited scalability.

**Last Updated:** October 9, 2025
