# COMPONENT_LIBRARY.md

**OBSIDIAN SIGNAL** — reusable component contract

| | |
|---|---|
| **Status** | ✅ Approved — Phase 0.3 complete |
| **CSS layer** | `resources/css/components.css` (2,059 lines, 53 primitives) |
| **Blade layer** | Phase 1 — thin wrappers over these primitives |
| **Verifier** | `docs/styleguide/index.html` (18 sections, all states rendered) |
| **Convention** | `.os-` prefix → zero collision with Tailwind utilities |

---

## 0. How to read this document

Every component entry states: **purpose · props/attributes · variants · states · accessibility
requirements · dependencies**. An entry with no states listed is an error, not a truncation — every
component in this system has states.

**The two-layer model:**

| Layer | Owns | Example |
|---|---|---|
| **Component layer** (CSS) | Anatomy that must be identical everywhere: height, padding, focus ring, disabled behaviour, transitions | `.os-btn`, `.os-input`, `.os-table` |
| **Blade layer** (Phase 1) | Composition + business meaning: loops, conditionals, data binding, ARIA wiring | `<x-os.project-card :project="$project" />` |

Bottom line: **the CSS fixes the contract, the Blade component supplies the meaning.** A Blade component
that restyles geometry instead of composing is a defect (§24.2 of `DESIGN_SYSTEM.md`).

---

## A. Foundation Components

### Container — `.os-container`

**Purpose.** The only legitimate way to constrain horizontal page width.

| Modifier | Max width |
|---|---|
| *(default)* | `--container-default` 1320px |
| `--narrow` | 720px — long-form reading |
| `--wide` | 1600px — galleries, CMS |
| `--prose` | 65ch — measure cap |
| `--bleed` | none, no gutter — full-bleed only |

**Rules.** One per band. Never nest. Carries `--space-gutter` inline padding.
**Dependencies.** None.

### Section — `.os-section`

**Purpose.** The vertical rhythm unit. One section per page band.

| Modifier | Effect |
|---|---|
| `--compact` | `--space-section-y-sm` |
| `--flush` | zero padding |
| `--subtle` / `--surface` | background surface |
| `--divided` | top hairline rule |

**Dependencies.** None.

### Stack — `.os-stack`

**Purpose.** One-directional vertical flow with a consistent gap.

**Modifiers.** `--tight` (8px) `--snug` (12px) `--loose` (32px) `--xloose` (64px) `--center` `--start` `--stretch`

**Rules.** Gap comes from the spacing scale only. Never add margins to children of a stack.

### Row — `.os-row`

**Purpose.** Horizontal flow with wrapping.

**Modifiers.** `--tight` `--snug` `--loose` `--between` `--end` `--center` `--nowrap` `--baseline` `--start`

### Grid — `.os-grid`

**Purpose.** 12-column layout with deliberate collapse behaviour.

| Modifier | Behaviour |
|---|---|
| *(default)* | 12 columns |
| `--2` `--3` `--4` | Fixed column counts |
| `--auto` | `auto-fit` with `--os-grid-min` (default 18rem) |

**Collapse.** `--4`/`--3` → 2 cols below 1024px → 1 col below 768px. `--2` → 1 col below 768px.

### Split — `.os-split`

**Purpose.** The signature asymmetric editorial layout.

| Modifier | Ratio | Collapse |
|---|---|---|
| `--7-5` | 7fr / 5fr | 1 col < 1024px |
| `--8-4` | 8fr / 4fr | 1 col < 1024px |
| `--5-7` | 5fr / 7fr | 1 col < 1024px |

**Rules.** At least one split per page (§25.2). Never nest splits — a nested split is a grid problem.

### Divider — `.os-divider`

**Purpose.** Hairline separation.

**Modifiers.** `--vertical` `--heavy` (4px) `--gradient`
**Accessibility.** Purely decorative — if it is an `<hr>` it is structural; a decorative rule uses a
`<div aria-hidden="true">`.

### Typography — `.os-display-*` `.os-heading-*` `.os-text-*` `.os-caption` `.os-label` `.os-editorial` `.os-index-numeral` `.os-mono`

**Purpose.** The complete type hierarchy. Sizes, leadings, and tracking are pre-paired.
**Rules.** One `h1` per page. Never skip heading levels. `.os-editorial` max 2 per page.
`.os-index-numeral` is decorative → `aria-hidden`.
**Dependencies.** Font tokens only.

### Icon — `.os-icon`

**Purpose.** Enforce the Lucide standard: stroke-only, `currentColor`, fixed sizes, 1.5px stroke.

