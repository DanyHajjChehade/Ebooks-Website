// Copies the icons the UI uses out of node_modules into resources/icons so Blade
// never reads node_modules at runtime. Run with `npm run icons` after changing the list.
//   lucide-static (ISC)  -> resources/icons/*.svg          (<x-icon name="…"/>)
//   simple-icons (CC0)   -> resources/icons/brands/*.svg   (<x-brand-icon name="…"/>)
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const root = join(here, '..', '..');

const LUCIDE = [
    'arrow-right', 'arrow-up-right', 'book-open', 'check', 'chevron-down', 'chevron-left', 'chevron-right',
    'circle-alert', 'circle-check', 'clock', 'copy', 'download', 'feather', 'file-text', 'image-up', 'info',
    'layout-dashboard', 'library-big', 'loader-circle', 'lock', 'log-out', 'mail', 'map-pin', 'menu',
    'message-square-quote', 'moon', 'pencil', 'phone', 'plus', 'receipt-text', 'search', 'settings',
    'shopping-bag', 'sliders-horizontal', 'star', 'sun', 'tags', 'trash-2', 'triangle-alert', 'upload',
    'user', 'users', 'x',
];
const BRANDS = ['facebook', 'instagram', 'x', 'youtube', 'tiktok'];

const clean = (svg) => svg
    .replace(/<!--[\s\S]*?-->/g, '')
    .replace(/\s+/g, ' ')
    .replace(/> </g, '><')
    .trim();

for (const name of LUCIDE) {
    const svg = readFileSync(join(root, 'node_modules/lucide-static/icons', `${name}.svg`), 'utf8');
    const inner = clean(svg).replace(/^.*?<svg[^>]*>/, '').replace(/<\/svg>$/, '');
    writeFileSync(join(here, `${name}.svg`), `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">${inner}</svg>\n`);
}

mkdirSync(join(here, 'brands'), { recursive: true });
for (const name of BRANDS) {
    const svg = readFileSync(join(root, 'node_modules/simple-icons/icons', `${name}.svg`), 'utf8');
    const inner = clean(svg).replace(/^.*?<svg[^>]*>/, '').replace(/<\/svg>$/, '').replace(/<title>.*?<\/title>/, '');
    writeFileSync(join(here, 'brands', `${name}.svg`), `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">${inner}</svg>\n`);
}

console.log(`Copied ${LUCIDE.length} lucide icons and ${BRANDS.length} brand icons.`);
