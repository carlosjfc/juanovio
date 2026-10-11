# Juanovio implementation notes

## Audit and implementation

The initial repository contained the four root briefs and a Composer authentication file, but no Laravel application, dependencies, approved logo PNGs, or photographs. The original briefs and authentication file were preserved. A Laravel skeleton was initialized separately and copied into the repository without replacing the root README or `.gitignore`. The skeleton's generated agent instructions were removed and the original Juanovio `AGENTS.md` restored.

Installed foundation: Laravel 13, PHP requirement `^8.3`, Livewire 4, Flux UI Pro 2, Tailwind 4, and Vite 8. Exact versions are pinned in the dependency lockfiles. Flux button, input, select, textarea, checkbox, and navigation syntax was checked against installed Blade components. Vendor files were not modified.

The implementation includes six marketing pages, two legal drafts, a shared responsive Blade layout, Livewire inquiry handling, mail notifications, metadata, breadcrumbs, robots, and sitemap routes. Content is version-controlled in `config/website.php`; navigation, inquiry types, category and market structures are in `config/juanovio.php`. No marketing CMS or inquiry database was added.

## Brand assets and typography

Approved logo artwork has now been supplied in `public/brand/original/`. Original PNGs are preserved unchanged. No generic globe, substitute logo, or invented operational photographs were created. User-supplied PNG favicons remain in `public/icons/`; unchanged copies in `public/brand/icons/` are linked in the shared head to avoid Apache's default `/icons/` alias. A valid root `public/favicon.ico` contains the supplied 16px, 32px and 64px artwork for browser fallback, replacing the previously empty file.

The shared `brand-image` component uses responsive WebP assets configured in `config/juanovio.php`. Desktop header: horizontal logo; mobile/tablet header up to 64rem: monogram; footer: full logo, lazy-loaded. The `<picture>` source selects the compact artwork without downloading the desktop logo for the mobile header. Intrinsic dimensions and original aspect ratios are retained. `BRAND_LOGO_PATH` remains an optional desktop-header override; leaving it empty uses the optimized default.

| Artwork | Original dimensions | Responsive WebP widths |
|---|---|---|
| Horizontal logo | 1448 × 1086 | 640, 960 |
| Full logo | 1448 × 1086 | 480, 720 |
| Monogram | 1254 × 1254 | 96, 192 |

Full-resolution lossless WebP copies in `public/brand/optimized/` were verified pixel-identical to the PNGs. Smaller responsive copies were resampled with `cwebp -lossless -exact -m 6 -resize WIDTH 0`. They preserve the complete canvas, transparency, colors, and aspect ratio, with no tracing, recoloring, or artwork crop. The horizontal source contains substantial surrounding canvas; the desktop header now centers the complete image on a compact, positioned brand link rather than reserving layout height for its transparency. The image is not clipped, stretched, or recolored. Source-size files are retained but are not used for these compact website placements. Desktop/mobile header and footer screenshots were inspected, and browser requests confirmed the correct responsive header source.

Cinzel 600, Cormorant Garamond 400, and Montserrat 500 are self-hosted through locked Fontsource packages and Vite. SIL Open Font License notices are retained in `public/licenses/`. No runtime font-provider request is needed. Font synthesis is disabled. Cinzel is available as a ceremonial token; the logo is never recreated with it.

The approved four colors live in `resources/css/app.css`. Supplemental functional tokens are `ink #29242D`, `muted #605963`, and `paper #FFFFFF`; these are UI neutrals, not new official brand colors. Section spacing, content width, and brand radius are centralized there. Automated accessibility checks cover actual rendered foreground/background combinations; they do not constitute a complete WCAG audit.

## Local development

```sh
composer install
npm ci
cp .env.example .env
php artisan key:generate
npm run build
```

Do not overwrite an existing `.env`. Laravel Herd serves this project at `http://juanovio.test`. Set `APP_URL` to the site's actual canonical origin. For another environment use `php artisan serve` and update the URL. Session and cache defaults use files, and email is synchronous; the marketing site does not require database migrations. Standard Laravel database scaffolding remains available for future needs.