**Sizes.** `--xs` 12 · `--sm` 16 · *(default)* 20 · `--lg` 24 · `--xl` 32 · `--2xl` 40
**Modifiers.** `--bold` (2px stroke)

**Accessibility (mandatory).**
- Decorative → `aria-hidden="true"` on the `<svg>`.
- Meaningful → `<title>` inside the SVG **or** an `aria-label` on the parent control.
- Icon-only control → `aria-label` on the control, `aria-hidden` on the SVG.

### Badge — `.os-badge`

**Purpose.** Compact status or taxonomy chip. Always mono, always small, never interactive except `--tech`.

**Variants.** *(neutral)* `--brand` `--accent` `--success` `--warning` `--danger` `--solid` `--tech`
**Sizes.** `--sm` (20px) · *(default)* 24px

**Rules.** A badge is not a button. If it is clickable it is a link or a `.os-btn--xs`.
Tech badges are **neutral by default** — a stack of eight must not become a rainbow.
**Accessibility.** Status meaning must also be in text; colour alone is insufficient (§5.3).

### Avatar — `.os-avatar`
**Variants.** `--sm` `--lg` `--xl` `--square`
**Accessibility.** A decorative avatar is `aria-hidden`; an identity avatar carries the name in adjacent text.

### Dot — `.os-dot`
**Purpose.** Live/status indicator. `--pulse` for an active state.
**Accessibility.** Never the only indicator — pair with a text label.

---

## B. Navigation Components

### Navbar — `.os-navbar`
**Purpose.** Sticky primary navigation. **Modifiers.** `--solid`
**Structure.** `.os-navbar__inner` › `__brand` · `__nav` · `__actions`
**States.** Transparent-blurred (default) · solid (over hero / scrolled) · mobile-collapsed
**Accessibility.** `<nav aria-label="Main">`. Current page via `aria-current="page"` — the 2px brand
underline is derived from that attribute, never from a class.
**Dependencies.** `.os-container`, `.os-nav-link`, `.os-icon-btn`.

### Nav link — `.os-nav-link`
**Modifiers.** `--signal` (adds the `aria-current` underline)
**Touch target.** 32px control height; mobile uses `.os-mobile-nav__link` at ≥44px.

### Mobile navigation — `.os-mobile-nav`
**Purpose.** Full-height panel, not a dropdown.
**Structure.** `__header` · `__body` · `__link` (≥44px rows)
**Accessibility.** Focus trap, Escape to close, `aria-expanded` on the trigger, focus returned on close.

### Breadcrumb — `.os-breadcrumb`
**Structure.** `__item` · `__sep` · last item `aria-current="page"`
**Accessibility.** Separators `aria-hidden="true"`; the current item is not a link.

### Footer — `.os-footer`
**Structure.** `__grid` (2fr + 3×1fr) · `__col-title` · `__link` · `__bottom`
**Collapse.** 4 → 2 columns below 768px.

### Sidebar — `.os-sidebar`
**Structure.** `__group` · `__item` · **Width.** 264px / 64px collapsed
**Accessibility.** `aria-current="page"` on the active item.

### Admin shell — `.os-admin`
**Structure.** `__sidebar` · `__main` · `__topbar` · `__content` · **Modifier.** `.is-collapsed`
**Breakpoint.** Sidebar removed from layout below 1024px.

### Social links — `.os-social`
**Accessibility.** Each link needs `aria-label` — an icon alone is not an accessible name.

---

## C. UI Components

### Button — `.os-btn`
**Variants.** `--primary` `--secondary` `--accent` `--outline` `--ghost` `--danger` `--danger-quiet` `--link`
**Sizes.** `--xs` 28 · `--sm` 32 · *(default)* 40 · `--lg` 48 · `--xl` 56 · `--block`
**States.** default · hover · active (`translateY(1px)`) · focus-visible · disabled · `.is-loading`
**Anatomy (fixed).** height, inline padding, gap 8px, radius 6px, `--text-body-sm`, weight 500/600
**Contrast.** All 16 fill/text pairings verified ≥4.5:1 (`DESIGN_SYSTEM.md` §4.5)
**Accessibility.** `<button>` for actions, `<a href>` for navigation — never the reverse. Loading sets
`aria-busy="true"` and preserves width. Touch targets ≥44px.
**Dependencies.** Icon slot, `--color-text-on-{solid,secondary,accent}`.

### Icon button — `.os-icon-btn`
**Sizes.** `--sm` `--lg` · **Modifier.** `--outline` · **States.** default · hover · `aria-pressed` · disabled
**Accessibility (strict).** `aria-label` is **mandatory**. Toggles use `aria-pressed`, not a class.

