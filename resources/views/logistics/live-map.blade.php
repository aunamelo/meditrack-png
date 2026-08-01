<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">Live Delivery Map</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="truck"
            description="Real-time GPS positions for vehicles currently on the road from Lae AMS to Modilon Hospital. Updates automatically every 15 seconds."
        />

        <div class="module-panel overflow-hidden p-0">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px]">
                <div id="live-map-canvas" class="h-[560px] w-full bg-surface-muted"></div>

                <div class="flex max-h-[560px] flex-col border-t border-line lg:border-l lg:border-t-0">
                    <div class="flex items-center justify-between border-b border-line px-4 py-3">
                        <p class="text-section-label">On the road</p>
                        <span id="live-map-count" class="rounded-full bg-surface-muted px-2 py-0.5 text-xs font-semibold text-ink-secondary">0</span>
                    </div>
                    <div id="live-map-list" class="flex-1 divide-y divide-line overflow-y-auto">
                        <p class="p-4 text-sm text-muted">Loading vehicle positions…</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-3 text-xs text-muted">
            A vehicle turns amber if it hasn't sent a position in over 10 minutes, and gray if tracking hasn't started or signal has been lost for a while — common on rural stretches of road with weak coverage.
        </p>
    </x-page-container>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" integrity="sha512-h9FcoyWjHcOcmEVkxOfTLnmZFWIH0iZhZT1H2TbOq55xssQGEJHEaIm+PgoUaZbRvQTNTluNOEfb1ZRy6D3BOw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68HdyKKbOcpzoez==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataUrl = @json(getDashboardLiveMapRoute('data'));
            const map = L.map('live-map-canvas').setView([-6.3, 146.0], 8); // Lae / Madang region default view

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            const markers = {};
            const trails = {};
            const listEl = document.getElementById('live-map-list');
            const countEl = document.getElementById('live-map-count');

            function statusColor(v) {
                if (!v.has_location) return '#9ca3af'; // gray — never reported
                return v.is_stale ? '#d97706' : '#0d6b6b'; // amber vs brand teal
            }

            function refresh() {
                fetch(dataUrl, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(payload => {
                        const vehicles = payload.vehicles || [];
                        countEl.textContent = vehicles.length;

                        if (vehicles.length === 0) {
                            listEl.innerHTML = '<p class="p-4 text-sm text-muted">No vehicles are currently on a road delivery.</p>';
                        } else {
                            listEl.innerHTML = '';
                        }

                        const seenIds = new Set();

                        vehicles.forEach(v => {
                            seenIds.add(v.vehicle_id);
                            const color = statusColor(v);

                            if (v.has_location) {
                                const pos = [parseFloat(v.latitude), parseFloat(v.longitude)];

                                if (markers[v.vehicle_id]) {
                                    markers[v.vehicle_id].setLatLng(pos);
                                    markers[v.vehicle_id].setStyle({ color: color, fillColor: color });
                                } else {
                                    markers[v.vehicle_id] = L.circleMarker(pos, {
                                        radius: 9,
                                        color: color,
                                        fillColor: color,
                                        fillOpacity: 0.85,
                                        weight: 2,
                                    }).addTo(map);
                                }

                                markers[v.vehicle_id].bindPopup(
                                    '<strong>' + v.vehicle_label + '</strong><br>' +
                                    (v.transfer_number ? v.transfer_number + ' &middot; ' + (v.drug_name || '') + '<br>' : '') +
                                    (v.speed_kmh ? Math.round(v.speed_kmh) + ' km/h &middot; ' : '') +
                                    'Last ping: ' + (v.last_ping_at || 'never')
                                );

                                if (v.trail && v.trail.length > 1) {
                                    if (trails[v.vehicle_id]) {
                                        trails[v.vehicle_id].setLatLngs(v.trail);
                                    } else {
                                        trails[v.vehicle_id] = L.polyline(v.trail, { color: color, weight: 3, opacity: 0.5 }).addTo(map);
                                    }
                                }
                            }

                            const row = document.createElement('div');
                            row.className = 'p-4 text-sm';
                            row.innerHTML =
                                '<div class="flex items-center justify-between">' +
                                    '<span class="font-semibold text-ink">' + v.vehicle_label + '</span>' +
                                    '<span class="h-2.5 w-2.5 rounded-full" style="background:' + color + '"></span>' +
                                '</div>' +
                                (v.transfer_number ? '<p class="mt-0.5 text-xs text-ink-secondary">' + v.transfer_number + ' &middot; ' + (v.drug_name || '') + '</p>' : '<p class="mt-0.5 text-xs text-muted">No active delivery linked</p>') +
                                '<p class="mt-1 text-xs text-muted">' + (v.has_location ? 'Last ping ' + v.last_ping_at : 'No GPS signal received yet') + '</p>';
                            listEl.appendChild(row);
                        });

                        Object.keys(markers).forEach(id => {
                            if (!seenIds.has(Number(id))) {
                                map.removeLayer(markers[id]);
                                delete markers[id];
                                if (trails[id]) { map.removeLayer(trails[id]); delete trails[id]; }
                            }
                        });
                    })
                    .catch(() => {
                        listEl.innerHTML = '<p class="p-4 text-sm text-red-600">Couldn\'t load live positions. Retrying…</p>';
                    });
            }

            refresh();
            setInterval(refresh, 15000);
        });
    </script>
</x-app-layout>
