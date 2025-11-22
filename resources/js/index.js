import * as LF from 'leaflet';
import 'leaflet.fullscreen';
import 'leaflet-gesture-handling';
import '@geoman-io/leaflet-geoman-free';
import * as GeoSearch from 'leaflet-geosearch';
import PDOKProvider from './providers/pdok.js';
import combine from '@turf/combine';
import flatten from '@turf/flatten';

const geoSearchProviders = Object.fromEntries([
    ...Object.entries(GeoSearch).filter(([key]) => key.endsWith('Provider')),
    ['PDOKProvider', PDOKProvider]
]);

LF.GeoJSON.include({
    withoutEvents(callback) {
        const originalFire = this.fire;
        this.fire = () => this;
        callback();
        this.fire = originalFire;
    },
});

export default function filamentGeometry($wire, $watch, config) {
    return {
        $wire: $wire,
        $watch: $watch,
        config: config,

        value: $wire.entangle(config.statePath),
        ignoreNextValueUpdate: false,

        map: null,
        tile: null,
        drawItems: null,

        create(el) {
            // Create map
            this.map = LF.map(el, this.config.map)

            if (this.config.bounds) {
                let southWest = LF.latLng(this.config.bounds.sw.lat, this.config.bounds.sw.lng)
                let northEast = LF.latLng(this.config.bounds.ne.lat, this.config.bounds.ne.lng)
                let bounds = LF.latLngBounds(southWest, northEast)
                this.map.setMaxBounds(bounds)
                this.map.fitBounds(bounds)
                this.map.on('drag', () => {
                    this.map.panInsideBounds(bounds, { animate: false })
                })
            }

            if (this.config.map.gestureHandling) {
                this.map.on('enterFullscreen', () => {
                    this.map.gestureHandling.disable()
                })
                this.map.on('exitFullscreen', () => {
                    this.map.gestureHandling.enable()
                })
            }

            // Create tile layer
            this.tile = LF.tileLayer(this.config.tileLayer.url, this.config.tileLayer.options).addTo(this.map)

            // Init geo search
            if (this.config.geoSearch.provider) {
                if (!geoSearchProviders[this.config.geoSearch.provider.name]) {
                    throw new Error(`Unsupported GeoSearch provider: ${this.config.geoSearch.provider.name}`);
                }

                const search = new GeoSearch.GeoSearchControl({
                    ...this.config.geoSearch,
                    provider: new geoSearchProviders[this.config.geoSearch.provider.name](this.config.geoSearch.provider.options),
                    resultFormat: ({ result }) => `${result.highlight || result.label}`,
                    searchLabel: this.config.lang.geo_search.search_label,
                    clearSearchLabel: this.config.lang.geo_search.clear_search_label,
                    notFoundMessage: this.config.lang.geo_search.not_found_message,
                });
                // Fix until https://github.com/smeijer/leaflet-geosearch/pull/436 lands
                search.searchElement.input.setAttribute('name', 'geosearch')

                this.map.addControl(search);
            }

            // Add geometries to map
            this.drawItems = this.getLeafletLayer().addTo(this.map)

            this.fitGeometryBounds()

            // Watch for changes coming from Livewire
            this.$watch('geoJsonFeature', () => {
                if (!this.ignoreNextValueUpdate) {
                    this.drawItems.withoutEvents(() => {
                        this.drawItems.clearLayers()
                        this.getLeafletLayer().eachLayer(layer => this.drawItems.addLayer(layer))
                    })

                    this.fitGeometryBounds()
                }
                this.ignoreNextValueUpdate = false;
            })

            // Init Geoman
            this.map.pm.setLang(this.config.locale, undefined, 'en');
            this.map.pm.addControls(this.config.geoman);

            this.map.pm.setGlobalOptions({
                layerGroup: this.drawItems,
                allowSelfIntersection: false,
                removeLayerBelowMinVertexCount: false,
                markerStyle: {
                    icon: this.createMarkerIcon(),
                },
            })

            this.map.pm.enableGlobalEditMode()

            this.map.on('pm:drawstart', () => {
                if (this.drawItems.getLayers().length === 0) {
                    return;
                }

                const allowedShapes = [this.drawItems.getLayers()[0].pm.getShape()];
                if (allowedShapes[0] === 'Rectangle') {
                    allowedShapes.push('Polygon')
                } else if (allowedShapes[0] === 'Polygon') {
                    allowedShapes.push('Rectangle')
                }
                const allowedShapeEnabled = allowedShapes.some((shape) => this.map.pm.Draw[shape].enabled())

                if (!this.config.multipart || !allowedShapeEnabled) {
                    if (confirm(this.config.multipart ? this.config.lang.warning.limit_multipart : this.config.lang.warning.limit)) {
                        this.drawItems.clearLayers()
                    } else {
                        this.map.pm.disableDraw()
                        this.map.pm.enableGlobalEditMode()
                    }
                }
            })

            this.map.on('pm:create', () => {
                this.map.pm.disableDraw()
                this.map.pm.enableGlobalEditMode()
            })

            this.map.on('pm:globalremovalmodetoggled', (e) => {
                if (e.enabled === false) {
                    this.map.pm.enableGlobalEditMode()
                }
            })

            this.drawItems.on('pm:edit layeradd layerremove', () => {
                this.updateGeoJson()
            })

            this.drawItems.on('pm:remove', (e) => {
                // Geoman doesn't properly remove the layer from the group, so we should do that ourselves
                this.drawItems.removeLayer(e.layer)
            })
        },

        createMarkerIcon() {
            return LF.divIcon(this.config.markerIcon);
        },

        fitGeometryBounds() {
            if (this.drawItems.getLayers().length > 0) {
                this.map.fitBounds(this.drawItems.getBounds(), {
                    maxZoom: 16,
                })
            }
        },

        updateGeoJson() {
            try {
                let value = null;
                if (this.drawItems.getLayers().length) {
                    if (this.config.multipart) {
                        value = combine(this.drawItems.toGeoJSON()).features[0].geometry;
                    } else {
                        value = this.drawItems.getLayers()[0].toGeoJSON().geometry;
                    }
                }

                this.ignoreNextValueUpdate = true;
                this.value = value ? JSON.stringify(value) : null;
            } catch (error) {
                console.error('Error updating GeoJSON:', error)
            }
        },

        get geoJsonFeature() {
            const parsed = this.value ? JSON.parse(this.value) : null;

            return parsed ? flatten(parsed).features.map(feature => feature.geometry) : null
        },

        getLeafletLayer() {
            return LF.geoJSON(this.geoJsonFeature ?? [], {
                pointToLayer: (geoJsonPoint, latlng) => {
                    return L.marker(latlng, {
                        icon: this.createMarkerIcon(),
                    })
                },
                onEachFeature: (feature, layer) => {
                    layer.pm.getShape = function () {
                        switch (feature.type) {
                            case 'Point':
                            case 'MultiPoint':
                                return 'Marker';
                            case 'LineString':
                            case 'MultiLineString':
                                return 'Line';
                            case 'Polygon':
                            case 'MultiPolygon':
                                return 'Polygon';
                            default:
                                throw new Error(`Unsupported feature type: ${feature.type}`);
                        }
                    }
                },
            })
        },

        destroy() {
            if (this.map) {
                this.map.remove();
                this.map = null;
            }
            this.drawItems = null;
            this.tile = null;
        },
    }
}
