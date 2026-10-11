# AGENTS.md — Juanovio International Trading Inc.

## Mission and positioning
Build a premium, credible, internationally oriented B2B trading-house website for **Juanovio International Trading Inc.** The company is a developing international trading venture connecting qualified suppliers, buyers, and commercial opportunities across borders. Its initial commercial pilot explores cement supply into Guyana, but **never position the company as only a cement exporter, Guyana specialist, construction-only company, or established global conglomerate**. The platform must support additional countries, goods, and categories, including construction materials, agricultural commodities, food products such as flour, and other suitable traded goods.

## Truthfulness and compliance — mandatory
- Do not claim verified partnerships, offices, licenses, certifications, trade volumes, operating history, shipping capacity, exclusive distribution rights, completed shipments, or market coverage without documented evidence.
- Distinguish active capabilities from intended opportunities. Prefer 'exploring', 'developing', 'working to connect', 'commercial inquiries' where applicable.
- Do not publish supplier/buyer names, pricing, quotes, or correspondence without approval.
- Do not invent addresses, telephone numbers, registration numbers, testimonials, case studies, or statistics.
- Do not imply Juanovio is the manufacturer, freight carrier, customs broker, or seller of record for every transaction. Commercial roles depend on the agreement.
- All claims about import/export compliance, Incoterms, customs, inspections, payment security, and product standards require transaction-specific verification.

## Brand identity
- Brand: **Juanovio International Trading Inc.**
- Display brand: **JUANOVIO** with secondary descriptor **INTERNATIONAL TRADING** when appropriate.
- Personality: distinguished, enduring, trustworthy, global, elegant, approachable, disciplined.
- Brand story: name draws on the founder's family heritage and honors both parents. Keep public-facing storytelling tasteful and do not reveal private family details without approval.
- Approved visual direction: purple-family premium palette, heritage/prestige-inspired emblem with a visible **J** and path-like movement. **Do not use generic globe icons**, generic shipping-container logos, or redesign the approved emblem.
- The approved PNG artwork is the visual source of truth. Do not recreate it with SVG tracing, AI approximation, CSS shapes, or substitute icons. Existing attempted SVG recreations were rejected. Use the approved raster files unchanged and create optimized WebP derivatives when useful.
- The approved palette is **Deep Plum #4B1F6F**, **Walnut Brown #6B4A2E**, **Sand Taupe #D6C6B1**, and **Ivory #F9F6EF**. Typography: **Cinzel SemiBold (600)** for logo wordmark style reference and occasional ceremonial display headings, **Cormorant Garamond Regular (400)** for refined headings/subheadings/taglines, and **Montserrat Medium (500)** for navigation, buttons, labels, and small-caps supporting text. The official wordmark must remain the supplied image, not HTML text styled to simulate the logo. Treat these values as approved. See BRAND_TOKENS.md.
- Load the approved families through properly licensed/self-hosted font files or a dependable font provider. Use accessible fallbacks only during loading, never as the intended design. Avoid fake weight synthesis.

## Design system
- Premium editorial B2B aesthetic: spacious layout, strong typography, restrained ornament, generous white space, precise alignment, calm motion.
- Use Deep Plum as the primary brand color, Walnut Brown as a secondary accent, Sand Taupe for subtle borders and soft surfaces, and Ivory for backgrounds. Ensure sufficient foreground/background contrast; do not assume every palette pairing meets AA. Do not let purple dominate every surface.
- Design mobile-first, with responsive navigation and refined desktop layouts.
- Prefer authentic, rights-cleared trade/product/port imagery, restrained and contextual. No misleading photos implying actual Juanovio operations.
- Logo must retain correct aspect ratio, clear space, contrast, and source fidelity.
- Never stretch, recolor, crop, or overlay the approved logo in ways that impair legibility.
- Meet WCAG 2.2 AA where practical: keyboard support, visible focus, proper labels, semantic landmarks, contrast, reduced-motion preference.
- Define tokens centrally; do not scatter literal colors, fonts, spacing, or radii across components.

