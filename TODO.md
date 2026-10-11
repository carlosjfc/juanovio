# Juanovio website — shared task tracker

Last updated: 2026-10-11

## How we use this file

- The user and AI assistants maintain this tracker together. Read it before planning website work and update it after completing work or discovering a dependency.
- Use `[ ]` for outstanding tasks and `[x]` only when completion criteria have been verified. Add `In progress` or `Blocked: <reason>` below an item when needed.
- Record relevant files, verification results, and unresolved dependencies with the task. Update the date above when changing this file.
- This tracker records priorities, not blanket authorization to deploy, publish claims, change review gates, or contact others. Follow the current user request and `AGENTS.md`.
- Preserve approved artwork and conservative business positioning. Do not invent company facts, operational coverage, or legal assurances.

## Next development priorities

- [ ] **Complete social sharing metadata.** AI implementation; user approval for any new artwork.
  - Add an approved share image, Open Graph image/alternative text/site name/locale, and Twitter card metadata through the shared layout.
  - Done when all eight pages render consistent metadata, image URLs resolve, and the artwork remains faithful to the brand.
- [ ] **Review CSS and loading performance.** AI investigation and implementation.
  - Investigate the 459.14 KB raw / 50.08 KB gzip CSS build and unnecessary component styles. Measure mobile performance before and after changes.
  - Done when justified improvements preserve Flux interactions and responsive layouts, the build and browser checks pass, and measurements are recorded. Field Core Web Vitals require production traffic.
- [ ] **Reconcile implementation documentation with the current site.** AI documentation update.
  - `docs/IMPLEMENTATION.md` still says Organization structured data is withheld and includes historical test counts. Update its current-state guidance to reflect JSON-LD and point to the SEO review.
  - Done when setup and launch instructions accurately describe the implementation and distinguish historical checks from current results.

## Launch dependencies

- [ ] **Review and approve public business copy.** User/business review; AI can prepare edits.
  - Verify company identity and all factual claims across page copy and metadata. Confirm categories remain interests and Guyana remains a pilot under evaluation unless documented evidence supports changes.
  - Done when approved wording is reflected in the version-controlled content and metadata.
- [ ] **Finalize privacy policy and terms.** User/legal review; AI can implement reviewed text.
  - Confirm jurisdiction, verified privacy contact, hosting/mail processors, processing locations, international transfers, mailbox retention, rights/deletion procedures, and applicable terms.
  - Replace draft copy and labels following review; decide the legal-page indexing policy explicitly.
  - Done when reviewed policies are published in the site and form disclosures match actual data handling.
- [ ] **Configure and verify real inquiry delivery.** User supplies business/provider details; AI implements configuration and checks within authorized scope.
  - Configure verified recipient and sender identities, a real mail transport, and credentials through environment settings. Confirm provider-required domain authentication.
  - Test buying, supply, partnership, and general inquiries, replies, validation, spam limits, and delivery errors. Confirm inbox arrival separately from transport acceptance.
  - Depends on reviewed policies and delivery configuration before enabling public submissions. Keep secrets and personal inquiry contents out of Git and logs.
  - Done when end-to-end delivery is verified and enabled settings reflect approved readiness.
- [ ] **Configure production hosting and canonical URLs.** User confirms domain/hosting and deployment scope; AI prepares implementation.
  - Configure HTTPS, canonical `APP_URL`, trusted hosts/proxies as appropriate, redirects, secure sessions, `APP_DEBUG=false`, and operational cache/mail settings.
  - Verify canonical, Open Graph, JSON-LD, and sitemap URLs on the public origin, including query variants and alternate hosts.
  - Done when production checks pass and deployment/rollback instructions are documented. Keep preview environments nonindexable or private.
- [ ] **Verify production accessibility and performance.** AI checks; manual review where necessary.
  - Recheck mobile/tablet/desktop, keyboard navigation, form feedback, branding, image loading, and Lighthouse. Record results and address material issues.
  - Done when automated checks pass and remaining manual/field limitations are documented.
- [ ] **Enable and verify search indexing after review.** Requires completed business/legal review and production readiness.
  - Enable the existing review gate only after prerequisites are met. Confirm public pages are crawlable and the sitemap contains intended canonical URLs.
  - Validate public structured data with Schema.org Validator and Google's Rich Results Test. Use Search Console URL Inspection and submit the sitemap through the verified property.
  - Done when production directives, sitemap, schemas, and Search Console submission are checked. Submission does not guarantee indexing or rich results.

## After launch

- [ ] **Develop useful commercial content.** User/business evidence and AI research/writing.
  - Research buyer/supplier search intent. Expand approved explanations of specifications, evaluation criteria, documentation requirements, and inquiry preparation.
  - Add category or market pages only when there is enough distinct, useful content backed by approved facts. Keep category and market structures extensible.
  - Done per page when factual review, metadata/schema, internal links, accessibility, and route checks are complete.
- [ ] **Establish ongoing monitoring and maintenance.** User operational preferences and AI support.
  - Agree how to review Search Console coverage, inquiry delivery, dependency/security updates, and production performance. Decide whether privacy-reviewed analytics are needed.
  - Done when ownership, review cadence, and any required tools are documented. No analytics are currently configured.

## Completed baseline

- [x] Laravel, Livewire, Flux UI Pro, Tailwind CSS, and Vite site foundation.
- [x] Eight responsive pages, shared navigation/footer, conservative version-controlled content, and legal drafts.
- [x] Approved raster logos, responsive WebP derivatives, brand tokens, and licensed self-hosted fonts.
- [x] Inquiry implementation with validation, spam/rate controls, notification handling, and prelaunch gates; real delivery remains a launch task above.
- [x] Unique titles/descriptions, canonical links, visible breadcrumbs, sitemap, and robots/review gates.
- [x] JSON-LD on all eight pages, using Organization, WebSite, appropriate page types, breadcrumbs, and conservative opportunity lists.
- [x] Local verification: 18 PHP tests / 279 assertions, 36 browser checks across three viewport sizes, Pint, and production build passed on 2026-10-11.
- [x] Repository/local SEO review documented in [docs/SEO_REVIEW.md](docs/SEO_REVIEW.md). Public indexing, field performance, and Google validation remain outstanding.

## Reference files

- [Project instructions](AGENTS.md)
- [Brand tokens](BRAND_TOKENS.md)
- [Draft content brief](WEBSITE_CONTENT.md)
- [Implementation notes](docs/IMPLEMENTATION.md)
- [SEO review](docs/SEO_REVIEW.md)
