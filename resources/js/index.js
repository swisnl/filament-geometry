import * as LF from 'leaflet';
import { FullScreen } from 'leaflet.fullscreen';
import 'leaflet-gesture-handling';
import '@geoman-io/leaflet-geoman-free';
import * as GeoSearch from 'leaflet-geosearch';
import { combine } from '@turf/combine';
import { flatten } from '@turf/flatten';

const geoSearchProviders = Object.fromEntries(
    Object.entries(GeoSearch).filter(([key]) => key.endsWith('Provider'))
);

LF.GeoJSON.include({
    withoutEvents(callback) {
        const originalFire = this.fire;
        this.fire = () => this;
        callback();
        this.fire = originalFire;
    },
});

export default function filamentGeometry($wire, $watch, config) {
    // Leaflet objects are kept outside Alpine's reactive data: Alpine would wrap them in a proxy, which Leaflet doesn't
    // recognize as the same object in its own event bookkeeping, so e.g. an open popup misses zoom updates
    let map = null
    let tile = null
    let drawItems = null

    return {
        $wire: $wire,
        $watch: $watch,
        config: config,

        value: config.statePath ? $wire.entangle(config.statePath) : config.value,
        ignoreNextValueUpdate: false,

        create(el) {
            // Create map
            map = LF.map(el, this.config.map)
            map.addControl(new FullScreen())

            if (this.config.bounds) {
                let southWest = LF.latLng(this.config.bounds.sw.lat, this.config.bounds.sw.lng)
                let northEast = LF.latLng(this.config.bounds.ne.lat, this.config.bounds.ne.lng)
                let bounds = LF.latLngBounds(southWest, northEast)
                map.setMaxBounds(bounds)
                map.fitBounds(bounds)
                map.on('drag', () => {
                    map.panInsideBounds(bounds, { animate: false })
                })
            }

            if (this.config.map.gestureHandling) {
                map.on('enterFullscreen', () => {
                    map.gestureHandling.disable()
                })
                map.on('exitFullscreen', () => {
                    map.gestureHandling.enable()
                })
            }

            // Create tile layer
            tile = LF.tileLayer(this.config.tileLayer.url, this.config.tileLayer.options).addTo(map)

            // Init geo search
            if (this.config.geoSearch?.provider && !this.config.disabled) {
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

                map.addControl(search);
            }

            // Add geometries to map
            drawItems = this.getLeafletLayer().addTo(map)

            this.fitGeometryBounds()

            // Watch for changes coming from Livewire
            this.$watch('features', () => {
                if (!this.ignoreNextValueUpdate) {
                    drawItems.withoutEvents(() => {
                        drawItems.clearLayers()
                        this.getLeafletLayer().eachLayer(layer => drawItems.addLayer(layer))
                    })

                    this.fitGeometryBounds()
                }
                this.ignoreNextValueUpdate = false;
            })

            if (this.config.disabled) {
                return;
            }

            // Init Geoman
            map.pm.setLang(this.config.locale, undefined, 'en');
            map.pm.addControls(this.config.geoman);

            map.pm.setGlobalOptions({
                layerGroup: drawItems,
                allowSelfIntersection: false,
                removeLayerBelowMinVertexCount: false,
                markerStyle: {
                    icon: this.createMarkerIcon(),
                },
            })

            map.pm.enableGlobalEditMode()

            map.on('pm:drawstart', () => {
                if (drawItems.getLayers().length === 0) {
                    return;
                }

                const allowedShapes = [drawItems.getLayers()[0].pm.getShape()];
                if (allowedShapes[0] === 'Rectangle') {
                    allowedShapes.push('Polygon')
                } else if (allowedShapes[0] === 'Polygon') {
                    allowedShapes.push('Rectangle')
                }
                const allowedShapeEnabled = allowedShapes.some((shape) => map.pm.Draw[shape].enabled())

                if (!this.config.multipart || !allowedShapeEnabled) {
                    if (confirm(this.config.multipart ? this.config.lang.warning.limit_multipart : this.config.lang.warning.limit)) {
                        drawItems.clearLayers()
                    } else {
                        map.pm.disableDraw()
                        map.pm.enableGlobalEditMode()
                    }
                }
            })

            map.on('pm:create', () => {
                map.pm.disableDraw()
                map.pm.enableGlobalEditMode()
            })

            map.on('pm:globaldragmodetoggled pm:globalremovalmodetoggled pm:globalcutmodetoggled pm:globalrotatemodetoggled', (e) => {
                if (e.enabled === false) {
                    map.pm.enableGlobalEditMode()
                }
            })

            drawItems.on('pm:edit layeradd layerremove', () => {
                this.updateGeoJson()
            })

            drawItems.on('pm:remove', (e) => {
                // Geoman doesn't properly remove the layer from the group, so we should do that ourselves
                drawItems.removeLayer(e.layer)
            })
        },

        createMarkerIcon() {
            return LF.divIcon(this.config.markerIcon);
        },

        fitGeometryBounds() {
            if (drawItems.getLayers().length > 0) {
                map.fitBounds(drawItems.getBounds(), {
                    maxZoom: 16,
                })
            }
        },

        updateGeoJson() {
            try {
                let value = null;
                if (drawItems.getLayers().length) {
                    if (this.config.multipart && drawItems.getLayers().length > 1) {
                        value = combine(drawItems.toGeoJSON()).features[0].geometry;
                    } else {
                        value = drawItems.getLayers()[0].toGeoJSON().geometry;
                    }
                }

                this.ignoreNextValueUpdate = true;
                this.value = value ? JSON.stringify(value) : null;
            } catch (error) {
                console.error('Error updating GeoJSON:', error)
            }
        },

        get features() {
            const parsed = typeof this.value === 'string'
                ? (this.value ? JSON.parse(this.value) : null)
                : (this.value ?? null);

            return parsed ? flatten(parsed).features : null
        },

        hasPopup(feature) {
            return this.config.popups === true && typeof feature.properties?.popup === 'string'
        },

        isInteractive(feature) {
            return !this.config.disabled || this.hasPopup(feature)
        },

        getLeafletLayer() {
            return LF.geoJSON(this.features ?? [], {
                pointToLayer: (feature, latlng) => {
                    return L.marker(latlng, {
                        icon: this.createMarkerIcon(),
                        interactive: this.isInteractive(feature),
                    })
                },
                // Leaflet reads `interactive` when a vector layer is first rendered, so it's set per feature here
                style: (feature) => ({
                    interactive: this.isInteractive(feature),
                }),
                onEachFeature: (feature, layer) => {
                    if (this.hasPopup(feature)) {
                        layer.bindPopup(feature.properties.popup, {
                            className: 'fi-geometry-popup-ctn',
                        })
                    }

                    layer.pm.getShape = function () {
                        switch (feature.geometry.type) {
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
                                throw new Error(`Unsupported feature type: ${feature.geometry.type}`);
                        }
                    }
                },
            })
        },

        destroy() {
            if (map) {
                map.remove();
                map = null;
            }
            drawItems = null;
            tile = null;
        },
    }
}