### Input / Textarea / Select — `.os-input` `.os-textarea` `.os-select`
**Sizes.** `--sm` 32 · *(default)* 40 · `--lg` 48
**States.** default · hover · focus · disabled · readonly · invalid (parent `.os-field.is-invalid`)
**Boundary.** `--color-border-input` (= `--color-border-strong`) — WCAG 1.4.11 compliant.
**Extra.** `.os-input-group` for a leading icon + suffix slot.
**Accessibility.** `<label for>` mandatory. `aria-invalid` + `aria-describedby` on error. `autocomplete`
tokens on identity fields. Never placeholder-as-label.

### Field wrapper — `.os-field`
**Structure.** `__label` · `__required` · `__optional` · `__help` · `__error`
**State.** `.is-invalid` propagates the danger boundary to its control
**Order.** Label → control → help → error. Help before error, always.

### Checkbox / Radio — `.os-check`
**Structure.** `__input` · `__body`
**States.** unchecked · checked · indeterminate · focus-visible · disabled
**Accessibility.** The `<label>` wraps input and text, so the whole row is the hit area.

### Toggle — `.os-toggle`
**Structure.** `__input` (visually hidden, focusable) · `__track` · label text
**States.** off · on · focus-visible · disabled
**Accessibility.** The focus ring is drawn on the **track** via a sibling selector, so it stays visible
even though the input is hidden.

### Fieldset — `.os-fieldset`
**Structure.** `__legend` (mono micro-label)
**Accessibility.** Native `<fieldset>` + `<legend>` — required for WCAG 1.3.1 on grouped controls.

### Card — `.os-card`
**Variants.** *(default)* `--elevated` `--interactive` `--flush` `--inset`
**Structure.** `__media` · `__header` · `__body` · `__footer` · `__link` (stretched link)
**States.** default · hover · `:focus-within` · `aria-selected`
**Accessibility.** One interactive target per card — stretched link, never nested anchors.

### Alert — `.os-alert`
**Variants.** *(info)* `--success` `--warning` `--danger` `--neutral`
**Structure.** `__icon` · `__body` → `__title` · `__description` · `__actions`
**Accessibility.** `role="alert"` for time-sensitive, `role="status"` otherwise. All tint pairings ≥4.5:1.

### Skeleton — `.os-skeleton`
**Variants.** `--text` `--title` `--media` `--circle`
**Purpose.** Loading placeholder whose shape matches the final content.
**Accessibility.** `aria-hidden="true"` — the region announces loading, not the placeholder.

### Empty state — `.os-empty-state`
**Structure.** `__icon` · `__title` · `__description` · action
**Rules.** Explains **why** it is empty **and** offers the primary action. A bare "No results" is a defect.

### Error state — `.os-error-state`
**Rules.** States the cause and includes a request ID. Never a bare "Something went wrong."

### Spinner — `.os-spinner`
**Accessibility.** `role="status"` + `aria-label`. Never the only signal — pair with text.

### Progress — `.os-progress`
**Structure.** `__bar` (inline-size driven). **Modifier.** `--meter`
**Accessibility.** `role="progressbar"` + `aria-valuenow` / `aria-valuemin` / `aria-valuemax`.

### Modal / Dialog — `.os-modal`
**Structure.** `__backdrop` · `__panel` → `__header` (`__title` `__description`) · `__body` · `__footer`
**Sizes.** `--sm` 24rem · *(default)* 32rem · `--lg` 48rem · `--xl` 64rem · `--danger`
**Accessibility (all mandatory).** `role="dialog"` + `aria-modal="true"`; focus trapped; Escape closes;
focus returns to the trigger; backdrop click closes; `aria-labelledby` → the title.
**Rule.** Destructive modals use `--danger` **and** a button label naming the consequence.

### Dropdown / Menu — `.os-dropdown` `.os-menu`
**Structure.** `__item` · `__separator` · `__label`
**Positioning.** `--start` / `--end`, `--below` / `--above`
**Accessibility.** `role="menu"` / `role="menuitem"`; arrow-key navigation; Escape closes; `aria-expanded`
on the trigger; focus returns to the trigger.

### Tooltip — `.os-tooltip`
**Structure.** Parent `.os-has-tooltip` + `.os-tooltip--visible`
**Accessibility.** `role="tooltip"` + `aria-describedby`; appears on **focus**, not hover only; never
contains interactive content.

### Tabs — `.os-tabs`
**Structure.** `__list` (tablist) → `.os-tab` (`aria-selected`) · `__panel`
**Accessibility.** Roving tabindex; arrow keys move tabs; `aria-controls` links tab to panel; the strip
scrolls horizontally rather than wrapping.