Current command-line verification uses PHP 8.5; Herd serves the local site with PHP 8.4. Earlier setup notes about a Homebrew `intl.so` warning are historical; no system PHP configuration was changed.

## Inquiry behavior and launch configuration

The form supports buying, supply, partnership, and general inquiries. Buying and supply inquiries require a product; company, country, valid email, message, and privacy acknowledgment are required for all inquiries. Public uploads are unavailable. The form uses Laravel/Livewire request protection, bounded validation, a honeypot, a locked minimum submission time, and five attempts per hashed IP address per ten minutes. Failed validation also consumes an attempt. Use a shared cache and configure trusted proxies appropriately if deploying across several application instances.

The component does not store inquiry content in a database. It sends an escaped HTML notification to the configured company mailbox, using the sender's address as reply-to. It clears personal fields after transport acceptance, prevents repeat submission after success, and reports delivery exceptions without logging submitted content. Transport acceptance does not guarantee inbox arrival. The form deliberately rejects log/array transports and composite fallback configurations that might silently log personal information.

Delivery is disabled unless all of these are configured:

- `INQUIRIES_ENABLED=true`
- `INQUIRY_RECIPIENT` is a verified business mailbox
- `LEGAL_REVIEWED=true`, after replacing/finalizing the policy drafts
- `MAIL_MAILER` points to a supported real transport, with verified credentials and sender identity

SMTP has a 15-second timeout. Synchronous delivery was selected because no queue worker infrastructure was supplied and inquiries are not persisted separately. If queues are introduced, define encrypted job storage, retention, delivery failures, and recovery before changing the success semantics.

Keep mail credentials and Flux authentication out of Git. Production should use HTTPS, secure session cookies, `APP_DEBUG=false`, and appropriate hosting/mail access controls. Define mailbox retention and a verified privacy contact during legal review.

## Content and launch limitations

Analytics was revised on 2026-10-11 to replace simultaneous direct GA4/GTM loading with a configurable single provider, consent-gated loading, browser preferences and success-only inquiry events. The privacy draft was updated. See [ANALYTICS.md](ANALYTICS.md) for configuration, event contracts, testing, limitations and required external GTM publication.

All copy remains a draft for factual review. Categories are interests, not confirmed inventory. Guyana is described as a pilot under evaluation. No supplier identities, corporate credentials, addresses, phone numbers, testimonials, statistics, or active route maps were invented. The founder's family story is withheld pending explicit approval. JSON-LD now describes the supplied company identity and approved logo, WebSite, page types, breadcrumbs, and conservative opportunity lists. Open Graph and Twitter summary cards use the supplied full-logo PNG unchanged, with actual dimensions and alternative text; no new artwork or social account identity was invented.

The legal routes are explicitly marked as drafts and noindexed. Until `LEGAL_REVIEWED` is true, all pages are noindexed, robots disallows crawling, and the sitemap is empty. That flag does not rewrite policy text or certify compliance: approved copy must be edited into the version-controlled content first. Any additional Organization facts require verification before adding them to structured data.

Before launch: review public claims and legal drafts; confirm privacy contact, processors, retention and jurisdiction; configure/test mail delivery with real infrastructure; verify any additional approved imagery; recheck metadata and indexing.

## Verification

Current checks and launch follow-ups are recorded in [PERFORMANCE.md](PERFORMANCE.md), [SEO_REVIEW.md](SEO_REVIEW.md), and [DEPLOYMENT.md](DEPLOYMENT.md). The verification counts in the historical sections below describe earlier milestones.

```sh
vendor/bin/pint
php artisan test
npm run build
npx playwright install chromium
npm run test:browser
php artisan website:check
npm run measure:performance
```

Set `PLAYWRIGHT_BASE_URL` if not using Herd. Browser tests cover all eight pages at 390px, 768px, and 1440px, check overflow and runtime errors, run axe WCAG A/AA checks, exercise keyboard navigation, and verify supplier preselection and disabled draft submissions. Screenshots are generated in ignored `test-results/` directories. PHP tests cover rendered copy, metadata/indexing, conditional validation, consent, spam checks, rate limits, delivery failure, success/duplicate behavior, and notification escaping. Notifications are faked; actual provider delivery requires configuration and a separate operational check.