## Technical stack and architecture
- Laravel (project-installed supported version), PHP (compatible supported version), Livewire (project-installed compatible version), **Flux UI Pro** (licensed and installed), Tailwind CSS (project-installed compatible version), Vite.
- Inspect composer.json, package.json, installed Flux documentation/components, and existing application conventions **before** generating code. Do not assume framework version, component names, or API signatures.
- Use Blade and Livewire for interactive features; prefer server-driven components over unnecessary custom JavaScript.
- Use Flux UI Pro components for supported forms, buttons, navigation, dialogs, and feedback; verify their installed syntax. Customize through brand tokens without modifying vendor code.
- Follow Laravel conventions: routes, controllers only where useful, Form Requests or Livewire validation, policies, services, notifications, queues when justified.
- Keep pages and components modular; separate content from presentation. Favor translatable/config-driven content and reusable page sections.
- Do not add a database/CMS merely to render static marketing copy. Start with version-controlled content and migrate to an admin-managed CMS only if needed.
- Ensure secret keys and credentials stay in .env and never in frontend bundles or Git.
- All forms must use CSRF protection, validation, rate limiting, spam mitigation, error handling, and privacy-conscious storage/retention.
- Contact form should support inquiry types: buy, supply, partnership, general; capture name, company, business email, country, product/category, message, and consent as appropriate. Avoid collecting sensitive financial documents on the public form.
- Treat uploads as disabled until security controls and business needs are defined.
- Use queues for outbound email if infrastructure supports them; configure delivery through environment variables, with tests that fake notifications.

## Content and information architecture
- Use WEBSITE_CONTENT.md as approved *draft* copy and content structure; all external-facing factual claims remain subject to review.
- Primary navigation: Home, About, What We Trade, How We Work, Markets, Contact. Optional Insights only when editorial capacity exists.
- Product categories and markets are extensible data structures, not hardcoded one-off cement/Guyana pages.
- Do not assert every listed category or geography is already active. Label opportunities and areas under evaluation appropriately.
- Maintain a consistent inquiry path for buyers and suppliers. Use neutral CTAs: 'Discuss a Trade Opportunity', 'Submit a Buying Inquiry', 'Introduce Your Supply'.
- Use professional international English. Avoid hype ('world-leading', 'guaranteed lowest price', 'seamless global network') and unverified assurances.

## SEO, performance, and quality
- Descriptive titles/meta descriptions, canonical URLs, Open Graph metadata, sitemap, robots, semantic headings, breadcrumb patterns where applicable.
- Structured data only for verified Organization facts. Do not manufacture reviews, addresses, or certifications.
- Responsive images (WebP where appropriate), lazy-load noncritical images, preserve originals, specify dimensions to minimize layout shift.
- Optimize Core Web Vitals and keep dependencies lean. Do not ship enormous hero images or unused component libraries.
- Accessible, localized-ready country/product taxonomies; avoid flag icons as the sole country identifier.
- Provide clear privacy policy and terms drafts for legal review, including form-data handling. Do not claim legal compliance without review.
- Write automated tests for routes, rendered copy, contact validation, spam/rate limits, and notification behavior; run Pint, tests, and build commands available in the project.

## Codex execution sequence
1. Audit repository versions, conventions, existing assets, and installed Flux UI Pro components.
2. Inventory approved brand assets. Identify actual logo variants and source image dimensions. Use the approved fonts/HEX palette in BRAND_TOKENS.md; ask only if asset files or licensing details are missing.
3. Implement centralized design tokens and the approved typography; flag only any truly unverified assets or secondary neutral tokens.
4. Implement responsive shared shell, header, footer, SEO primitives, and accessible navigation.
5. Build pages in WEBSITE_CONTENT.md; keep claims and labels consistent.
6. Implement validated buyer/supplier/general inquiry flow, notification handling, privacy safeguards, and tests.
7. Check mobile/tablet/desktop rendering, accessibility, image performance, and branding fidelity.
8. Report changes, tests run, any blockers, and all remaining assumptions. Never represent provisional branding or unverified business claims as finalized.

## Definition of done
- A coherent premium trading-house site, not a single-country or single-product landing page.
- Approved logo reproduced faithfully; no invented SVG/logo.
- Consistent design tokens and reusable Laravel/Livewire/Flux components.
- Accurate, conservative business copy; responsive, accessible pages.
- Working, tested inquiry pathways; clear distinction between active activity and future opportunities.
