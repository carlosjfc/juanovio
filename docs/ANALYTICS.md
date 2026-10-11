# Analytics and inquiry measurement

Implemented 2026-10-11 after reviewing material-freight. Juanovio uses its own existing identifiers, not DoubleO's. No Google account/container changes or production publication were performed.

## Application behavior

`config/juanovio.php` controls analytics. `App\Support\Analytics` validates the selected identifier; invalid configuration fails closed. The layout emits configuration only, and `resources/js/analytics.js` loads exactly one provider after permission. The previous unconditional GTM plus direct GA4 loading is removed. No GTM noscript iframe is emitted because it would bypass the browser consent gate.

Analytics defaults to disabled outside production when `ANALYTICS_ENABLED` is absent. `.env.example` explicitly disables it; production must deliberately enable it after reviewing the tags and privacy draft. Existing `.env` and configuration caches were not edited. Rebuild the configuration cache with the release.

```dotenv
ANALYTICS_ENABLED=true
ANALYTICS_DRIVER=gtm
GOOGLE_TAG_MANAGER_ID=GTM-TFMSHHLF
GOOGLE_ANALYTICS_ID=G-Y1RN3SLGFQ
ANALYTICS_CONSENT_VERSION=2026-10-11
```

These are the existing Juanovio IDs; ownership and intended production property must be verified in Google. Use separate staging IDs or disable analytics in staging. `ANALYTICS_DRIVER=gtag` selects direct GA4 instead; the GTM ID is then ignored. Never install another Google snippet in the layout.

## Consent and privacy

The consent banner follows the shared `.wrap` content width (up to `--width-content`, 76rem), including the site's responsive side gutters. Its text uses the available width to reduce unnecessary wrapping on desktop.

Before permission, no provider script loads and all four Google consent states are denied. Accepting grants only `analytics_storage`; advertising remains denied. The localStorage key `juanovio-analytics-consent` stores the choice, policy version and decision timestamp for 180 days. Invalid, expired, future-dated or obsolete choices require permission again. Increment the version when policy wording or behavior materially changes.

The footer's Analytics preferences control reopens the banner and focuses a choice button. Rejecting after acceptance sends the denied update and reloads the page when the choice can be saved, removing loaded scripts. Changes in another tab trigger a reload. Existing Google cookies are not explicitly deleted; already transmitted data cannot be recalled. When storage is unavailable the choice is page-local; Google receives consent updates and application events stop on rejection, but loaded third-party code cannot be fully unloaded without a reload. This limitation needs consideration during privacy review. Reloading can discard unsent form input.

The implementation uses a consent-gated/basic loading approach, unlike material-freight's loader which can load tags under denied defaults. It is an application safeguard, not certification of legal compliance. Final jurisdictions, Google processing/transfers, retention and wording require legal/owner review. See the updated privacy draft in `config/website.php`.

## Event contract

| Application event | GA4 event | Parameters | When |
| --- | --- | --- | --- |
| `juanovio_page_view` (GTM only) | `page_view` | `page_location`, `page_title`, `page_referrer` | Once per current pathname after permission; supports Livewire navigation |
| `generate_lead` | `generate_lead` | `inquiry_type` | After a validated inquiry is accepted by the mail transport |

Page locations exclude query strings/fragments; referrer is deliberately empty to avoid forwarding sensitive URL content. Inquiry type is one of `buy`, `supply`, `partnership`, `general`. No form name, company, email, country, product, route, message or other user-entered value is included. No revenue/value is invented. A rejected or failed submission emits no lead event; the server's locked success state prevents resubmission events. Events occurring before analytics permission are dropped, not replayed. Analytics is browser-local and can be blocked; it is not a delivery ledger or guaranteed exactly-once system.

No ecommerce events, purchase deduplication, advertising integrations or DoubleO brand logic were copied. Consent for inquiry processing is separate from optional analytics consent.

## Required GTM configuration

The code cannot configure the published Google container. Configure and preview the following in Juanovio's container before enabling production analytics:

