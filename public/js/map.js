/**
 * map.js
 *
 * OMG - OpenMapGenerator - Javascript library
 *
 * Version 1.2 novembre 2022
 *
 * Manage Map generation using Leaflet, user and server actions
 *
 * Copyright © 2022, philippe@croue.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

"use strict";

/**
 * lang - Javascript messages translated in user language (see OMG.initConstants)
 */
var lang = {};

/**
 * log - Show message in navigator console if log enabled, first parameter in info mode ⓘ, others in log mode
 *
 * @param {object(s)} Using arguments for variable number of parameters
 */
function log() {

    if(enableLog) {

        for (var i = 0; i < arguments.length; i++) {

            if(i === 0) {

                console.info(arguments[i]);

            } else {

                console.log(arguments[i]);
            }
        }
    }
}
/**
 * String.replaceSubstring - Added to String prototype - Replace all occurrences of before by after in string
 *
 * @param String before
 * @param String after
 *
 * @return String replace result
 */
String.prototype.replaceSubstring = function(before, after) {

    const array = this.split(before);
    return array.join(after);
}

/**
 * decodeEntities - Added to String prototype - Replace HTML entities with chars in string
 *
 * @param String strval
 *
 * @return String with decoded chars
 */
String.prototype.decodeEntities = function() {

    var txtobj = document.createElement('textarea');
    txtobj.innerHTML = this;
    return txtobj.value;
}

/**
 * String.removeCR - Added to String prototype - Remove Cariage Return from String.
 *
 * Replace:
 *
 *   \r\n by <space>
 *   \n\r by <space>
 *   \r by <space>
 *   \n by <space>
 *
 * @return String with CR replaced by space
 */
String.prototype.removeCR = function() {

    return this.replaceSubstring('\r\n', ' ')
               .replaceSubstring('\n\r', ' ')
               .replaceSubstring('\n', ' ')
               .replaceSubstring('\r', ' ');
}

/**
 * String.jsonFilter - Added to String prototype - Replace some chars in string:
 *
 *   " by ''
 *   ' by \'
 *
 * @return String with replace result
 */
String.prototype.jsonFilter = function() {

    return this.replaceSubstring('"', "''")
               .replaceSubstring("'", "\'");
}

/**
 * o - Get first DOM object matches with def
 *
 * @param String ref, object reference (#id, .className, tagName)
 *
 * @return DOM object
 */
function o(ref) {

    return document.querySelector(ref);
}

/**
 * OMGlocation class - OMG marker definition
 */
class OMGlocation {

    /**
     * Initialize properties and set if datas (def) given
     *
     * @param {string} def, default object definition parameters.
     *   def must contain properties:
     *     code, map, lat, lng, name, description, icon, link
     */
    constructor(def) {

        log('OMGlocation.constructor(def)', def);

        this.code = 0;
        this.map = 0;
        this.lat = 0.0;
        this.lng = 0.0;
        this.name = '';
        this.description = '';
        this.icon = '';
        this.link = '';

        if(typeof def !== 'undefined') {

            if(def.hasOwnProperty('code')) {

                this.setCode(def.code);
            }

            if(def.hasOwnProperty('map')) {

                this.setMap(def.map);
            }

            if(def.hasOwnProperty('lat')) {

                this.setLat(def.lat);
            }

            if(def.hasOwnProperty('lng')) {

                this.setLng(def.lng);
            }

            if(def.hasOwnProperty('name')) {

                this.setName(def.name);
            }

            if(def.hasOwnProperty('description')) {

                this.setDescription(def.description);
            }

            if(def.hasOwnProperty('icon')) {

                this.setIcon(def.icon);
            }

            if(def.hasOwnProperty('link')) {

                this.setLink(def.link);
            }
        }
    }

    /**
     * Convert value to integer
     *
     * @param {variant} value, value to convert
     *
     * @return integer, value converted
     */
    toInt(value) {

        return parseInt(value);
    }

    /**
     * Convert value to float
     *
     * @param {variant} value, value to convert
     *
     * @return float, value converted
     */
    toFloat(value) {

        return parseFloat(value).toFixed(5);
    }