### Accordion — `.os-accordion`
**Structure.** `__item` · `__trigger` (`aria-expanded`) · `__icon` · `__panel`
**Accessibility.** Trigger is a `<button>`; panel linked by `aria-controls`; the chevron rotates from the
`aria-expanded` attribute, never a class.

### Pagination — `.os-pagination`
**Structure.** `__info` · `__list` → `.os-page-link`
**Accessibility.** `<nav aria-label="Pagination">`; current page via `aria-current="page"`; disabled links
stay focusable with `aria-disabled`.
**Rule.** Server-side via query params so a filtered view is linkable.

### Toast — `.os-toast-region` `.os-toast`
**Structure.** `__title` · `__message`; a 4px left rule carries the tone
**Variants.** *(default)* `--success` `--warning` `--danger`
**Accessibility.** Region is `aria-live="polite"`; destructive confirmations require manual dismissal.

### Link — `.os-link`
**States.** default · hover · focus-visible · visited
**Rule.** Always underlined in prose — WCAG 1.4.1, colour alone cannot indicate a link.

### Timeline — `.os-timeline`
**Structure.** `__item` (`--brand` `--success` `--danger`) · `__marker` · `__body` · `__meta` · `__text`
**Accessibility.** Rendered as an `<ol>` — chronology is meaningful, so semantics carry it.

---

## D. Data Table & CMS Components

### Table — `.os-table`
**Structure.** `.os-table-wrap` › `.os-table-scroll` › `table.os-table`
**Modifiers.** `--compact`; cell helpers `__num` `__actions` `__cell-muted`
**States.** row hover · `aria-selected="true"` · sticky header
**Accessibility.** `<caption>` mandatory (visually hidden when a heading already names it); `<th scope="col">`;
selection via `aria-selected`. Full spec: `DESIGN_SYSTEM.md` §18.1.

### Toolbar — `.os-toolbar`
**Structure.** `__group` · `__spacer` · `__selected`
**Rules.** Search + filters + bulk actions. Bulk actions appear **only** when rows are selected, and state the count.

### Media tile — `.os-media-tile`
**States.** default · hover · `aria-selected="true"` (2px brand ring)
**Structure.** `__caption` on a gradient scrim using `--color-text-on-overlay`
**Accessibility.** `role="button"` + `tabindex="0"` when selectable, plus `aria-selected`.

### Dropzone — `.os-dropzone`
**States.** default · hover · `.is-dragover`
**Rules.** Constraints (formats, size, dimensions) are stated **before** upload — not only on rejection.
**Accessibility.** `role="button"` + `tabindex="0"` + `aria-label`, plus a hidden file input.

### Upload item — `.os-upload-item`
**Structure.** `__thumb` · `__body` → `__name` `__meta` · progress · status badge
**States.** uploading · converting · done · rejected (with the reason shown).

### Metric — `.os-metric`
**Structure.** `__label` (mono micro-label) · `__value` (`--text-heading-xl`, tabular) · `__delta`
**Delta variants.** `--up` `--down` `--flat`
**Rule.** A delta carries a direction glyph **and** text — never colour alone.

### Prose — `.os-prose`
**Purpose.** Sanitised rich-text output (article body and editor preview).
**Covers.** h2/h3, p, a, strong, ul/ol, blockquote, inline code, pre, img, hr, table.
**Capped.** `--prose-max-width` (65ch).

### Status badge
**Implementation.** `.os-badge` with a tone variant, mapped from the domain enum in **exactly one place**
(`StatusBadge`): `published → --success` · `scheduled → --warning` · `draft → --neutral` ·
`failed → --danger` · `archived → --neutral`

---

## E. Portfolio Components

### Hero — `.os-hero`
**Structure.** `__inner` (7/5 grid) · `__title` (with `<em>` editorial accent) · `__lead` · `__actions` ·
`__meta` › `__stat` (`__stat-value` `__stat-label`)
**Rules.** One per page. The `<em>` inside `__title` is the **only** inline editorial-serif usage. The
optional `.os-grid-overlay` sits behind and is `aria-hidden`.

### ProjectCard — `.os-project-card`
**Anatomy (fixed).** `__media` (5fr, 16/10) · `__index` (mono eyebrow) · `__title` · `__summary` ·
`__stack` · `__meta`
**Modifier.** `--featured` inverts to 7/5 media-led
**States.** default · hover (media scales 1.03, title takes the brand colour) · focus-within
**Accessibility.** The whole card is one `<a>`; the title is the accessible name. Uniform 5/7 on every row
— see `DESIGN_SYSTEM.md` §9.3.

