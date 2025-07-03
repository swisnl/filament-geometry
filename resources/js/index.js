import * as LF from 'leaflet'
import 'leaflet.fullscreen'
import '@geoman-io/leaflet-geoman-free'

export default function filamentGeometry($wire, config) {
    return {
        $wire: $wire,
        config: config,
        createMap: function(el) {
            this.map = LF.map(el, config.controls)

            if (config.bounds) {
                let southWest = LF.latLng(config.bounds.sw.lat, config.bounds.sw.lng)
                let northEast = LF.latLng(config.bounds.ne.lat, config.bounds.ne.lng)
                let bounds = LF.latLngBounds(southWest, northEast)
                this.map.setMaxBounds(bounds)
                this.map.fitBounds(bounds)
                this.map.on('drag', function() {
                    map.panInsideBounds(bounds, { animate: false })
                })
            }

            this.tile = LF.tileLayer(config.tileLayer.url, config.tileLayer.options).addTo(this.map)

            this.map.pm.setLang(config.locale, {}, 'en');
            this.map.pm.addControls({
                customControls: false,
                cutPolygon: false,
                drawCircle: false,
                drawCircleMarker: false,
                drawControls: true,
                drawMarker: config.geoMan.drawMarker,
                drawPolygon: config.geoMan.drawPolygon,
                drawPolyline: config.geoMan.drawPolyline,
                drawRectangle: config.geoMan.drawRectangle,
                drawText: false,
                editControls: false,
                oneBlock: true,
                optionsControls: false,
                position: config.geoMan.position,
                removalMode: false,
                rotateMode: false,
            })

            this.map.pm.setGlobalOptions({
                markerStyle: {
                    icon: this.createMarkerIcon(),
                },
                preventMarkerRemoval: true,
            })

            this.map.pm.enableGlobalEditMode()

            this.drawItems = new LF.FeatureGroup().addTo(this.map)

            this.map.on('pm:drawstart', (e) => {
                if (this.drawItems.getLayers().length === 0) {
                    return;
                }

                if (confirm(config.lang.warning.limit)) {
                    this.drawItems.clearLayers()
                    this.updateGeoJson()
                } else {
                    this.map.pm.disableDraw()
                    this.map.pm.enableGlobalEditMode()
                }
            })

            this.map.on('pm:create', (e) => {
                this.map.pm.disableDraw()
                this.map.pm.enableGlobalEditMode()

                if (e.layer && e.layer.pm) {
                    e.layer.pm.enable({
                        allowSelfIntersection: false,
                    })

                    this.drawItems.addLayer(e.layer)
                    this.updateGeoJson()
                }
            })

            // Load existing GeoJSON if available
            const existingGeoJsonFeature = this.getGeoJsonFeature()
            if (existingGeoJsonFeature) {
                this.drawItems = LF.geoJSON(existingGeoJsonFeature, {
                    pointToLayer: (geoJsonPoint, latlng) => {
                        return L.marker(latlng, {
                            icon: this.createMarkerIcon(),
                        })
                    },
                }).addTo(this.map)

                this.map.fitBounds(this.drawItems.getBounds(), {
                    maxZoom: 16,
                })
            }

            this.drawItems.on('pm:edit', (e) => {
                this.updateGeoJson()
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

        removeMap: function(el) {
            this.tile.remove()
            this.tile = null
            this.map.off()
            this.map.remove()
            this.map = null
        },

        attach: function(el) {
            this.createMap(el)
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.intersectionRatio > 0) {
                        if (!this.map) {
                            this.createMap(el)
                        }
                    } else {
                        this.removeMap(el)
                    }
                })
            }, {
                root: null,
                rootMargin: '0px',
                threshold: 1.0,
            })
            observer.observe(el)
        },
    }
}
