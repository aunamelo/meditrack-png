<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>MediTrack PNG | Delivery Tracking</title>
    <meta name="theme-color" content="#0f766e">

    @include('partials.fonts')
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <div class="mx-auto flex min-h-screen max-w-md flex-col px-5 py-8">
        <div class="mb-6 text-center">
            <p class="text-section-label">MediTrack PNG</p>
            <h1 class="heading-page mt-1">Delivery Tracking</h1>
        </div>

        <div class="module-panel space-y-4 p-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Transfer</p>
                <p class="text-lg font-semibold text-ink">{{ $transfer->transfer_number }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Vehicle</p>
                    <p class="font-medium text-ink">{{ $transfer->vehicle?->displayLabel() ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Cargo</p>
                    <p class="font-medium text-ink">{{ $transfer->drug?->drug_name ?? '—' }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Route</p>
                <p class="font-medium text-ink">Lae AMS &rarr; Modilon Hospital</p>
            </div>
        </div>

        <div class="mt-6 flex flex-1 flex-col items-center justify-center text-center">
            <button
                id="track-toggle"
                type="button"
                class="flex h-40 w-40 items-center justify-center rounded-full bg-brand-600 text-lg font-bold uppercase tracking-wide text-white shadow-lg transition hover:bg-brand-700 active:scale-95"
            >
                Start<br>Tracking
            </button>

            <p id="track-status" class="mt-5 text-sm font-medium text-ink-secondary">Not sharing location yet</p>
            <p id="track-detail" class="mt-1 text-xs text-muted"></p>
        </div>

        <p class="mt-6 text-center text-xs text-muted">
            Keep this open while you drive. It only shares your location for this delivery, and stops automatically once it's marked received.
        </p>
    </div>

    <script>
        (function () {
            const pingUrl = @json($pingUrl);
            const button = document.getElementById('track-toggle');
            const statusEl = document.getElementById('track-status');
            const detailEl = document.getElementById('track-detail');

            let watchId = null;
            let tracking = false;

            function sendPing(position) {
                const body = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    speed_kmh: position.coords.speed !== null ? Math.max(0, position.coords.speed * 3.6) : null,
                    heading: position.coords.heading,
                    accuracy_meters: position.coords.accuracy,
                };

                fetch(pingUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(body),
                    keepalive: true,
                })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        if (!ok && data.stop) {
                            stopTracking();
                            statusEl.textContent = data.message || 'Tracking stopped.';
                            return;
                        }
                        const now = new Date();
                        detailEl.textContent = 'Last sent ' + now.toLocaleTimeString();
                    })
                    .catch(() => {
                        detailEl.textContent = 'Connection issue — will retry on next update.';
                    });
            }

            function startTracking() {
                if (!navigator.geolocation) {
                    statusEl.textContent = 'This phone/browser does not support location sharing.';
                    return;
                }

                watchId = navigator.geolocation.watchPosition(sendPing, function (err) {
                    statusEl.textContent = 'Location error: ' + err.message + '. Check location permission is allowed.';
                }, {
                    enableHighAccuracy: true,
                    maximumAge: 20000,
                    timeout: 25000,
                });

                tracking = true;
                button.textContent = 'Stop Tracking';
                button.classList.remove('bg-brand-600', 'hover:bg-brand-700');
                button.classList.add('bg-red-600', 'hover:bg-red-700');
                statusEl.textContent = 'Sharing your location…';
            }

            function stopTracking() {
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                tracking = false;
                button.innerHTML = 'Start<br>Tracking';
                button.classList.remove('bg-red-600', 'hover:bg-red-700');
                button.classList.add('bg-brand-600', 'hover:bg-brand-700');
                statusEl.textContent = 'Not sharing location';
            }

            button.addEventListener('click', function () {
                tracking ? stopTracking() : startTracking();
            });

            window.addEventListener('beforeunload', stopTracking);
        })();
    </script>
</body>
</html>
