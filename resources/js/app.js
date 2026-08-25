import './bootstrap';
import './theme';
import './charts';
import { registerConfirmDialog } from './confirm-dialog';
import { registerGuestLogin } from './guest-login';
import { registerQrScanner } from './qr-scanner';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

registerConfirmDialog(Alpine);
registerGuestLogin(Alpine);
registerQrScanner(Alpine);

Alpine.start();
