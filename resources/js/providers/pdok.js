import { JsonProvider } from 'leaflet-geosearch'

export default class PDOKProvider extends JsonProvider {
    constructor(options = {}) {
        super({
            ...options,
            params: {
                bq: 'type:gemeente^0.5 type:woonplaats^0.5 type:weg^1.0 type:postcode^1.5 type:adres^2.0',
                fl: 'id,weergavenaam,centroide_ll',
                rows: 5,
                ...options.params,
            }
        });
    }

    endpoint({ query }) {
        return this.getUrl('https://api.pdok.nl/bzk/locatieserver/search/v3_1/suggest', { q: query });
    }

    parse({ data }) {
        return data.response.docs.map((doc) => {
            const position = doc.centroide_ll.replace(/POINT\(|\)/g, '').trim().split(' ');

            let highlight = doc.weergavenaam;
            if (data.highlighting[doc.id].suggest) {
                highlight = data.highlighting[doc.id].suggest[0];
            }

            return {
                x: Number(position[0]),
                y: Number(position[1]),
                label: doc.weergavenaam,
                highlight,
                bounds: null,
                raw: doc,
            }
        });
    }

    async search(options) {
        const url = this.endpoint({
            query: options.query,
        });

        const json = await fetch(url)
            .then(response => response.json())
            .then(json => {
                // If there are no results found but there are spellcheck corrections, we search again using the corrected query.
                if (json.response.numFound < 1) {
                    if (json.spellcheck.collations.length >= 2) {
                        const url = this.endpoint({
                            query: json.spellcheck.collations[1].collationQuery,
                        });

                        return fetch(url)
                            .then(response => response.json());
                    }
                }

                return json;
            });

        return this.parse({ data: json });
    }
}