    /**
     * Convert value to string
     *
     * @param {variant} value, value to convert
     *
     * @return string, value converted
     */
    toString(value) {

        return value.toString().trim().removeCR().decodeEntities();
    }

    /**
     * Set code property
     *
     * @param {variant} value, value to set to code
     */
    setCode(value) {

        this.code = this.toInt(value);
    }

    /**
     * Set map property
     *
     * @param {variant} value, value to set to map
     */
    setMap(value) {

        this.map = this.toInt(value);
    }

    /**
     * Set lat property
     *
     * @param {variant} value, value to set to lat
     */
    setLat(value) {

        this.lat = this.toFloat(value);
    }

    /**
     * Set lng property
     *
     * @param {variant} value, value to set to lng
     */
    setLng(value) {

        this.lng = this.toFloat(value);
    }

    /**
     * Set name property
     *
     * @param {variant} value, value to set to name
     */
    setName(value) {

        this.name = this.toString(value);
    }

    /**
     * Set description property
     *
     * @param {variant} value, value to set to description
     */
    setDescription(value) {

        this.description = this.toString(value);
    }

    /**
     * Set icon property
     *
     * @param {variant} value, value to set to icon
     */
    setIcon(value) {

        this.icon = this.toString(value);
    }

    /**
     * Set link property
     *
     * @param {variant} value, value to set to link
     */
    setLink(value) {

        this.link = this.toString(value);
    }
}

/**
 * OMG - Main OpenMapGenerator object - Use Leaflet API to manage OMG maps and markers.
 *
 * @property {object} map, map generated by Leaflet
 * @property {object} currentOSMmarker, current selected marker in map
 * @property {integer} iconWidth, Icon width on map
 * @property {integer} iconHeight, Icon height on map
 * @property {integer} defaultIcon, Icon used by default
 * @property {boolean} editable, Map is editable or not
 * @property {string} action, define action managed by map click
 * @property {array of {OMGlocation}} locations, List of map markers
 * @property {integer} notifBarSeconds, Seconds to show notification bar
 */
