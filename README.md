# Book Planet

Book Planet is an independent **digital ebook store**. Readers browse the catalogue, pay with Stripe Checkout, and download their PDF or EPUB files from a personal library. Admins manage books, authors, categories, orders, reviews and site settings.

- **Stack:** Laravel 13 (PHP 8.3+), Blade, Tailwind CSS v4, Alpine.js and Motion (built with Vite), Stripe Checkout and webhooks.
- **Database:** SQLite for development and tests. MySQL 8, MariaDB 10.6+ or PostgreSQL 14+ in production. The app uses no database-specific SQL.
- **Files:** ebook files live on the private `local` disk (`storage/app/private`) and are only served by the authorised download route. Covers and author photos are on the `public` disk.

---

## Local setup

Requirements: PHP 8.3+ (with `pdo_sqlite`, `mbstring`, `intl`, `fileinfo`, `zip`), Composer 2, Node 20+ and npm.

```bash
git clone <repo> book-planet && cd book-planet
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed          # demo catalogue, admin + customer accounts
php artisan storage:link            # serves covers/author photos from /storage
npm install
npm run dev                         # or: npm run build
php artisan serve                   # http://localhost:8000
```

### Demo data

`php artisan migrate:fresh --seed` rebuilds the database from scratch. You can run it again at any time. The seed data:

| What | Details |
|---|---|
| Admin | `admin@bookplanet.test` / `password` |
| Customer | `reader@bookplanet.test` / `password`. Owns 3 books (one free) and has reviewed 2 of them. |
| Catalogue | 8 categories, 12 authors, 40 published books (8 on sale, 2 free) and 1 draft. All titles and authors are invented. |
| Files | A small generated PDF for every book, in `storage/app/private/ebooks/demo/` |
| Activity | 8 more customers with paid orders and reviews, plus one pending and one refunded order |

Books have no cover images. The frontend generates covers.

### Creating an administrator

```bash
php artisan app:make-admin you@example.com                 # promote an existing user
php artisan app:make-admin you@example.com --password=...  # or create the account
```

In the admin area (`/admin/users`), admins can promote and demote other users. They cannot change their own role.

### Tests and code style

```bash
php artisan test          # PHPUnit feature and unit tests (in-memory SQLite, Stripe faked)
vendor/bin/pint           # code style (use --test in CI)
```

---

## Payments with Stripe (test mode)

1. Copy your **test** keys from <https://dashboard.stripe.com/test/apikeys> into `.env`:

   ```dotenv
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   STRIPE_CURRENCY=usd
   ```

