// Book Planet front end: small ES modules, progressive enhancement throughout.
// Pages work without JS; these add fetch-based cart updates, dialogs, menus and motion.
import Alpine from 'alpinejs';
import { initTheme } from './theme';
import { initToasts } from './toast';
import { initDialogs } from './dialogs';
import { initNav } from './nav';
import { initCart } from './cart';
import { initForms } from './forms';
import { initMotion } from './motion';

initTheme();
initToasts();
initDialogs();
initNav();
initCart(); // before initForms: cart forms cancel their own submit first
initForms();
initMotion();

window.Alpine = Alpine;
Alpine.start();
