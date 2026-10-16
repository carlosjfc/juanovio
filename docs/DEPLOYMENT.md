# Production deployment checklist

Prepared 2026-10-11. No production deployment has been performed. Domain, hosting, verified mail identities and legal approval remain user-supplied dependencies.

## Prepare the release

Analytics setup, consent behavior, required GTM tags and preview checks are documented in [ANALYTICS.md](ANALYTICS.md). Enable production analytics only after completing those checks; the application now selects one provider and does not load tags before permission. Publishing the Google container is a separate release step.

- [ ] Approve business copy and finalize privacy/terms, contact details, processor information and retention. Replace draft labels in the footer and choose the legal-page indexing policy; setting a flag does not rewrite these pages.
- [ ] Confirm the canonical public hostname and HTTPS certificate. Redirect HTTP and alternate hosts to that origin at the hosting layer; configure trusted proxies/hosts for the actual infrastructure.
- [ ] Provision PHP compatible with the installed Laravel version, required extensions, and a web root pointing to `public/`. Make `storage/` and `bootstrap/cache/` writable by the application, without exposing them publicly.
- [ ] Provide secrets through the hosting environment or an untracked `.env`. Preserve the application key across releases. Never overwrite an existing environment file as part of deployment.
- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to the canonical HTTPS origin, and `SESSION_SECURE_COOKIE=true`. Choose session/cache stores appropriate to the number of application instances. Multi-instance rate limiting needs shared cache.
- [ ] Configure the verified inquiry recipient, sender and supported real mail transport. Verify provider credentials and required SPF/DKIM/DMARC configuration. Log/array mailers do not enable inquiries.
- [ ] Keep `LEGAL_REVIEWED=false` and `INQUIRIES_ENABLED=false` until their prerequisites are satisfied. Protect preview environments from indexing; review flags must not unintentionally make previews public.

Build in an isolated release directory or CI environment using the lockfiles:

```sh
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan optimize
php artisan website:check
```

Run Pint, PHP tests and browser checks in CI with development dependencies installed before shipping. `website:check` is read-only and exits 1 if a configured launch prerequisite is missing, including on a deliberately closed prelaunch deployment. It prints no credential values and sends no mail. A passing result verifies configuration shape, not factual/legal approval, domain ownership, provider authentication or actual delivery.

Existing environments using file sessions/cache and synchronous mail do not require database migrations. The distributed `.env.example` now selects database queue/cache stores and a local service identity; set a unique production `SERVICE_INSTANCE` and plan migrations and backup/rollback when adopting these settings. No queue worker is required for current synchronous inquiry notifications.

Optional managed queue/scheduler infrastructure is provided by `bin/manage-services` and `services/`. Follow [the operations guide](OPERATIONS.md) for the dedicated Linux service account, ACLs, asynchronous backend setup, identity checks and systemd installation. This infrastructure does not change synchronous inquiry delivery. Restart managed services after release/rollback and configuration changes when enabled.

## Verify the deployed site

- [ ] Deploy `public/brand/icons/` and the nonempty `public/favicon.ico`. Verify `/brand/icons/juanovio-favicon-32.png` and `/favicon.ico` return 200 with image content. The production Apache `/icons/` alias intercepted the original favicon URLs; the shared head now uses the safe brand path. No favicon environment variable is required. Browsers may retain the earlier failed favicon until their cache is refreshed.

- [ ] Confirm `/up` and all eight pages respond successfully; unknown routes return 404. Verify production pages do not expose debug output.
- [ ] Check canonical, Open Graph, JSON-LD and sitemap URLs for the chosen HTTPS origin, including query variants, proxy behavior and alternate-host redirects.
- [ ] Verify the approved share-image URL serves `image/png`, and test social previews. The initial card uses the unchanged supplied logo rather than newly designed artwork.
- [ ] Check mobile/tablet/desktop layouts, menu keyboard behavior, skip link, visible focus, form descriptions and feedback. Automated checks supplement manual accessibility review.
- [ ] Measure mobile Lighthouse with production caching/compression enabled. Monitor field Core Web Vitals once there is sufficient traffic; local timings do not predict public performance.
- [ ] After policy approval and mail configuration, enable inquiries and submit controlled buyer/supplier/partnership/general tests. Verify inbox arrival, reply-to, error recovery and retention; do not commit message content.
- [x] User-supplied screenshots verify one partnership inquiry: website success confirmation, Resend Delivered status to the configured inquiry recipient, and receipt in Hostinger Mail with all submitted business fields and privacy acknowledgment. Delivery reached Spam, not Inbox. Personal field values and dashboard identifiers are not reproduced here.
- [ ] Resolve Spam placement and verify Inbox delivery. Buyer, supplier, and general live submissions, reply-to behavior, error recovery, and operational retention remain separate checks.
- [ ] After business/legal review and publication readiness, enable the review gate and verify robots, page directives and the sitemap. Privacy/terms currently remain noindexed until that policy is explicitly changed.
- [ ] Run Schema.org Validator and Google Rich Results Test on public URLs. Verify the Search Console property, inspect URLs and submit the sitemap. Indexing and rich results are not guaranteed.

## Rollback and operations

- [ ] Keep the prior release/build available and switch releases atomically where supported. Preserve environment settings, application key and persistent storage.
- [ ] After rollback, rebuild configuration/view/route caches against the restored release. Restart long-lived processes if introduced later.
- [ ] Assign ownership for inbox monitoring, mail delivery failures, application logs, backups and dependency updates. Keep logs free of submitted inquiry content.
- [ ] Put the owner-led privacy-request process into operation: monitor privacy@juanovio.com; record receipt and applicable deadlines; verify identity proportionately without routinely collecting ID documents; search relevant inbox, sent, archive, Trash, and any authorized forwarded/local copies; protect third-party information; complete access/correction/deletion or document a valid exception; contact providers where needed; confirm the outcome. Keep only the minimum request-handling record needed under an approved retention schedule. Review applicable rights, deadlines, and appeal/complaint requirements before publishing the policy as final.
- [ ] Implement the inquiry-mailbox retention process: delete unanswered inquiries 30 days after receipt when no commercial discussion develops, and closed inquiries 30 days after closure. Review relevant commercial correspondence periodically after a relationship ends and document any continued retention need. The website does not perform mailbox deletion. Confirm the review cadence, responsible person, permanent deletion from Trash, any local/forwarded copies, and provider backup expiration before finalizing the privacy policy.

Related commands:

```sh
php artisan website:check
npm run measure:performance
PLAYWRIGHT_BASE_URL=https://your-approved-host npm run test:browser
```

Run the browser suite against preview first: its existing tests assume a draft, disabled inquiry form. Update readiness expectations before running that suite against an enabled production form; do not send live inquiries inadvertently.

Reference: [Laravel deployment documentation](https://laravel.com/framework/docs/13.x/deployment).