The extended browser suite also verifies SEO/social metadata, JSON-LD identities, internal links, the social image, and accessible form descriptions. `website:check` is a read-only configuration check; exit 1 is expected locally while launch prerequisites remain unmet. It does not send mail or certify legal review. If configuration is cached when code/config changes, clear it locally with `php artisan config:clear`; rebuild it as part of a production release.

Tailwind excludes unused Flux families through `@source not`; review the exclusions before adopting new components or Pro variants. CSS fell from 459.14 KB to 183.25 KB raw. The reproducible performance command reports lab observations and asset size, not field Core Web Vitals.

Initial verification: 13 PHP tests (124 assertions) and 30 browser tests passed; Pint, production build, Composer validation, Blade caching, and route caching also passed. Desktop navigation's default Flux gray narrowly missed contrast against Ivory; the shared functional text token corrects it. Home desktop/mobile and mobile contact screenshots were visually inspected after rendering, including the final navigation adjustment. No automated accessibility violations were found in the tested states; manual assistive-technology testing and final asset review remain launch checks.

## Layout refinement

On phone-width screens, back-to-top now uses a 48px target, a 24px right inset and a 64px bottom inset, plus any browser-reported safe-area insets. The extra baseline clearance keeps the control away from the bottom corner even when iPhone Chrome reports no bottom safe area; Safari can add its reported inset. Desktop positioning is retained. Browser checks verify the minimum mobile spacing and target size; physical iPhone Safari/Chrome toolbar states still require device verification after deployment.

Text CTAs now use the reusable `cta-link` Blade component with Flux's installed `arrow-up-right` outline SVG icon. The previous Unicode arrow could become a blue emoji on iPhone Safari. SVG geometry and currentColor provide consistent rendering without system glyph or emoji fallback; decorative arrows are hidden from assistive technology and CTA text retains the approved Montserrat font. Arrow size and spacing use centralized CSS tokens.

Back-to-top intentionally focuses the header so keyboard navigation resumes at the top. The shared focus-visible rule previously drew a purple outline around that entire noninteractive container, including on mobile after menu navigation. A header-only focus override removes that outline while preserving visible focus on links and buttons. Browser regression checks cover menu navigation followed by back-to-top, and confirm Tab then focuses the logo link with its normal visible outline.

The live site was inspected in Chrome and through automated Chromium checks. The duplicated desktop current-page underline was removed, retaining Flux's single indicator. Header height was reduced from 241px to 112px on desktop, and from 112px to 80px on mobile. Centralized section padding is now 36–60px rather than 56–112px; hero, breadcrumb, card, and closing spacing were tightened. Display headings wrap more efficiently, category introductions sit beside their rows on desktop, and mobile footer navigation uses two columns. Four-section content such as the process steps now uses a balanced two-by-two grid on wider screens rather than a three-plus-one arrangement.

Mobile navigation now uses the installed Flux dropdown, icon button, and menu components. It provides a hamburger icon, an accessible name, expanded-state semantics, keyboard navigation, Escape dismissal, restored trigger focus, and 44px-or-larger control targets. A stable scrollbar gutter prevents page content reflow when the menu opens. No custom navigation JavaScript or vendor edits were added.

The refinement passed 33 browser tests, including menu-open accessibility and layout-stability checks, plus an overflow scan of all eight pages at nine viewport widths (320, 375, 390, 768, 1024, 1025, 1280, 1440, and 1920px). Header, hero, category, footer, and mobile-menu screenshots were inspected. At the initially measured desktop/mobile widths, the home page became approximately 32%/19% shorter without removing content.

PHP tests were also isolated from the locally cached application configuration, which otherwise caused persistent file-cache rate limits to leak into repeated test runs. The PHPUnit configuration uses a separate config-cache path and forces temporary test environment values. Live configuration caches and `.env` were left unchanged. All 13 PHP tests pass on consecutive runs.