### Skill / SkillGroup — `.os-skill` `.os-skill-group`
**Structure.** `__name` · `__level` (mono) · `__meter` (`.os-progress--meter`)
**Accessibility.** The meter is decorative and `aria-hidden`; the **percentage or level text is the
accessible value**. A bar with no text value is inaccessible.

### ExpertiseCard — `.os-expertise-card`
**Structure.** `__icon` · title · description · `__deliverables` (icon + text list)

### Experience timeline — `.os-exp-item`
**Structure.** `__period` (mono, 10rem column) · `__role` · `__company` · `__achievements` (list)
**Collapse.** Two columns → one below 768px.

### Terminal — `.os-terminal`
**Structure.** `__bar` (`__dots` `__title`) · `__body` (pre, mono)
**Syntax slots.** `__prompt` `__key` `__value` `__comment` `__cursor`
**Rules.** Real output only — never fake terminal decoration. `__cursor` is the only animated element and
must stop under `prefers-reduced-motion`.

### Architecture diagram — `.os-arch-node` `.os-arch-connector`
**Structure.** `__node` (`--brand` for emphasis) + 1px connectors
**Accessibility.** A diagram conveyed only visually needs a text alternative — adjacent prose or a
visually-hidden list.

### Chart bars — `.os-chart-bars` `.os-chart-bar`
**Structure.** `__label` · `__track` › `__fill` · `__value`
**Rule.** The only sanctioned chart form (§18.2). Two colours maximum. Label + tabular value on every bar.

---

## F. Signature Devices

| Device | Class | Binding rule |
|---|---|---|
| Chamfer | `.os-chamfer` / `--sm` / `--lg` | Feature surfaces only; never with a large radius on the same corner |
| Signal rule | `.os-signal-rule` | One per section |
| Grid overlay | `.os-grid-overlay` | Decorative; `aria-hidden`; opacity ≤ 0.045 |
| Grain | `.os-grain` | Once on the root wrapper; opacity ≤ 0.035 |

---

## G. Component Dependency Map

Understanding the direction of dependency is what prevents circular composition.

```
design-tokens.css  (values)
  └─ components.css  (primitive contract)
        ├─ Foundation ...... container, section, stack, row, grid, split, divider,
        │                    typography, icon, badge, avatar, dot
        ├─ Forms ........... field, input, textarea, select, check, toggle, fieldset, input-group
        ├─ Surfaces ........ card, alert, modal, menu, toast, tooltip, accordion, tabs, timeline
        ├─ Data ............ table-wrap, table-scroll, table, toolbar, pagination
        ├─ States .......... skeleton, empty-state, error-state, spinner, progress
        └─ CMS ............. admin, sidebar, metric, dropzone, upload-item, media-tile, media-grid

Blade components (Phase 1) — compose primitives, add meaning
  ProjectCard  ← card + badge + stack + icon + progress
  DataTable    ← table-wrap + toolbar + pagination + badge + icon-btn + empty-state
  MediaPicker  ← modal + media-grid + media-tile + dropzone + toolbar
  ContactForm  ← field + input + textarea + select + button + alert
```

**The dependency rule:** a Blade component may compose any primitive. A primitive may **never** depend on a
Blade component. CSS has no knowledge of content — that direction is one-way and non-negotiable.

---

## H. Showcase Verification Checklist

Every component must satisfy all ten before it counts as part of the library.

- [ ] Rendered in `docs/styleguide/index.html`
- [ ] Verified in **both** light and dark mode
- [ ] All variants rendered side by side
- [ ] All applicable states rendered: default, hover, focus, active, disabled, loading, error, empty
- [ ] Focus ring visible and not clipped by an ancestor `overflow`
- [ ] Verified at 320px, 768px, 1024px, 1440px
- [ ] `prefers-reduced-motion` honoured, and the component still reaches its final state
- [ ] Accessible name present for every icon-only control
- [ ] No raw hex, no arbitrary spacing, no arbitrary font size
- [ ] No `[data-theme]` selector in its CSS

---

## I. Change Log

| Date | Change | Reason |
|---|---|---|
| 2026-08-31 | Initial library defined — 53 canonical primitive selectors across 6 families (Phase 0.3) | Foundation gate |
| 2026-08-31 | Added `--color-text-on-{solid,secondary,accent,overlay}`; wired into button variants | Three measured contrast failures in the original button colours |

---

*Verification artefact: `docs/styleguide/index.html`. Automated checks: `_validate.mjs` (53 primitive
assertions + 16 contrast computations).*