1. Keep exactly one Google tag for `G-Y1RN3SLGFQ`, triggered on Initialization / All Pages. Add the configuration parameter `send_page_view` with boolean `false`. Remove competing GA4 loaders/page-view tags. Set its default event parameters to a safe `page_location` (origin + pathname) and empty `page_referrer`; do not let automatically collected events use raw query strings. Verify the resolved parameters in Tag Assistant.
2. Create Data Layer Variables (version 2) for `page_location`, `page_title`, `page_referrer`, and `inquiry_type`.
3. Create a GA4 Event tag named `Juanovio - Page view`, event name `page_view`, using the same Measurement ID. Map the three page parameters to their Data Layer Variables. Trigger on the exact Custom Event `juanovio_page_view`.
4. Create a GA4 Event tag named `Juanovio - Inquiry submitted`, event name `generate_lead`, using the same Measurement ID. Map `inquiry_type` to its Data Layer Variable. Trigger on the exact Custom Event `generate_lead`.
5. Require `analytics_storage` on these tags in Consent Settings and inspect Consent Overview. Do not add advertising tags or Custom HTML that overwrites consent. Disable automatic page views from history changes and automatic form-interaction measurement in the GA4 stream; the application supplies authoritative page/lead events. Review other enhanced-measurement features for unintended URL/form collection; leave them disabled until verified. For the direct `gtag` option, review the same stream settings.
6. Register `inquiry_type` as an event-scoped custom dimension if reports need buyer/supplier/partnership/general breakdowns. Mark `generate_lead` as a key event if all successfully submitted inquiries match the reporting goal; a submitted inquiry is not a qualified commercial lead.
7. Preview clean accepted/rejected/withdrawn sessions in Tag Assistant and GA4 DebugView. Check one page view per page, one lead after successful delivery, none on validation/transport failures, advertising denied, and no personal data. Confirm no provider requests before permission or on a fresh rejected session. Review a test URL containing a fake sensitive query value and ensure it is not transmitted.
8. Publish the reviewed container version and record its version/date and reporting owner here. Publication remains outstanding.

This page-view event tag is intentional: it disables automatic page views and sends only the application's sanitized URLs. The Google tag can internally fetch Google's own libraries; the single-provider guarantee refers to application bootstrap paths.

## Verification

Completed locally: Pint passed; 24 PHP tests passed (410 assertions); Vite build passed; all 54 browser checks passed across mobile/tablet/desktop. After adding global sanitized URL defaults, the 12 analytics browser checks passed again. An isolated temporary Laravel server with analytics enabled also passed real Flux-control checks at 390px, 768px and 1440px: no tags before permission, one GTM loader after acceptance, none after withdrawal/reload, no horizontal overflow and no automated WCAG violations. The mobile consent screenshot was visually inspected. Google network requests were stubbed in these browser checks, so live collection and GTM publication remain unverified.

The local PHP installation emitted a missing `intl.so` startup warning; Pint and tests still passed. This existing environment issue was not changed. Existing `.env` and cached configuration were preserved.

Global Google command settings also use sanitized page locations and empty referrers before provider initialization and page events. This reduces raw URL exposure but intentionally omits referrer and query-based campaign attribution. If campaign measurement is needed later, introduce an explicitly reviewed allowlist of campaign parameters rather than forwarding arbitrary query strings.

PHP tests check provider selection/disabled/invalid settings across pages and success-only inquiry dispatch. Browser tests exercise the actual JavaScript with a controlled HTML fixture and stub Google requests; they cover both drivers, denied defaults, preference expiry/version, saved consent, unavailable storage, withdrawal, safe event payloads, navigation deduplication and keyboard focus. They do not prove a published GTM container behaves correctly. The existing browser suite checks the real local pages at mobile/tablet/desktop sizes.

References: [Google consent implementation](https://developers.google.com/tag-platform/security/guides/consent), [basic consent mode](https://developers.google.com/tag-platform/security/concepts/consent-mode), [recommended GA4 events](https://developers.google.com/analytics/devguides/collection/ga4/reference/events?client_type=gtm).
