# Local performance and quality checks

Measured 2026-10-11 on the local Herd HTTPS site with production Vite assets. These are local lab checks, not Lighthouse scores, network-throttled mobile results or field Core Web Vitals.

## CSS change

| Metric | Before | After | Reduction |
| --- | --- | --- | --- |
| CSS bytes | 459,143 | 183,247 | 60.1% |
| Gzip bytes | 50,089 | 26,181 | 47.7% |

Flux's imported stylesheet itself registers the complete Flux/Flux Pro stub directories as Tailwind sources. Removing only the application's broad source directives did not reduce output. The final implementation uses Tailwind's supported `@source not` exclusions for unused Pro components and unused core families. Vendor code and shared Flux styles remain intact; sources for forms, menus, navigation, buttons, icons and shared helpers remain available.

When adding a new Flux component family, review these exclusions in `resources/css/app.css`, rebuild, and check all relevant states. The current select is the native default variant; enabling a Pro select variant requires restoring its source family. [Tailwind source detection documentation](https://tailwindcss.com/docs/detecting-classes-in-source-files) explains the exclusion mechanism.

## Mobile viewport timings

Three fresh Chromium contexts per version; viewport 390 × 844, device scale factor 2, no CPU/network throttling. Values are milliseconds except CLS.

| Metric | Before runs | After runs |
| --- | --- | --- |
| FCP / observed LCP | 92, 64, 60 | 76, 76, 60 |
| DOM ready | 110.4, 82.3, 86.7 | 96.1, 107.5, 89.6 |
| Observed CLS | 0, 0, 0 | 0, 0, 0 |

The substantial improvement is reduced stylesheet payload. Local timing variability does not establish a paint-speed improvement. Observations end after network idle, so later shifts/interactions are outside these measurements. Reproduce with `npm run build` followed by `npm run measure:performance`; set `PLAYWRIGHT_BASE_URL` to check another environment.

## Quality checks

PHP tests cover schema/metadata, indexing gates, inquiry validation/spam controls and notification behavior, plus read-only readiness checks. Browser checks cover all eight pages at three viewport sizes, axe WCAG checks, keyboard navigation, metadata/schema consistency, link and social-image reachability, eager image loading and accessible form descriptions. Home mobile and desktop screenshots were visually reviewed after the CSS change.

Production Lighthouse, manual assistive-technology review, social-platform preview validation, Google rich-result validation and field performance remain release follow-ups in [DEPLOYMENT.md](DEPLOYMENT.md).