var OMG = {

    map: null,
    maxZoom: 19,
    currentOSMmarker: null,
    popupMaxWidth: 240,
    iconWidth: 32,
    iconHeight: 32,
    iconPath: iconsDir + '/',
    defaultIcon: defaultIcon,
    editable: false,
    action: '',
    locations: null,
    notifBarSeconds: 4,

    /**
     * showNotificationBar - Show system message
     *
     * @param {string} message, message to show
     * @param {string} messageClass, message CSS class, default notificationBarInfo
     */
    showNotificationBar: function(message, messageClass = 'notificationBarInfo') {

        log('OMG.showNotificationBar(message, messageClass)', message, messageClass);

        var div = o('#notificationBar');
        div.innerHTML = message;
        div.className = messageClass + ' notificationBarAnimation';

        var i = setInterval(function() {

            div.className = '';
            clearInterval(i);
        }, this.notifBarSeconds * 1000);
    },

    /**
     * init - Initialize environment - Map initialized in OMGresponseInit function
     */
    init: function() {

        log('OMG.init()');

        // Get Javascript messages

        this.post({
            'action': 'getMessages',
            'dat': {'lang': locale},
            'response': OMGresponseInit,
        });
    },

    /**
     * initMap - Initialize map ugins DOM DIV object #mapDiv
     *           with global constant map sent by application
     */
    initMap: function() {

        log('OMG.initMap()');

        this.map = L.map('mapDiv').setView([map.latitude, map.longitude], map.zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: this.maxZoom,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        if(map.edit) {

            this.editable = true;
            this.map.on('click', OMGonMapClick);
        }
    },

    /**
     * addMarkers
     *
     * Add markers recieved from server by page generation in map
     *
     * @param {array of {object}} locations, markers to add to map
     */
    addMarkers: function(locations) {

        log('OMG.addMarkers(locations)', locations);

        this.locations = [];

        locations.forEach(

            (location) => {

                var newLocation = new OMGlocation(location);
                this.locations.push(newLocation);
                this.addMarker(newLocation);
            }
        );
    },

    /**
     * addMarker
     *
     * Add marker to map
     *
     * @param {{OMGlocation}} location, marker to add in map
     */
    addMarker: function(location) {

        log('OMG.addMarker(location)', location);

        var icon = L.icon({

            iconUrl: this.iconPath + location.icon,
            iconSize: [this.iconWidth, this.iconHeight]
        });

        var marker = L.marker(

            [location.lat, location.lng],
            {icon: icon}

        ).addTo(this.map);

        marker.bindPopup(
            this.generatePopup(location),
            { maxWidth : this.popupMaxWidth }
        );

        marker.on('click', OMGonMarkerClick);
    },


    /**
     * refreshMarker - Add, replace or delete marker in map
     *
     * @param {{object}} oldOSM, OpenStreetMap marker to remove or update
     * @param {{object}} newOMG, OpenMapGenerator marker to add or update
     */
    refreshMarker: function(oldOSM, newOMG) {

        log('OMG.refreshMarker(oldOSM, newOMG)', oldOSM, newOMG);

        if(oldOSM !== null) {

            // Remove old marker from list

            var key = this.searchMarker(this.getLocDef(oldOSM), 'code', 'key');

            if(key !== false) {

                this.locations.splice(key, 1);
            }

            // Remove old marker from map

            this.map.removeLayer(oldOSM);
        }

        if(newOMG !== null) {

            // Add new marker in list

            this.locations.push(newOMG);

            // Add new marker in map

            this.addMarker(newOMG);
        }
    },

    /**
     * generatePopup
     *
     * Generate marker popup with infos (name, description, link...)
     *
     * @param {{OMGlocation}} location, marker informations
     *
     * @return {string} generated HTML popup code
     */
    generatePopup: function(location) {

        log('OMG.generatePopup(location)', location);

        var out = '<popup>';
        out += '<p class="popupTitle">' + location.name + '</p>';

        if(location['description'] !== '') {

            out += '<p class="popupDesc">' + location.description + '</p>';
        }

        if(location['link'] !== '') {

            out += '<p><a href="' + location.link + '" target="_blank">' + lang.generatePopupMoreInfos + '</a></p>';
        }

        out += '<table class="tableGoWith"><tr>';
        out += '<td class="center"><a href="https://www.openstreetmap.org/#map=19/' + location.lat + '/' + location.lng + '" target="_blank" title="' + lang.generatePopupWith + ' OpenStreetMap"><img src="/images/openstreetmap.png" /></a></td>';
        out += '<td class="center"><a href="com.sygic.aura://coordinate|' + location.lng + '|' + location.lat + '|show" target="_blank" title="' + lang.generatePopupWith + ' Sygic"><img src="/images/sygic.png" /></a></td>';
        out += '<td class="center"><a href="https://www.waze.com/ul?ll=' + location.lat + '%2C' + location.lng + '&navigate=yes" target="_blank" title="' + lang.generatePopupWith + ' Waze"><img src="/images/waze.png" /></a></td>';
        out += '<td class="center"><a href="http://maps.google.com/?q=' + location.lat + '%2C' + location.lng + '" target="_blank" title="' + lang.generatePopupWith + ' Google maps"><img src="/images/googlemaps.png" /></a></td>';
        out += '</tr></table>';

        if(this.editable) {

            out += '<locDef>{' +
                '"code": "' + location.code + '"' +
            '}</locDef>';
        }

        out += '</popup>';

        return out;
    },

    /**
     * getLocDef
     *
     * Read marker information from its popup (code in <locDef>),
     * search marker code in OMG.locations and return marker found
     *
     * @param {object} location, OSM marker object to get informations
     *
     * @return {OMGlocation} marker object
     */
    getLocDef: function(location) {

        log('OMG.getLocDef(location)', location);

        var content = location.getPopup().getContent();
        var start = content.indexOf('<locDef>') + 1;
        var end   = content.indexOf('</locDef>', start);

        return this.searchMarker(

            new OMGlocation(

                JSON.parse(content.substring(start + 7, end))
            ),
            'code'
        );
    },

    /**
     * post
     *
     * Ajax post
     *
     * @param {object} datas, contains:
     *
     *  {string} action     : URL to call (/ajax/action/#)
     *  {integer} key       : object primary key to manage (# in URL)
     *  {array} dat         : datas to send
     *  {function} response : Ajax callback function with 2 parameters (xhr, datas)
     *  {various}           : additional parameters to use by callback function
     */
    post: function(datas) {

        log('OMG.post(datas)', datas);

        var data = new FormData();

        if(typeof datas.dat !== 'undefined') {

            Object.entries(datas.dat).forEach(

                ([key, value]) => {

                    if(typeof value === 'string') {

                        value = String(value).jsonFilter().removeCR();
                    }

                    data.append(key, value);
                }
            );
        }

        var xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {

            datas.response(xhr, datas);
        };

        var url = window.location.origin + '/ajax/' + datas.action;

        if(typeof datas.key !== 'undefined' && datas.key !== null) {

            url += '/' + datas.key;
        }

        xhr.open('POST', url, true); // Always asynchrone
        xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");
        xhr.responseType = 'json';
        xhr.send(data);
    },

    /* Manage map methods */

    /**
     * getMapInputs
     *
     * Get map values set by user in marker form inputs
     *
     * @return {object} values set.
     *
     *   Structure of returned object is:
     *     {
     *       id: {integer},
     *       name: {string},
     *       description: {string},
     *       private: {integer} [0-1],
     *       password: {string},
     *     }
     */
    getMapInputs: function() {

        log('OMG.getMapInputs()');

        return {
            id: parseInt(map.code),
            name: o('#mapName').value.trim(),
            description: o('#mapDescription').value.trim().removeCR(),
            private: (o('#mapPrivate').checked ? 1 : 0),
            password: o('#mapPassword').value.trim(),
        };
    },

    /**
     * updateMap
     *
     * Update map event from Update button click
     * Send updated map datas to server with Ajax
     *
     * Ajax action: mapUpdate
     */
    updateMap: function() {

        log('OMG.updateMap()');

        const map = this.getMapInputs();

        if(map.name.trim() === '') {

            alert(lang.updateMapEmptyName);

        } else {

            this.post(
                {
                    action: 'mapUpdate',
                    key: map.id,
                    dat: map,
                    response: OMGresponseUpdate,
                }
            );
        }
    },

    /**
     * setMapCenterZoom
     *
     * Update map event from Update button click
     * Send map default position and zoom to server with Ajax
     *
     * Ajax action: mapCenterUpdate
     */
    setMapCenterZoom: function() {

        log('OMG.setMapCenterZoom()');

        var mapLocation = {

            'id': parseInt(map.code),
            'lat': parseFloat(this.map.getCenter().lat).toFixed(5),
            'lng': parseFloat(this.map.getCenter().lng).toFixed(5),
            'zoom': parseInt(this.map.getZoom())
        };

        this.post(
            {
                action: 'mapCenterUpdate',
                key: mapLocation.id,
                dat: mapLocation,
                response: OMGresponseUpdateCenter,
            }
        );
    },

    /**
     * resetLocationInputs
     *
     * Reset marker form inputs
     */
    resetLocationInputs: function() {

        log('OMG.resetLocationInputs()');

        o('#code').value = '';
        o('#lat').value = '';
        o('#lng').value = '';
        o('#name').value = '';
        o('#description').value = '';
        o('#icon').value = defaultIcon;
        o('#link').value = '';

        o('#code').defaultValue = '';
        o('#lat').defaultValue = '';
        o('#lng').defaultValue = '';
        o('#name').defaultValue = '';
        o('#description').defaultValue = '';
        o('#icon').defaultValue = defaultIcon;
        o('#link').defaultValue = '';
    },

    /**
     * getLocationInputs
     *
     * Read values set by user for marker
     *
     * @return {object} marker object
     */
    getLocationInputs: function() {

        log('OMG.getLocationInputs()');

        return new OMGlocation(
            {
                'map': map.code,
                'code': o('#code').value,
                'lat': o('#lat').value,
                'lng': o('#lng').value,
                'name': o('#name').value,
                'description': o('#description').value,
                'icon': o('#icon').value,
                'link': o('#link').value,
            }
        );
    },

    /**
     * setLocationInputs
     *
     * Set marker form inputs with marker informations
     * before user updates
     *
     * @param {OMGlocation} location, marker informations
     */
    setLocationInputs: function(location) {

        log('OMG.setLocationInputs(location)', location);

        o('#code').value = location.code;
        o('#lat').value = location.lat;
        o('#lng').value = location.lng;
        o('#name').value = location.name;
        o('#description').value = location.description;
        o('#icon').value = location.icon;
        o('#link').value = location.link;
    },

    /**
     * setFormButtons
     *
     * Show or hide HTML buttons depends of action asked
     *
     * @param {string} action, action for showing buttons.
     *   Possible values are:
     *   - create to show Create and hide Update, Delete, Move
     *   - update to show Update, Delete, Move and hide Create
     */
    setFormButtons: function(action) {

        log('OMG.setFormButtons(action)', action);

        if(action === 'create') {

            o('#buttonLocCreate').style.display = '';
            o('#buttonLocUpdate').style.display = 'none';
            o('#buttonLocDelete').style.display = 'none';
            o('#buttonLocLocate').style.display = 'none';

        } else if(action === 'update') {

            o('#buttonLocCreate').style.display = 'none';
            o('#buttonLocUpdate').style.display = '';
            o('#buttonLocDelete').style.display = '';
            o('#buttonLocLocate').style.display = '';
        }
    },

    /**
     * showMarkerWindow
     *
     * Show marker form popup window for user updates
     *
     */
    showMarkerWindow: function() {

        log('OMG.showMarkerWindow()');

        o('#markerWindow').style.display = 'inline-block';
    },

    /**
     * hideMarkerWindow
     *
     * Hide marker form popup window after user action
     *
     * @param {boolean} withReset, reset marker form inputs or not
     *
     */
    hideMarkerWindow: function(withReset = false) {

        log('OMG.hideMarkerWindow(withReset)', withReset);

        if(withReset) {

            this.resetLocationInputs();
        }

        o('#markerWindow').style.display = 'none';
    },

    /* Manage markers methods */

    /**
     * searchMarker
     *
     * Search marker in map locations
     *
     * @param {object} location, marker to search
     * @param {string} searchType, 'code' to search by code or empty to search by lat/lng
     * @param {string} returnType, 'key' to return array key or empty to return OMGlocation
     *
     * @return {object} found marker or false
     *
     */
    searchMarker: function(location, searchType = '', returnType = '') {

        log('OMG.searchMarker(location, searchType, returnType)', location, searchType, returnType);

        if('_latlng' in location) {

            // Convert Leaflet object to OMGlocation

            location = this.getLocDef(location);
        }

        var lat = location.lat;
        var lng = location.lng;
        var code = location.code;

        var found = false;

        for(var key = 0; key < this.locations.length; key++) {

            // Search by latitude and longitude

            if(
                searchType === ''
                &&
                this.locations[key].lat === lat
                &&
                this.locations[key].lng === lng
            ) {

                if(returnType === 'key') {

                    found = key;

                } else {

                    found = this.locations[key];
                }

                break;
            }

            // Search by code

            if(
                searchType === 'code'
                &&
                this.locations[key].code === code
            ) {

                if(returnType === 'key') {

                    found = key;

                } else {

                    found = this.locations[key];
                }

                break;
            }
        }

        return found;
    },

    /**
     * createMarker
     *
     * Read user marker form inputs and send
     * datas to server with Ajax for create
     *
     * Ajax command is locationCreate
     */
    createMarker: function() {

        log('OMG.createMarker()');

        var location = this.getLocationInputs();

        if(this.searchMarker(location) !== false) {

            alert(lang.createMarkerAlreadyExists);
            return;
        }

        if(location.name === '') {

            alert(lang.updateMarkerEmptyName);
            return;
        }
        this.post(
            {
                action: 'locationCreate',
                key: map.code,
                dat: location,
                response: OMGresponseCreateLocation,
                old: null,
                new: location
            }
        );
        this.resetLocationInputs();
        this.hideMarkerWindow(true);
    },

    /**
     * updateMarker
     *
     * Read user marker form inputs and send
     * datas to server with Ajax for update
     *
     * @param {boolean} withReset, reset values and hide location form, or not
     *
     * Ajax command is locationUpdate
     */
    updateMarker: function() {

        log('OMG.updateMarker()');

        if(this.currentOSMmarker === null) {

            return;
        }

        var location = this.getLocationInputs();

        if(location.code === 0) {

            alert(lang.updateMarkerError);
            return;
        }

        if(location.name === '') {

            alert(lang.updateMarkerEmptyName);
            return;
        }

        this.post(
            {
                action: 'locationUpdate',
                key: location.code,
                dat: location,
                response: OMGresponseUpdateLocation,
                old: this.currentOSMmarker,
                new: location
            }
        );

        this.resetLocationInputs();
        this.hideMarkerWindow(true);
    },

    /**
     * moveMarker
     *
     * Start moving marker action
     */
    moveMarker: function() {

        log('OMG.moveMarker()');

        this.action = 'relocate';
        this.hideMarkerWindow(false);
        this.showNotificationBar(lang.toRelocateLocation, 'notificationBarInfo');
    },

    /**
     * updateCoordinates
     *
     * Update marker coordinates after moving operation
     */
    updateCoordinates: function() {

        log('OMG.updateCoordinates()');

        this.action = '';
        this.showMarkerWindow();
        this.updateMarker();
    },

    /**
     * removeMarker
     *
     * Remove marker from map
     *
     * Ajax command is locationDelete
     */
    removeMarker: function() {

        log('OMG.removeMarker()');

        if(this.currentOSMmarker === null) {

            return;
        }

        var location = this.getLocationInputs();

        this.post(
            {
                action: 'locationDelete',
                key: location.code,
                dat: location,
                response: OMGresponseDeleteLocation,
                old: this.currentOSMmarker,
                new: null,
            }
        );
        this.resetLocationInputs();
        this.hideMarkerWindow(true);
    }
};

/* Global events */

/**
 * OMGonMapClick
 *
 * Map click event, set marker form inputs and show it to add marker
 * or update marker coordinates for a relocate action
 *
 * @param {object} e, click event
 *
 */
function OMGonMapClick(e) {

    log('OMGonMapClick(e)', e);

    if(OMG.action === '') {

        // Create new location

        OMG.resetLocationInputs();

        OMG.setLocationInputs(
            new OMGlocation(
                {
                    'lat': e.latlng.lat,
                    'lng': e.latlng.lng,
                    'icon': OMG.defaultIcon,
                }
            )
        );

        OMG.setFormButtons('create');
        OMG.showMarkerWindow();

    } else if(OMG.action === 'relocate') {

        // Update location coordinates

        var location = OMG.getLocationInputs();
        location.setLat(e.latlng.lat);
        location.setLng(e.latlng.lng);
        OMG.setLocationInputs(location);
        OMG.updateCoordinates();
    }
}

/**
 * OMGonMarkerClick
 *
 * Marker click event, set marker form inputs for user update
 *
 * @param {object} e, click event
 *
 */
function OMGonMarkerClick(e) {

    log('OMGonMarkerClick(e)', e);

    if(OMG.editable) {
        OMG.map.closePopup();
        OMG.showMarkerWindow();
        OMG.resetLocationInputs();
        OMG.currentOSMmarker = e.target;
        var location = OMG.getLocDef(OMG.currentOSMmarker);
        OMG.setLocationInputs(location);
        OMG.setFormButtons('update');
    }
}

/* Ajax server responses */

/**
 * OMGresponseUpdate
 *
 * Ajax server response after map updates
 * Ajax original action: mapUpdate
 *
 * @param {object} xhr, XML Http request result
 * @param {object} datas, datas sent by Ajax post to
 *            transmit informations to response
 */
function OMGresponseUpdate(xhr, datas) {

    if(xhr.status === 200 && xhr.response !== null) {

        log('OMGresponseUpdate(xhr, datas)', xhr, datas);

        if(xhr.response.result) {

            var className = 'notificationBarInfo';
            var message = lang.responseUpdateOK;

        } else {

            className = 'notificationBarError';
            message = lang.responseUpdateKO;

        }
        OMG.showNotificationBar(message, className);
    }
}

/**
 * OMGresponseUpdateCenter
 *
 * Ajax response after map coordinates update
 * Ajax original action: mapCenterUpdate
 *
 * @param {object} xhr, XML Http request result
 * @param {object} datas, datas sent by Ajax post to
 *            transmit informations to response
 */
function OMGresponseUpdateCenter(xhr, datas) {

    if(xhr.status === 200 && xhr.response !== null) {

        log('OMGresponseUpdateCenter(xhr, datas)', xhr, datas);

        if(xhr.response.result) {

            var className = 'notificationBarInfo';
            var message = lang.responseUpdateCenterOK;

        } else {

            className = 'notificationBarError';
            message = lang.responseUpdateCenterKO;

        }
        OMG.showNotificationBar(message, className);
    }
}

/**
 * OMGresponseCreateLocation
 *
 * Ajax response after creating a new marker
 * Ajax original action: locationCreate
 *
 * @param {object} xhr, XML Http request result
 * @param {object} datas, datas sent by Ajax post to
 *            transmit informations to response
 */
function OMGresponseCreateLocation(xhr, datas) {

    if(xhr.status === 200 && xhr.response !== null) {

        log('OMGresponseCreateLocation(xhr, datas)', xhr, datas);

        if(xhr.response.result) {

            datas.new.code = xhr.response.newId;

            OMG.refreshMarker(null, datas.new);

            var className = 'notificationBarInfo';
            var message = lang.responseCreateLocationOK;

        } else {

            className = 'notificationBarError';
            message = lang.responseCreateLocationKO;

        }
        OMG.showNotificationBar(message, className);
    }
}

/**
 * OMGresponseUpdateLocation
 *
 * Ajax response after updating marker informations
 * Ajax original action: locationUpdate
 *
 * @param {object} xhr, XML Http request result
 * @param {object} datas, datas sent by Ajax post to
 *            transmit informations to response
 */
function OMGresponseUpdateLocation(xhr, datas) {

    if(xhr.status === 200 && xhr.response !== null) {

        log('OMGresponseUpdateLocation(xhr, datas)', xhr, datas);

        if(xhr.response.result) {

            OMG.refreshMarker(datas.old, datas.new);

            var className = 'notificationBarInfo';
            var message = lang.responseUpdateLocationOK;

        } else {

            className = 'notificationBarError';
            message = lang.responseUpdateLocationKO;

        }
        OMG.showNotificationBar(message, className);
    }
}

/**
 * OMGresponseDeleteLocation
 *
 * Ajax response after deleting marker
 * Ajax original action: locationDelete
 *
 * @param {object} xhr, XML Http request result
 * @param {object} datas, datas sent by Ajax post to
 *            transmit informations to response
 */
function OMGresponseDeleteLocation(xhr, datas) {

    if(xhr.status === 200 && xhr.response !== null) {

        log('OMGresponseDeleteLocation(xhr, datas)', xhr, datas);

        if(xhr.response.result) {

            OMG.refreshMarker(datas.old, null);

            var className = 'notificationBarInfo';
            var message = lang.responseDeleteLocationOK;

        } else {

            className = 'notificationBarError';
            message = lang.responseDeleteLocationKO;

        }
        OMG.showNotificationBar(message, className);
    }
}

/**
 * OMGresponseInit - Ajax response of getMessages - Get translated messages for Javascript and initialize map
 *
 * @param {object} xhr, XML Http request result
 */
function OMGresponseInit(xhr, data) {

    if(xhr.status === 200 && xhr.response !== null) {

        log('OMGresponseInit(xhr, data)', xhr, data);

        lang = xhr.response;
        OMG.initMap();
        OMG.addMarkers(locations);
    }
}
