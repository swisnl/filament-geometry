import * as LF from 'leaflet';
import 'leaflet.fullscreen';
import 'leaflet-gesture-handling';
import '@geoman-io/leaflet-geoman-free';

export default function filamentGeometry($wire, config) {
    return {
        $wire: $wire,
        config: config,

        create: function(el) {
            // Create map
            this.map = LF.map(el, config.map)

            if (config.bounds) {
                let southWest = LF.latLng(config.bounds.sw.lat, config.bounds.sw.lng)
                let northEast = LF.latLng(config.bounds.ne.lat, config.bounds.ne.lng)
                let bounds = LF.latLngBounds(southWest, northEast)
                this.map.setMaxBounds(bounds)
                this.map.fitBounds(bounds)
                this.map.on('drag', () => {
                    this.map.panInsideBounds(bounds, { animate: false })
                })
            }

            this.tile = LF.tileLayer(config.tileLayer.url, config.tileLayer.options).addTo(this.map)

            this.drawItems = LF.geoJSON(this.getGeoJsonFeature() ?? [], {
                pointToLayer: (geoJsonPoint, latlng) => {
                    return L.marker(latlng, {
                        icon: this.createMarkerIcon(),
                    })
                },
            }).addTo(this.map)

            if (this.drawItems.getLayers().length > 0) {
                this.map.fitBounds(this.drawItems.getBounds(), {
                    maxZoom: 16,
                })
            }

            if (config.map.gestureHandling) {
                this.map.on('enterFullscreen', () => {
                    this.map.gestureHandling.disable()
                })
                this.map.on('exitFullscreen', () => {
                    this.map.gestureHandling.enable()
                })
            }

            // Init Geoman
            this.map.pm.setLang(config.locale, undefined, 'en');
            this.map.pm.addControls(config.geoman);

            this.map.pm.setGlobalOptions({
                layerGroup: this.drawItems,
                allowSelfIntersection: false,
                removeLayerBelowMinVertexCount: false,
                markerStyle: {
                    icon: this.createMarkerIcon(),
                },
            })

            this.map.pm.enableGlobalEditMode()

            this.map.on('pm:drawstart', (e) => {
                if (this.drawItems.getLayers().length === 0) {
                    return;
                }

                if (confirm(config.lang.warning.limit)) {
                    this.drawItems.clearLayers()
                } else {
                    this.map.pm.disableDraw()
                    this.map.pm.enableGlobalEditMode()
                }
            })

            this.map.on('pm:create', () => {
                this.map.pm.disableDraw()
                this.map.pm.enableGlobalEditMode()
            })

            this.drawItems.on('pm:edit layeradd layerremove', () => {
                this.updateGeoJson()
            })

            this.drawItems.on('pm:remove', () => {
                // Geoman doesn't properly remove the layer from the group, so we should clear it manually
                this.drawItems.clearLayers()
            })
        },

        createMarkerIcon() {
            return LF.divIcon(config.markerIcon);
        },

        updateGeoJson: function() {
            try {
                this.$wire.set(config.statePath, this.drawItems.getLayers()[0] ? JSON.stringify(this.drawItems.getLayers()[0].toGeoJSON().geometry) : null, true)
            } catch (error) {
                console.error('Error updating GeoJSON:', error)
            }
        },

        getGeoJsonFeature: function() {
            return JSON.parse(this.$wire.get(config.statePath))
        },

        destroy: function() {
            if (this.map) {
                this.map.remove();
                this.map = null;
            }
            this.drawItems = null;
            this.tile = null;
        },
    }
}
