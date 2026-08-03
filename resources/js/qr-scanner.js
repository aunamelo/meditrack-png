/**
 * Alpine.js QR scanner for MediTrack batch labels.
 *
 * Defense walkthrough:
 * 1. html5-qrcode wraps getUserMedia and barcode decoding.
 * 2. We only start the camera in a secure context (HTTPS or localhost).
 * 3. On a successful scan we parse JSON { drug_id, batch_no, expiry },
 *    fill matching form fields by element id, then stop the camera.
 * 4. facingMode: 'environment' prefers the rear camera on phones.
 */

import { Html5Qrcode } from 'html5-qrcode';

/**
 * Fill an input/select if it exists on the page, and notify Alpine/x-model.
 */
function fillField(id, value) {
    if (value === undefined || value === null || value === '') {
        return false;
    }

    const el = document.getElementById(id);
    if (! el) {
        return false;
    }

    el.value = String(value);
    // Alpine x-model and native listeners need these events.
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));

    return true;
}

export function registerQrScanner(Alpine) {
    Alpine.data('qrScanner', () => ({
        open: false,
        scanning: false,
        error: '',
        success: '',
        scanner: null,
        readerId: 'qr-reader-' + Math.random().toString(36).slice(2, 9),

        get isSecureContext() {
            // getUserMedia is blocked on insecure origins (except localhost).
            return window.isSecureContext === true;
        },

        async openScanner() {
            this.error = '';
            this.success = '';

            if (! this.isSecureContext) {
                this.error = 'Camera scanning needs HTTPS or localhost. Open this page over a secure connection and try again.';
                this.open = true;
                return;
            }

            this.open = true;
            // Wait a tick so the #qr-reader div exists in the DOM.
            await this.$nextTick();
            await this.startCamera();
        },

        async closeScanner() {
            await this.stopCamera();
            this.open = false;
            this.error = '';
            // Keep success message briefly so the user sees confirmation.
        },

        async startCamera() {
            this.error = '';
            this.scanning = true;

            try {
                this.scanner = new Html5Qrcode(this.readerId);

                // Rear camera first (phones); falls back if unavailable.
                await this.scanner.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1,
                    },
                    (decodedText) => this.onScanSuccess(decodedText),
                    () => {
                        // Ignore per-frame "not found" callbacks — not real errors.
                    },
                );
            } catch (err) {
                this.scanning = false;
                this.scanner = null;
                this.error = this.cameraErrorMessage(err);
            }
        },

        async stopCamera() {
            if (! this.scanner) {
                this.scanning = false;
                return;
            }

            try {
                // stop() releases the media stream (important on iOS Safari).
                await this.scanner.stop();
            } catch (err) {
                // Already stopped — safe to ignore.
            }

            try {
                this.scanner.clear();
            } catch (err) {
                // Element already cleared.
            }

            this.scanner = null;
            this.scanning = false;
        },

        async onScanSuccess(decodedText) {
            // Stop immediately so we do not process the same QR twice.
            await this.stopCamera();

            let data;
            try {
                data = JSON.parse(decodedText);
            } catch (err) {
                this.error = 'This QR code is not a valid MediTrack batch label (expected JSON).';
                return;
            }

            if (
                typeof data !== 'object'
                || data === null
                || ! ('drug_id' in data || 'batch_no' in data || 'expiry' in data)
            ) {
                this.error = 'QR payload is missing batch fields (drug_id, batch_no, expiry).';
                return;
            }

            // Support the ids used in this project and the names from the brief.
            const filled = [
                fillField('drug_id', data.drug_id),
                fillField('batch_no', data.batch_no),
                fillField('batch_number', data.batch_no),
                fillField('expiry', data.expiry),
                fillField('expiry_date', data.expiry),
            ].some(Boolean);

            if (! filled) {
                this.error = 'Scanned OK, but this page has no matching fields (#drug_id, #batch_no / #batch_number, #expiry / #expiry_date).';
                return;
            }

            this.success = 'Batch scanned — form fields updated.';
            this.error = '';
            this.open = false;
        },

        cameraErrorMessage(err) {
            const raw = String(err?.message || err || '');
            const lower = raw.toLowerCase();

            if (lower.includes('permission') || lower.includes('notallowed') || lower.includes('denied')) {
                return 'Camera permission was denied. Allow camera access in your browser settings (iOS: Settings → Safari → Camera) and try again.';
            }

            if (lower.includes('notfound') || lower.includes('requested device not found') || lower.includes('no camera')) {
                return 'No camera was found on this device.';
            }

            if (lower.includes('notreadable') || lower.includes('trackstart')) {
                return 'The camera is in use by another app. Close other apps using the camera and try again.';
            }

            return 'Could not start the camera. ' + (raw || 'Please try again.');
        },
    }));
}
