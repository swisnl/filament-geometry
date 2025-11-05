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

            if (config.geoSearch.provider) {
                if (!geoSearchProviders[config.geoSearch.provider.name]) {
                    throw new Error(`Unsupported GeoSearch provider: ${config.geoSearch.provider.name}`);
                }

                const search = new GeoSearch.GeoSearchControl({
                    ...config.geoSearch,
                    provider: new geoSearchProviders[config.geoSearch.provider.name](config.geoSearch.provider.options),
                    resultFormat: ({ result }) => `${result.highlight || result.label}`,
                    searchLabel: config.lang.geo_search.search_label,
                    clearSearchLabel: config.lang.geo_search.clear_search_label,
                    notFoundMessage: config.lang.geo_search.not_found_message,
                });
                // Fix until https://github.com/smeijer/leaflet-geosearch/pull/436 lands
                search.searchElement.input.setAttribute('name', 'geosearch')

                this.map.addControl(search);
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

                const allowedShapes = [this.drawItems.getLayers()[0].pm.getShape()];
                if (allowedShapes[0] === 'Rectangle') {
                    allowedShapes.push('Polygon')
                } else if (allowedShapes[0] === 'Polygon') {
                    allowedShapes.push('Rectangle')
                }
                const allowedShapeEnabled = allowedShapes.some((shape) => this.map.pm.Draw[shape].enabled())

                if (!this.config.multipart || !allowedShapeEnabled) {
                    if (confirm(this.config.multipart ? config.lang.warning.limit_multipart : config.lang.warning.limit)) {
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
                let value = null;
                if (this.drawItems.getLayers().length) {
                    if (this.config.multipart) {
                        value = combine(this.drawItems.toGeoJSON()).features[0].geometry;
                    } else {
                        value = this.drawItems.getLayers()[0].toGeoJSON().geometry;
                    }
                }

                this.$wire.set(config.statePath, value ? JSON.stringify(value) : null, true)
            } catch (error) {
                console.error('Error updating GeoJSON:', error)
            }
        },

        getGeoJsonFeature: function() {
            const parsed = JSON.parse(this.$wire.get(config.statePath))

            return parsed ? flatten(parsed).features.map(feature => feature.geometry) : null
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
