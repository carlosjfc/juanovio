# SEO review — 11 October 2026

The technical foundation is good, but the site cannot yet be described as having exceptionally high SEO quality or being ready for organic acquisition. This review covers repository code and the local HTTPS site, not a deployed public domain, Google indexing, search demand, backlinks, or field Core Web Vitals.

Follow-up completed 2026-10-11: social metadata and CSS improvements below are implemented. See [performance results](PERFORMANCE.md) and the [deployment checklist](DEPLOYMENT.md). Local automated checks now include metadata/link verification; production readiness remains subject to business/legal review and actual hosting/mail configuration.

## Findings and priorities

| Priority | Finding | Recommended action |
| --- | --- | --- |
| Launch blocker | With `LEGAL_REVIEWED=false`, all pages emit `noindex, nofollow`, robots blocks all crawling, and the sitemap has no page entries. The local site confirms the noindex setting. This is an intentional prelaunch safeguard. | Complete factual/legal review before enabling the existing gate on production. Keep preview environments private or nonindexable. |
| High | Canonical, sitemap, Open Graph and schema URLs use Laravel route/asset helpers, which reflect request URL configuration. Local URLs correctly resolve to `https://juanovio.test/`; public domain behavior is unverified. | Configure the final HTTPS `APP_URL`, trusted proxies and host redirects; verify all metadata on production, including alternate hosts, HTTP, trailing slashes and query variants. |
| High | Privacy and terms remain draft content, permanently noindexed by the current template, even when the global review gate is enabled. The contact flow also requires reviewed configuration. | Finalize policies, verified contact details and inquiry delivery before public launch. Update draft labels and the legal-page indexing policy deliberately. |
| Implemented | Open Graph now includes image/alternative text, dimensions, MIME type, site name and locale. Twitter summary-card tags are present. | The supplied approved logo PNG is used unchanged. Verify previews on social platforms after deployment. |
| Improved | CSS reduced from 459,143 to 183,247 bytes raw and from 50,089 to 26,181 bytes gzip by excluding unused Flux component sources. | Browser checks pass. Measure production Lighthouse and field Core Web Vitals; local measurements do not establish production loading speed. |
| Medium | Core copy is readable and conservative, but short and sometimes abstract. Most headings are brand-led rather than specific to buyer/supplier search intent. | Research relevant commercial queries and add substantive, approved explanations of evaluation criteria, specifications and inquiry requirements. Create category or market pages only when useful, evidence-backed content exists. |
| Implemented | No JSON-LD existed. | Added one safely encoded, connected JSON-LD graph per page, covered by automated tests. |

## Existing strengths

- All eight pages have distinct titles and descriptions, canonical URLs, server-rendered copy and one H1.
- Navigation, contextual calls to action and breadcrumbs provide internal links. Query-based inquiry variants canonicalize to the contact page.
- Sitemap includes the six principal pages after review and excludes draft legal pages.
- Logo imagery has alternative text, explicit dimensions, responsive WebP variants and lazy loading in the footer. Fonts are self-hosted.
- Browser checks pass across mobile, tablet and desktop, including automated WCAG checks, keyboard navigation and viewport fit. Automated checks do not constitute a full manual accessibility audit.

## JSON-LD implemented

Every graph contains `Organization`, `WebSite` and a page node with stable URL-based IDs. Organization facts are limited to the supplied company name, site URL and approved logo. No private notification mailbox is published.

| Page | Page type | Additional markup |
| --- | --- | --- |
| Home | WebPage | Organization and WebSite identity |
| About | AboutPage | Organization reference; BreadcrumbList |
| What We Trade | CollectionPage | ItemList of categories of interest; BreadcrumbList |
| How We Work | WebPage | BreadcrumbList |
| Markets | CollectionPage | ItemList retaining evaluation/discussion status; BreadcrumbList |
| Contact | ContactPage | Organization reference; BreadcrumbList |
| Privacy draft | WebPage | BreadcrumbList; existing noindex retained |
| Terms draft | WebPage | BreadcrumbList; existing noindex retained |

Lists describe visible opportunities as `Thing` nodes, not products for sale or geographic coverage. No Product, Offer, Review, LocalBusiness, unsupported contact details, certifications or service promises are introduced. No SearchAction is supplied because the site has no search feature. No dates are invented.

Schema is generated in `app/Support/PageSchema.php` and inserted server-side by the shared layout. Titles, descriptions, categories and markets come from existing version-controlled content. Legal breadcrumbs now use the visible draft heading consistently. HTML-sensitive characters are escaped during JSON serialization to prevent closing the script element through content.

## Verification and release follow-up

- Pint passed.
- PHP tests: 18 passed, 279 assertions, including five new schema tests covering every page, identities, opportunity copy, review gates and safe encoding.
- Vite production build passed.
- Playwright: 36 checks passed across three viewport sizes.
- Local rendered homepage has one JSON-LD script, an HTTPS canonical and the expected noindex directive.

Follow-up checks: 22 PHP tests, 42 browser checks, Pint and build passed after social metadata, source exclusions and read-only launch diagnostics were added. See PERFORMANCE.md for timing conditions and deployment follow-ups.

After deployment, validate the public URLs with Schema.org Validator and Google's Rich Results Test, then use Search Console URL Inspection and submit the sitemap. Production crawlability, rich-result eligibility and field performance were not validated here. Structured data does not guarantee rich results or rankings.

References: [Google Organization documentation](https://developers.google.com/search/docs/appearance/structured-data/organization), [Google structured-data policies](https://developers.google.com/search/docs/appearance/structured-data/sd-policies), [Google breadcrumbs](https://developers.google.com/search/docs/appearance/structured-data/breadcrumb), [Google site names](https://developers.google.com/search/docs/appearance/site-names), and Schema.org definitions for [AboutPage](https://schema.org/AboutPage), [ContactPage](https://schema.org/ContactPage) and [CollectionPage](https://schema.org/CollectionPage).
