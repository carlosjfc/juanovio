# Juanovio brand tokens — approved core identity

**Authority:** user-supplied “JUANOVIO Brand Directive — Core Identity / Heritage + Prestige · Variant B.” These core colors and font recommendations are approved; do not replace them with sampled guesses.

## Official palette

| Name | HEX | Usage |
|---|---|---|
| Deep Plum | `#4B1F6F` | Primary brand color, signature emphasis, prominent headings and CTAs |
| Walnut Brown | `#6B4A2E` | Secondary accent, dividers, elevated details |
| Sand Taupe | `#D6C6B1` | Borders, subtle surfaces and supporting accents |
| Ivory | `#F9F6EF` | Background, quiet page sections |

## Official type recommendations

| Family | Weight | Purpose |
|---|---|---|
| Cinzel | SemiBold / `600` | **Logo wordmark style reference**; occasional formal display if appropriate. Never recreate/replace official logo with styled text. |
| Cormorant Garamond | Regular / `400` | Refined headings, taglines, subheadings and premium editorial text |
| Montserrat | Medium / `500` | Navigation, labels, buttons, small caps and supporting UI text |

Use font files consistent with their licenses, or load via a dependable font provider. Do not ship Google Fonts or other externally hosted assets where privacy/security policy disallows them. Configure Tailwind / Flux typography utilities to use these shared tokens, not arbitrary hardcoded font-family values.

## CSS variables

```css
:root {
  --brand-deep-plum: #4B1F6F;
  --brand-walnut-brown: #6B4A2E;
  --brand-sand-taupe: #D6C6B1;
  --brand-ivory: #F9F6EF;

  --brand-font-wordmark-reference: 'Cinzel', Georgia, serif;
  --brand-font-editorial: 'Cormorant Garamond', Georgia, serif;
  --brand-font-ui: 'Montserrat', Arial, sans-serif;
}
```

These CSS values identify families and fallbacks; **font assets must still be loaded** by the implementation. The logo typography is illustrative guidance only and must not replace the supplied raster lockup.

## Logo forms and usages

- **Primary/full logo:** brand-centric contexts, documents, appropriate website locations.
- **Horizontal logo:** site header, email signatures, narrow horizontal placements.
- **Monogram:** favicon, app icon, compact social avatar. Verify size legibility.
- Concept: prominent **J** for Juanovio; distinctive **O** evokes global opportunity; orbital route references international paths and connection. Do not use a generic globe substitute.
- Preserve approved PNG artwork exactly. Derive WebP for production if needed; never approximate official SVG/vector artwork.
- Await the actual approved logo uploads before assigning filenames, generating favicon derivatives, or making final asset-specific decisions.

## Accessibility and brand fidelity

Check WCAG contrast for every actual text/surface pairing. Official palette colors are not automatically appropriate for every text size or button state. Any supplemental near-black/white and state colors must be documented separately as functional UI tokens and not called new official brand colors.
