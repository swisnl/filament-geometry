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

                this.drawItems.on('pm:edit', (e) => {
                    this.updateGeoJson()
                })

                this.map.fitBounds(this.drawItems.getBounds(), {
                    maxZoom: 16,
                })
            }
        },
        createMarkerIcon() {
            const markerColor = config.markerColor || '#3b82f6'
            const markerHtml = `<svg xmlns="http://www.w3.org/2000/svg" class="map-icon" fill="${markerColor}" width="36" height="36" viewBox="0 0 24 24"><path d="M12 0c-4.198 0-8 3.403-8 7.602 0 4.198 3.469 9.21 8 16.398 4.531-7.188 8-12.2 8-16.398 0-4.199-3.801-7.602-8-7.602zm0 11c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"/></svg>`

            return LF.divIcon({
                className: config.markerIconClassName,
                html: markerHtml,
                iconAnchor: [18, 36],
                iconSize: [36, 36],
            });
        },
        updateGeoJson: function() {
            try {
                this.$wire.set(config.statePath, this.drawItems.getLayers()[0] ? JSON.stringify(this.drawItems.getLayers()[0].toGeoJSON().geometry) : null, true)
            } catch (error) {
                console.error('Error updating GeoJSON:', error)
            }
        },

        getGeoJsonFeature: function() {
            return JSON.parse(this.$wire.get(config.statePath)) ?? {}
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
