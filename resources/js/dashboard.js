// CID-AMS Dashboard Enhanced Functionality
class DashboardManager {
    constructor() {
        this.currentLocation = null;
        this.allowedAreas = [
            { name: 'Main Office', lat: 14.5995, lng: 120.9842, radius: 100 }, // Manila coordinates
            { name: 'Remote Work Zone', lat: 14.6091, lng: 121.0223, radius: 200 }
        ];
        this.map = null;
        this.realtimeMap = null;
        this.employees = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadEmployeeData();
        this.startLocationTracking();
        this.initializeMaps();
        this.startRealtimeUpdates();
    }

    bindEvents() {
        // Check-in button
        const checkinBtn = document.getElementById('checkin-btn');
        if (checkinBtn) {
            checkinBtn.addEventListener('click', () => this.performCheckin());
        }

        // Map filter
        const mapFilter = document.getElementById('map-filter');
        if (mapFilter) {
            mapFilter.addEventListener('change', (e) => this.filterEmployees(e.target.value));
        }

        // Search functionality
        const searchInput = document.querySelector('input[placeholder="Search employees..."]');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.searchEmployees(e.target.value));
        }
    }

    startLocationTracking() {
        if (!navigator.geolocation) {
            this.updateLocationStatus('error', null, 'Geolocation not supported');
            return;
        }

        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutes
        };

        navigator.geolocation.getCurrentPosition(
            (position) => this.handleLocationSuccess(position),
            (error) => this.handleLocationError(error),
            options
        );

        // Watch position for real-time updates
        navigator.geolocation.watchPosition(
            (position) => this.updateCurrentLocation(position),
            (error) => console.warn('Location watch error:', error),
            options
        );
    }

    handleLocationSuccess(position) {
        this.currentLocation = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
            accuracy: position.coords.accuracy
        };

        this.updateLocationStatus('success', position);
        this.checkGeofence();
        this.updateMaps();
    }

    handleLocationError(error) {
        let message = 'Unknown error';
        switch(error.code) {
            case error.PERMISSION_DENIED:
                message = 'Location access denied by user';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Location information unavailable';
                break;
            case error.TIMEOUT:
                message = 'Location request timed out';
                break;
        }
        this.updateLocationStatus('error', null, message);
    }

    updateLocationStatus(status, position, message = '') {
        const badge = document.getElementById('location-badge');
        const location = document.getElementById('current-location');

        if (status === 'success' && position) {
            badge.className = 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium';
            badge.textContent = 'Location Active';
            location.innerHTML = `
                <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                Lat: ${position.coords.latitude.toFixed(4)}, 
                Lng: ${position.coords.longitude.toFixed(4)} 
                <span class="text-xs text-gray-500">(±${Math.round(position.coords.accuracy)}m)</span>
            `;
        } else {
            badge.className = 'px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium';
            badge.textContent = 'Location Error';
            location.innerHTML = `
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                ${message}
            `;
        }
    }

    checkGeofence() {
        if (!this.currentLocation) return;

        let inAllowedArea = false;
        const checkinBtn = document.getElementById('checkin-btn');

        this.allowedAreas.forEach((area, index) => {
            const distance = this.calculateDistance(
                this.currentLocation.lat, this.currentLocation.lng,
                area.lat, area.lng
            );

            const distanceElement = document.getElementById(
                index === 0 ? 'office-distance' : 'remote-distance'
            );
            
            if (distanceElement) {
                distanceElement.textContent = `${Math.round(distance)}m`;
                
                if (distance <= area.radius) {
                    distanceElement.classList.add('text-green-600');
                    distanceElement.classList.remove('text-blue-600', 'text-red-600');
                    inAllowedArea = true;
                } else if (distance <= area.radius * 1.5) {
                    distanceElement.classList.add('text-yellow-600');
                    distanceElement.classList.remove('text-blue-600', 'text-green-600');
                } else {
                    distanceElement.classList.add('text-red-600');
                    distanceElement.classList.remove('text-blue-600', 'text-green-600');
                }
            }
        });

        // Update check-in button
        if (checkinBtn) {
            if (inAllowedArea) {
                checkinBtn.disabled = false;
                checkinBtn.className = 'w-full py-4 bg-green-600 text-white rounded-lg font-semibold text-lg hover:bg-green-700 transition-colors duration-200';
                checkinBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Check In Now';
            } else {
                checkinBtn.disabled = true;
                checkinBtn.className = 'w-full py-4 bg-red-400 text-white rounded-lg font-semibold text-lg cursor-not-allowed';
                checkinBtn.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Outside Allowed Area';
            }
        }
    }

    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = this.deg2rad(lat2 - lat1);
        const dLng = this.deg2rad(lng2 - lng1);
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) * 
            Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    deg2rad(deg) {
        return deg * (Math.PI/180);
    }

    performCheckin() {
        if (!this.currentLocation) {
            alert('Location not available. Please enable location services.');
            return;
        }

        // Show loading state
        const checkinBtn = document.getElementById('checkin-btn');
        if (checkinBtn) {
            checkinBtn.disabled = true;
            checkinBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }

        // Simulate API call
        setTimeout(() => {
            // Update UI to show successful check-in
            this.showCheckinSuccess();
        }, 2000);
    }

    showCheckinSuccess() {
        const checkinBtn = document.getElementById('checkin-btn');
        if (checkinBtn) {
            checkinBtn.className = 'w-full py-4 bg-blue-600 text-white rounded-lg font-semibold text-lg';
            checkinBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Checked In Successfully';
        }

        // Show success notification
        this.showNotification('Check-in successful!', 'success');

        // Update today's activity (you could add this to the UI)
        console.log('Check-in recorded at:', new Date().toLocaleTimeString());
    }

    initializeMaps() {
        this.initializeCheckinMap();
        this.initializeRealtimeMap();
    }

    initializeCheckinMap() {
        const mapElement = document.getElementById('checkin-map');
        if (!mapElement) return;

        // Initialize Leaflet map
        this.map = L.map('checkin-map').setView([14.5995, 120.9842], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Add geofence circles
        this.allowedAreas.forEach(area => {
            L.circle([area.lat, area.lng], {
                color: 'green',
                fillColor: 'lightgreen',
                fillOpacity: 0.2,
                radius: area.radius
            }).addTo(this.map).bindPopup(area.name);
        });

        // Add user location when available
        this.updateMapLocation();
    }

    initializeRealtimeMap() {
        const mapElement = document.getElementById('realtime-map');
        if (!mapElement) return;

        this.realtimeMap = L.map('realtime-map').setView([14.5995, 120.9842], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.realtimeMap);

        // Add employee markers
        this.addEmployeeMarkers();
    }

    updateMapLocation() {
        if (!this.map || !this.currentLocation) return;

        // Remove existing user marker
        if (this.userMarker) {
            this.map.removeLayer(this.userMarker);
        }

        // Add new user marker
        this.userMarker = L.marker([this.currentLocation.lat, this.currentLocation.lng], {
            icon: L.divIcon({
                className: 'user-location-marker',
                html: '<div style="background: #3b82f6; border: 3px solid white; border-radius: 50%; width: 20px; height: 20px;"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        }).addTo(this.map).bindPopup('Your Location');

        // Center map on user location
        this.map.setView([this.currentLocation.lat, this.currentLocation.lng], 16);
    }

    loadEmployeeData() {
        // Mock employee data - replace with actual API call
        this.employees = [
            { id: 'john-doe', name: 'John Doe', status: 'online', lat: 14.5995, lng: 120.9842, location: 'Main Office' },
            { id: 'jane-smith', name: 'Jane Smith', status: 'field', lat: 14.6091, lng: 121.0223, location: 'Client Site A' },
            { id: 'mike-brown', name: 'Mike Brown', status: 'break', lat: 14.5995, lng: 120.9842, location: 'Main Office' },
            { id: 'sarah-adams', name: 'Sarah Adams', status: 'offline', lat: 14.5887, lng: 120.9777, location: 'Last known' }
        ];
    }

    addEmployeeMarkers() {
        if (!this.realtimeMap) return;

        this.employees.forEach(employee => {
            const color = this.getStatusColor(employee.status);
            
            const marker = L.marker([employee.lat, employee.lng], {
                icon: L.divIcon({
                    className: 'employee-marker',
                    html: `<div style="background: ${color}; border: 2px solid white; border-radius: 50%; width: 16px; height: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                })
            }).addTo(this.realtimeMap);

            marker.bindPopup(`
                <div class="p-2">
                    <h4 class="font-semibold">${employee.name}</h4>
                    <p class="text-sm text-gray-600">${employee.location}</p>
                    <p class="text-xs text-gray-500">Status: ${employee.status}</p>
                </div>
            `);
        });
    }

    getStatusColor(status) {
        const colors = {
            'online': '#10b981',
            'field': '#3b82f6', 
            'break': '#f59e0b',
            'offline': '#ef4444'
        };
        return colors[status] || '#6b7280';
    }

    filterEmployees(filter) {
        console.log('Filtering employees by:', filter);
        // Implement employee filtering logic
    }

    searchEmployees(query) {
        console.log('Searching employees:', query);
        // Implement employee search logic
    }

    startRealtimeUpdates() {
        // Update employee positions every 30 seconds
        setInterval(() => {
            this.updateRealtimeData();
        }, 30000);
    }

    updateRealtimeData() {
        // Simulate real-time updates
        console.log('Updating realtime data...');
        
        // Update last update time
        const lastUpdateElement = document.getElementById('last-update');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = new Date().toLocaleTimeString();
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        } text-white`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Global functions for backward compatibility
window.refreshMap = function() {
    if (window.dashboardManager) {
        window.dashboardManager.updateRealtimeData();
    }
};

window.focusOnEmployee = function(employeeId) {
    console.log('Focusing on employee:', employeeId);
    // Find employee and center map on them
    if (window.dashboardManager && window.dashboardManager.realtimeMap) {
        const employee = window.dashboardManager.employees.find(e => e.id === employeeId);
        if (employee) {
            window.dashboardManager.realtimeMap.setView([employee.lat, employee.lng], 16);
        }
    }
};

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardManager = new DashboardManager();
});