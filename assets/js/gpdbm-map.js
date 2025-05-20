/**
 * GPD Business Maps - Leaflet Map Implementation
 *
 * @package GPD_Business_Maps
 * @version 1.0.0
 * @date 2025-05-20
 */

(function($) {
    'use strict';
    
    // Maps instances
    var gpdbmMaps = {};
    
    /**
     * Initialize a map
     * 
     * @param {string} mapId - The map container ID
     * @param {object} options - Map options
     */
    window.gpdbmInitMap = function(mapId, options) {
        var mapContainer = document.getElementById(mapId);
        
        if (!mapContainer) {
            console.error('Map container not found:', mapId);
            return;
        }
        
        // Set default options
        options = options || {};
        var center = options.center || { lat: 40.7128, lng: -74.0060 }; // Default to New York
        var zoom = options.zoom || 13;
        var clustering = options.clustering !== undefined ? options.clustering : true;
        
        // Initialize the map
        var map = L.map(mapId).setView([center.lat, center.lng], zoom);
        
        // Add the OpenStreetMap tiles
        L.tileLayer(gpdbmVars.map_tile_url, {
            attribution: gpdbmVars.map_attribution,
            maxZoom: 19
        }).addTo(map);
        
        // Store map instance
        gpdbmMaps[mapId] = map;
        
        // Add markers for businesses
        if (options.businesses && options.businesses.length > 0) {
            addBusinessMarkers(map, options.businesses, clustering);
        }
        
        // Fix rendering issues
        setTimeout(function() {
            map.invalidateSize();
        }, 100);
        
        return map;
    };
    
    /**
     * Add business markers to the map
     * 
     * @param {L.Map} map - Leaflet map instance
     * @param {Array} businesses - Array of business objects
     * @param {boolean} clustering - Whether to use marker clustering
     */
    function addBusinessMarkers(map, businesses, clustering) {
        // Create marker group
        var markers;
        
        if (clustering && typeof L.markerClusterGroup === 'function') {
            markers = L.markerClusterGroup();
        } else {
            markers = L.featureGroup();
        }
        
        // Add markers for each business
        businesses.forEach(function(business) {
            if (!business.lat || !business.lng) {
                return;
            }
            
            // Create marker
            var marker = L.marker([business.lat, business.lng]);
            
            // Create popup content
            var popupContent = '<div class="gpdbm-popup">';
            popupContent += '<h4>' + business.title + '</h4>';
            
            if (business.address) {
                popupContent += '<p class="gpdbm-address">' + business.address + '</p>';
            }
            
            popupContent += '<div class="gpdbm-popup-buttons">';
            
            // Add link to view business details
            popupContent += '<a href="' + business.permalink + '" class="gpdbm-view-business">' + 
                            'View Details</a>';
            
            // Add link to Google Maps if available
            if (business.maps_uri) {
                popupContent += '<a href="' + business.maps_uri + '" target="_blank" ' +
                                'class="gpdbm-directions">Open in Google Maps</a>';
            } else {
                // Fallback to OpenStreetMap directions
                popupContent += '<a href="https://www.openstreetmap.org/directions?from=&to=' + 
                                business.lat + '%2C' + business.lng + '" target="_blank" ' +
                                'class="gpdbm-directions">Get Directions</a>';
            }
            
            popupContent += '</div>';
            
            // Add thumbnail if available
            if (business.thumbnail) {
                popupContent += '<div class="gpdbm-popup-thumbnail">' +
                                '<img src="' + business.thumbnail + '" alt="' + business.title + '">' +
                                '</div>';
            }
            
            popupContent += '</div>';
            
            // Add popup to marker
            marker.bindPopup(popupContent);
            
            // Add marker to marker group
            markers.addLayer(marker);
        });
        
        // Add markers to map
        map.addLayer(markers);
        
        // If more than one business, fit the map to show all markers
        if (businesses.length > 1) {
            var bounds = markers.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds);
            }
        }
        
        return markers;
    }
    
    // Make maps responsive
    $(window).on('resize', function() {
        // Refresh all maps when window is resized
        $.each(gpdbmMaps, function(id, map) {
            map.invalidateSize();
        });
    });
    
})(jQuery);
