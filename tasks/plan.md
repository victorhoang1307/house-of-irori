# Implementation Plan: House of IRORI — Home v3B (`index.html`)

## Overview
Build the complete, responsive Static HTML5/CSS3/JS implementation of `Home v3B` (`494:776` Desktop 1920px & `494:1209` Mobile 390px) for House of IRORI using real assets exported from the Figma REST API (`unKDZtWKWErXbWWjttuqWa`).

## Architecture Decisions
- **Direct Figma Asset Export Pipeline:** Export every raster image fill and vector logo/ornament directly from Figma API into `assets/images/` and `assets/icons/` so there is zero loss of visual fidelity.
- **CSS Custom Properties & Responsive Grid:** Use exact Figma colors (`#F4F1EA`, `#FFFFFF`, `#5A2929`, `#4A3B32`, `#C6A15B`, `#0A0A0A`) and typography (`Playfair Display`, `Lora`, `Inter`) with desktop-to-mobile breakpoints matching `1920w default` and `390w default`.
- **Strapi-Ready Data Attributes:** Mark dynamic collection containers (`data-cms-collection="reviews"`, `data-cms-collection="articles"`) and item templates (`data-cms-item`) for seamless Strapi integration in Phase 2.

## Task List

### Phase 1: Foundation & Asset Pipeline
- [ ] Task 1: Export all Figma raster images, framed artwork, watermark graphics, and SVG logos/icons for `Home v3B` into `assets/images/` and `assets/icons/`.
- [ ] Task 2: Write automated verification test suite (`tests/verify-home.mjs`) and base HTML5/CSS3 skeleton (`index.html`, `css/style.css`) with Header (`Stick header` Default + Scrolled + Mobile Drawer) and Hero section.

### Checkpoint: Foundation
- [ ] Asset files verified on disk, Header & Hero render cleanly.

### Phase 2: Core Content Sections
- [ ] Task 3: Implement Story (`Hidden in Plain Sight`), Asymmetric Collage (`The Space Between, Stillness and Beauty`), `Level One: The Alley` (50/50 split + carousel), and `Level Two: The Bar` (50/50 split + carousel).
- [ ] Task 4: Implement `Google Reviews & Map` (50/50 split with English cocktail bar reviews slider + interactive/styled map), `Atmospheric Banner Strip`, `Latest News` (3-card slider), and `Footer` (`A Glimpse Inside` + `Section 20`).

### Checkpoint: Core Features
- [ ] All 10 sections and interactive JS sliders/carousels (`js/main.js`) pass `tests/verify-home.mjs`.

### Phase 3: Browser Verification & Visual QA
- [ ] Task 5: Serve locally, inspect Desktop (`1920x1080`) and Mobile (`390x844`) in Chrome DevTools MCP, verify zero console/network errors, run QA/Review subagents.

### Checkpoint: Complete
- [ ] All acceptance criteria met and verified against Figma renders.

## Risks and Mitigations
| Risk | Impact | Mitigation |
|------|--------|------------|
| Composite masked frames in Figma (e.g., embossed bartender frame, kimono frame, watermark) | Med | Export composite node renders directly from Figma `/v1/images` endpoint at 2x scale so embossed borders and masks are 100% preserved |
| Hebrew placeholder text in Figma Google Review cards | High | Replace with authentic English reviews for House of IRORI while preserving exact card layout and stars |
