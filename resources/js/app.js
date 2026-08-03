import './bootstrap';
import './theme';
import './charts';
import { registerConfirmDialog } from './confirm-dialog';
import { registerQrScanner } from './qr-scanner';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

registerConfirmDialog(Alpine);
registerQrScanner(Alpine);

Alpine.start();
