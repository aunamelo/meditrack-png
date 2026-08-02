import './bootstrap';
import './theme';
import './charts';
import { registerConfirmDialog } from './confirm-dialog';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

registerConfirmDialog(Alpine);

Alpine.start();