2. Forward webhooks to your machine with the [Stripe CLI](https://docs.stripe.com/stripe-cli):

   ```bash
   stripe login
   stripe listen --forward-to localhost:8000/stripe/webhook \
     --events checkout.session.completed,checkout.session.async_payment_succeeded,checkout.session.async_payment_failed,checkout.session.expired,charge.refunded
   ```

   Put the `whsec_...` secret it prints into `STRIPE_WEBHOOK_SECRET`, then run `php artisan config:clear`.

3. Add a book to the cart, check out, and pay with card `4242 4242 4242 4242` (any future expiry date and any CVC).

How checkout works:

- `POST /checkout` turns the cart into a **pending** order. Prices come from the database, never from the browser. The customer is then redirected to Stripe Checkout, and the order id travels in the session metadata.
- Access is granted only once Stripe confirms payment. Two paths can confirm it:
  - the success page asks Stripe for the session and checks the order, amount and currency;
  - the signed webhook `checkout.session.completed` (or `async_payment_succeeded`).
  Fulfilment is idempotent (a conditional status update in a transaction, plus `unique(user_id, book_id)` on the library table), so it is safe for both paths to race.
- `checkout.session.expired` and `async_payment_failed` mark the order `failed`.
- A **full** refund, made from the Stripe dashboard (`charge.refunded`) or with the admin **Refund** button, marks the order `refunded` and removes those books from the customer's library. Partial refunds do not change access.
- A cart that totals 0 is fulfilled immediately, without Stripe. Admin validation only accepts a price of 0 or at least 0.50, which is Stripe's minimum charge.
- Optional: `STRIPE_AUTOMATIC_TAX=true` enables Stripe Tax on Checkout. Stripe Tax must be configured in your Stripe account first.

---

## Configuration reference

| Key | Purpose |
|---|---|
| `APP_ENV`, `APP_DEBUG`, `APP_URL` | In production use `production`, `false` and your https URL |
| `DB_*` | Database connection (see the comments in `.env.example`) |
| `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_CURRENCY`, `STRIPE_AUTOMATIC_TAX` | Payments |
| `MAIL_*` | Password-reset emails need a real mailer in production |
| `FILESYSTEM_DISK`, `EBOOK_DISK`, `MEDIA_DISK` | Storage disks. Ebooks default to the private `local` disk and images to `public`. |
| `EBOOK_MAX_KB` (default 51200), `IMAGE_MAX_KB` (default 2048) | Upload limits enforced by validation |
| `TRUSTED_PROXIES` | Set to `*` (or a list of proxy IPs) behind a load balancer or TLS-terminating proxy |

Uploads are validated by the file's detected content type as well as its extension. PDFs must be `application/pdf`. EPUBs must be `application/epub+zip`, or a zip whose `mimetype` entry says so. Covers and photos must be JPG, PNG or WebP. Every upload is stored under a random name.

---

## Deploy

### Requirements

- PHP 8.3 or 8.4 with `ctype`, `curl`, `dom`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo_mysql` (or `pdo_pgsql`), `tokenizer`, `xml` and `zip`
- Composer 2, and Node 20+ (only to build the assets)
- MySQL 8 / MariaDB 10.6+ / PostgreSQL 14+
- An HTTPS web server (nginx or Apache) whose document root is **`public/`**
- A real mail transport, for password-reset emails

**PHP upload limits** must cover the largest ebook you plan to upload (`EBOOK_MAX_KB`, 50 MB by default). Set them in `php.ini` and in your web server:

```ini
upload_max_filesize = 64M
post_max_size = 64M
```

```nginx
client_max_body_size 64M;
```

### Release steps

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build
php artisan migrate --force
php artisan storage:link                 # once per server
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Then create your first admin with `php artisan app:make-admin you@yourdomain.com`.

Production `.env` checklist:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://…`
- `LOG_LEVEL=warning`
- `SESSION_SECURE_COOKIE=true`
- live Stripe keys
- `TRUSTED_PROXIES` if you are behind a proxy

When `APP_ENV=production`, `/robots.txt` allows crawling and links the sitemap. In every other environment it disallows everything. Destructive database commands (`migrate:fresh` and similar) are blocked.

### Stripe webhook

In the Stripe dashboard (Developers → Webhooks), add the endpoint `https://your-domain/stripe/webhook` with these events:

- `checkout.session.completed`
- `checkout.session.async_payment_succeeded`
- `checkout.session.async_payment_failed`
- `checkout.session.expired`
- `charge.refunded`

Copy the signing secret into `STRIPE_WEBHOOK_SECRET`. The endpoint is exempt from CSRF and rejects any request without a valid signature. If the secret is missing, it fails closed with a 500 response.

### Queue and scheduler

Nothing is queued or scheduled by default. Payment fulfilment happens inside the request. The default `QUEUE_CONNECTION=database` is ready if you later queue mail. In that case, run a worker under a process manager:

```bash
php artisan queue:work --tries=3 --max-time=3600
```

To use the scheduler later, add this cron entry: `* * * * * php /path/to/artisan schedule:run`.

### File permissions

The web server user needs write access to `storage/` and `bootstrap/cache/`, and nothing else:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

`storage/app/private` (the ebook files) must **not** be reachable from the web. Only `public/` is the document root, and `public/storage` links to `storage/app/public` only. Back up `storage/app/private` together with the database.

---

## Project layout (backend)

| Path | Contents |
|---|---|
| `app/Http/Controllers` | Thin controllers: storefront, `Auth/` and `Admin/` |
| `app/Http/Requests` | Form Requests for every input, including list filters |
| `app/Policies` | `BookPolicy` (view/download), `OrderPolicy`, `ReviewPolicy`, `UserPolicy`, plus the `access-admin` gate |
| `app/Services/Cart.php` | Session cart of book IDs |
| `app/Services/CheckoutService.php` | Orders, idempotent fulfilment and refunds |
| `app/Payments` | `PaymentGateway` interface and its Stripe implementation (tests swap in a fake) |
| `app/View/Composers/SiteComposer.php` | Shares `$settings`, `$cartCount`, `$cartBookIds` and `$ownedBookIds` with every view |
| `database/seeders` | Demo catalogue (`data/catalogue.php`) and the PDF generator |
