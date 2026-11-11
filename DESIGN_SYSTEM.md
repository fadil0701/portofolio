# DESIGN_SYSTEM.md

**OBSIDIAN SIGNAL** — Premium Technology × Swiss Editorial × Neo-Industrial Engineering

| | |
|---|---|
| **Status** | ✅ Approved — Phase 0.1 complete |
| **Owner** | Design System (single source of truth) |
| **Consumers** | `resources/css/design-tokens.css`, `resources/css/components.css`, all Blade views, the CMS |
| **Verifier** | `docs/styleguide/index.html` + `_validate.mjs` (76 automated checks) |
| **Last updated** | 2026-08-31 |

> **Authority.** This document is the single source of truth for every visual decision. If code and
> this document disagree, the code is wrong. If a design change is needed, this document changes
> **first**, then the tokens, then the components. Never the reverse.

---

## 1. Design Philosophy

**Software is infrastructure, not decoration.** The interface should read like an instrument:
precise, legible under pressure, and honest about its own state.

Five principles, in priority order when they conflict:

1. **Legibility beats expression.** Contrast, size, and hierarchy are non-negotiable. A beautiful
   interface with one unreadable label is a failure, not a trade-off.
2. **Structure beats ornament.** Hierarchy comes from space, rule, and surface step — not from
   shadows, gradients, or illustration. Every decorative element must be removable without losing
   meaning.
3. **Systems beat screens.** A decision made once in a token applies everywhere forever. A decision
   made inline applies once and becomes debt.
4. **Restraint beats novelty.** One accent moment per viewport. Restraint is what makes the single
   accent read as emphasis rather than noise.
5. **Truth beats polish.** Loading, empty, and error states get the same design attention as the
   happy path. Hiding a state is not designing it.

**The test:** if a screenshot would be indistinguishable from a generic template, it has failed. If
it would be distinguishable but not readable, it has also failed.

---

## 2. Creative Direction

Three references, each contributing a specific, bounded quality:

| Reference | What it contributes | What it does **not** license |
|---|---|---|
| **Premium Technology** | Precision, engineering credibility, monospace as signal, dark-first surfaces, data treated seriously | Glow for its own sake, sci-fi chrome, neon overload |
| **Swiss Editorial** | Asymmetric grids, typographic hierarchy doing the heavy lifting, generous whitespace, index numerals | Ornamental serif everywhere, fake newspaper texture |
| **Neo-Industrial Engineering** | Hairline rules, visible measurement, chamfered corners, functional mono labels, honesty about mechanism | Rust/grit aesthetic, deliberate ugliness, skeuomorphism |

**The synthesis:** an editorial magazine laid out by an engineer, printed on a precision surface.

**Emotional target:** *"this person measures things."* Confidence through accuracy, not volume.

**Deliberately rejected directions** (recorded so they are not relitigated):
- Portfolio-as-art-gallery — hides the engineering work behind atmosphere
- Terminal-pastiche — degrades usability for a costume
- Agency maximalism — animation-first, content-thin
- AI-slop gradient futurism — see §25

---

## 3. Visual Identity

### 3.1 The signal/dark-field metaphor

The palette is named for the object in the callsign: a **quasar** — an intensely luminous point
against a near-black field. That is the whole visual thesis.

- The **field** is obsidian: `#0B0D10`, near-black with a trace of blue. Not pure black — pure black
  kills depth and makes every surface step invisible.
- The **signal** is cyan `#00D4FF` — luminous, cold, technical. Brand and information.
- The **accent** is lime `#C6F277` — used **once** per viewport, for the single most important
  non-brand moment.

### 3.2 Identity devices

Four devices, and only four, carry the identity (full rules in §25):

1. **The signal rule** — a 4px brand rule with tick marks, opening a major section.
2. **The chamfer** — a notched corner (`clip-path`), replacing a large radius on feature surfaces.
3. **The mono micro-label** — uppercase, 0.085em tracked, 11–12px, and it says something real.
4. **The editorial split** — asymmetric 7/5 and 8/4 grids instead of centred symmetry.

### 3.3 Voice in the interface

- Micro-labels are **specific**: `// AVAILABLE · Q1 2026`, never `// WELCOME`.
- Numbers are always tabular and always real. No `99+` placeholders.
- Errors state the cause and the next action. Never "Something went wrong."
- Placeholder prose is a defect. If content does not exist, the **empty state** is the design.

---

## 4. Color Palette

All colour lives in `resources/css/design-tokens.css`. **No raw hex value is permitted anywhere
else** — enforced by an automated check that fails on a hex literal in `components.css`.

### 4.1 Brand

| Token | Dark | Light | Role |
|---|---|---|---|
| `--color-brand-primary` | `#00D4FF` | `#00707F` | Interactive + informational. The signal. |
| `--color-brand-primary-hover` | `#33DEFF` | `#005C69` | Hover |
| `--color-brand-primary-active` | `#00A6C9` | `#004A55` | Pressed |
| `--color-brand-primary-surface` | `#0F313C` | `#E0EEF0` | 12–14% tint |
| `--color-brand-secondary` | `#3678FF` | `#2159D8` | Structural — **fills and gradients only** |
| `--color-brand-secondary-text` | `#4A8CFF` | `#2159D8` | Text-safe secondary |
| `--color-brand-secondary-solid` | `#2A5FD6` | `#2159D8` | Solid fill carrying white text |
| `--color-brand-accent` | `#C6F277` | `#4F6B00` | Emphasis. **Once per viewport.** |
| `--color-brand-accent-surface` | `#2A3120` | `#EAEDE0` | Accent tint |

**Why `--color-brand-secondary` is fill-only in dark mode:** `#3678FF` measures **4.13:1** on
`--color-surface-elevated` — below the 4.5:1 AA floor for body text. It is not a text colour. Use
`--color-brand-secondary-text` (`#4A8CFF`, 6.01:1) instead. This is a measured constraint, not a
style preference.

### 4.2 Surfaces

Elevation steps **up** in luminance (dark) or whiteness (light). Never step a surface *down* to
signal emphasis.

| Token | Dark | Light | Use |
|---|---|---|---|
| `--color-background` | `#0B0D10` | `#F7F8FA` | Page canvas |
| `--color-background-subtle` | `#08090B` | `#EFF1F5` | Alternating bands, table headers |
| `--color-surface` | `#12161C` | `#FFFFFF` | Cards, panels, tables |
| `--color-surface-elevated` | `#1A2028` | `#FFFFFF` | Modals, menus, popovers |
| `--color-surface-inset` | `#08090B` | `#F0F2F6` | Recessed: terminals, code, wells |
| `--color-surface-input` | `#0E1116` | `#FFFFFF` | Form control fill |
| `--color-surface-hover` | `#171C23` | `#F3F5F8` | Row / item hover |
| `--color-surface-active` | `#1F262F` | `#EAEDF2` | Selected / pressed |
| `--color-surface-inverse` | `#F2F4F7` | `#0B0D10` | Inverted chips, tooltips |

### 4.3 Text

Every pair below is a **measured** WCAG 2.2 ratio, not an estimate.

| Token | Dark | Light | Ratio (dark / light) | Permitted for |
|---|---|---|---|---|
| `--color-text-primary` | `#F2F4F7` | `#0B0D10` | 17.66 / 18.31 | Everything |
| `--color-text-secondary` | `#89939F` | `#5A6472` | 6.24 / 5.65 | Supporting copy |
| `--color-text-tertiary` | `#84909C` | `#6E7887` | 5.98 / **4.21** | Micro-labels & metadata **only** |
| `--color-text-disabled` | `#565F6D` | `#A0AAB6` | exempt | Disabled controls (non-content) |

> ⚠️ **`--color-text-tertiary` is 4.21:1 in light mode** — below the 4.5:1 AA floor for normal text.
> In light mode it is permitted **only** for ≥18.66px bold / ≥24px text, or for non-essential
> metadata a user never needs to act on. Any copy a user *must* read uses `--color-text-secondary`
> or above. This is the single intentional sub-AA value in the system, and it is bounded.

### 4.4 Semantic status

Each status exposes four tokens: base (text/icon), `-surface` (tint), `-border`, `-solid` (fill).

| Status | Dark | Light | On page bg |
|---|---|---|---|
| `--color-success` | `#A3D977` | `#2F6B21` | 11.81 / 6.09 |
| `--color-warning` | `#F4BE5E` | `#8A5A00` | 11.46 / 5.58 |
| `--color-danger` | `#F16F75` | `#C0373F` | 6.74 / 5.12 |
| `--color-info` | `#00D4FF` | `#00707F` | 10.99 / 5.45 |
| `--color-neutral` | `#89939F` | `#5A6472` | 6.24 / 5.65 |

Tinted surfaces are verified against their own text: success 8.21/5.42, warning 7.97/5.00,
danger 5.23/4.55, info 7.78/4.87. **All clear 4.5:1.**

### 4.5 Text on solid fills

A filled control's luminance decides which text colour it can carry. This is the most common
contrast bug, so each family declares its own token instead of reusing a generic "inverse":

| Token | Value | Rationale |
|---|---|---|
| `--color-text-on-solid` | `light-dark(#FFFFFF, #0B0D10)` | Primary and danger fills **flip** per mode, so their text must too |
| `--color-text-on-secondary` | `#FFFFFF` | Secondary solid is dark blue in **both** modes |
| `--color-text-on-accent` | `#0B0D10` | Accent solid is light lime in **both** modes |
| `--color-text-on-overlay` | `#FFFFFF` | Sits on a dark scrim in both modes |

Verified pairings — all 16 combinations clear AA:

| Control | Light | Dark |
|---|---|---|
| Primary | `#00707F` + white = **5.79** | `#00D4FF` + `#0B0D10` = **10.99** |
| Primary hover | `#005C69` + white = **7.68** | `#33DEFF` + `#0B0D10` = **12.08** |
| Secondary | `#2159D8` + white = **6.03** | `#2A5FD6` + white = **5.67** |
| Secondary hover | `#1B4AB8` + white = **7.73** | `#3568E0` + white = **4.99** |
| Danger | `#C0373F` + white = **5.44** | `#F16F75` + `#0B0D10` = **6.74** |
| Danger hover | `#A82E36` + white = **6.75** | `#F58A8E` + `#0B0D10` = **8.25** |
| Accent | `#BFE85C` + `#0B0D10` = **13.82** | `#C6F277` + `#0B0D10` = **15.17** |
| Accent hover | `#D6F79A` + `#0B0D10` = **16.34** | `#D6F79A` + `#0B0D10` = **16.34** |

> Derived by measurement during Phase 0. Three bugs were caught this way: white-on-light-red in dark
> mode (2.89:1), `--color-text-inverse` on the light-mode accent fill (1.41:1), and a secondary
> hover that dipped to 4.47:1.

---

## 5. Color Semantic Mapping

Colour is **assigned by meaning, never by aesthetics.** Before choosing a colour, answer: *what does
this colour assert about the thing it colours?*

| Meaning | Token | Never use for |
|---|---|---|
| **Interactive / informational** | `--color-brand-primary` | Decoration, large fills, status |
| **Structural / depth** | `--color-brand-secondary` | Text (fails AA in dark), status |
| **Emphasis — one moment** | `--color-brand-accent` | Anything repeated; more than one per viewport |
| **Success / complete / positive delta** | `--color-success` | Anything merely "good-looking" |
| **Warning / pending / attention needed** | `--color-warning` | Errors |
| **Danger / destructive / error / negative delta** | `--color-danger` | Anything reversible or merely cautious |
| **Neutral / draft / inactive** | `--color-neutral` | Published or active content |

**Hard rules:**

1. **Danger semantics are load-bearing.** A destructive action uses `--color-danger-solid` **and**
   a label naming the consequence ("Delete permanently", not "OK"). Colour never carries meaning
   alone — WCAG 1.4.1.
2. **Status colour maps 1:1 to domain state.** `published → success`, `scheduled → warning`,
   `draft → neutral`, `failed → danger`. This mapping lives in one place in code (`StatusBadge`), not
   at each call site.
3. **Colour is never the only channel.** Every status carries colour **+** a text label **+** where
   useful an icon.
4. **Charts use at most two colours**: `--color-brand-primary` and
   `--color-brand-secondary`. A four-series chart does not get four hues — it gets labelled bars.
5. **Tech/stack badges are neutral** (`--color-neutral-surface`). Eight technologies must not become
   eight colours; that would destroy the signal value of status colour.
6. **Gradient (`--gradient-signal`) is structural only** — reserved for progress fills and one hero
   element. It must never sit behind body text.

---

## 6. Typography System

### 6.1 Families — three, each with exactly one job

| Token | Family | Job | Never used for |
|---|---|---|---|
| `--font-display` | Geist (variable) | Headings, UI, numerals, controls | Long-form body copy |
| `--font-body` | = `--font-display` | Body copy | — |
| `--font-mono` | Geist Mono (variable) | Code, data, technical micro-labels, terminals | Running prose |
| `--font-editorial` | Instrument Serif | Pull-quotes + oversized index numerals | Anything else (**max 2 per page**) |

**Why the display and body families are the same:** contrast between heading and body is created by
*size, weight, leading, and tracking* — not by a second family. Two sans families would add weight
and inconsistency for no legibility gain. The serif is a deliberate, rationed third voice.

**Delivery:** self-hosted via `@fontsource-variable/*` packages — no external font requests, no
layout shift, no third-party privacy surface. All three are OFL licensed.

### 6.2 The scale is generated, not hand-picked

Every size is a `clamp()` interpolated linearly between **320px and 1440px**, pinned at both ends.
The coefficients were computed, not guessed:

| Token | Range | Token | Range |
|---|---|---|---|
| `--text-display-2xl` | 44 → 104px | `--text-heading-sm` | 17 → 21px |
| `--text-display-xl` | 38 → 82px | `--text-body-lg` | 17 → 19px |
| `--text-display-lg` | 33 → 64px | `--text-body-md` | 16 → 17px |
| `--text-display-md` | 28 → 48px | `--text-body-sm` | 14 → 15px |
| `--text-heading-xl` | 27 → 40px | `--text-caption` | 13 → 14px |
| `--text-heading-lg` | 23 → 32px | `--text-micro` | 11 → 12px |
| `--text-heading-md` | 20 → 26px | | |

Interpolated values at the three test viewports (from `_validate.mjs` output):

```
display-2xl   320:44.0   768:68.0   1440:104.0
heading-xl    320:27.0   768:32.2   1440:40.0
body-md       320:16.0   768:16.4   1440:17.0
micro         320:11.0   768:11.4   1440:12.0
```

**Rules:**
- Never set a font size in a component. Use a `--text-*` token.
- Never mix a size token with a leading token from a different step. `--leading-*` is paired 1:1.
- `--text-micro` (11px) is **only** for uppercase mono labels. Never for sentence-case copy.

### 6.3 Line height and tracking

Leading is tightened as type grows and opened as it shrinks — the physical behaviour of type:

| Step | Leading | | Step | Leading |
|---|---|---|---|---|
| display-2xl | 0.94 | | heading-sm | 1.32 |
| display-lg | 1.00 | | body-lg | 1.60 |
| heading-xl | 1.10 | | body-md | 1.62 |
| heading-lg | 1.16 | | caption | 1.50 |

Tracking: `display` = **-0.032em**, `heading` = **-0.020em**, `body` = **-0.006em**,
`label` = **+0.085em**. Large type is tightened optically; uppercase mono labels are opened so
11px text stays legible.

### 6.4 Measure

Body copy is capped at `--container-content` (**65ch**). No paragraph exceeds it. A line of body text
wider than ~75 characters loses the reader; 65ch is the enforceable ceiling.

---

## 7. Font Hierarchy

Each level has one purpose and one placement. Do not skip levels to reach a desired visual size —
choose the correct semantic level and let it be the size it is.

| Level | Class | Semantic | Placement | Weights |
|---|---|---|---|---|
| **1** | `.os-display-2xl` | One per page | Home hero statement | 600 |
| **2** | `.os-display-lg` / `.os-display-md` | One per section | Section openers, page titles | 600 |
| **3** | `.os-heading-xl` / `.os-heading-lg` | `h2` | Section headings | 600 |
| **4** | `.os-heading-md` / `.os-heading-sm` | `h3`, card titles | Sub-sections, cards | 500–600 |
| **5** | `.os-text-lg` / `.os-text-md` | Body | Reading copy | 400 |
| **6** | `.os-text-sm` | Body SM | Dense UI: tables, forms, cards | 400–500 |
| **7** | `.os-caption` | Caption | Metadata, helper text | 400 |
| **8** | `.os-label` | Micro label | Uppercase mono eyebrow, table headers | 500 |
| **9** | `.os-editorial` | Pull quote | Editorial accent | 400 |

**Rules:**
- **One `h1` per page.** Heading levels never skip (`h2` → `h4` is a defect).
- **Level ≠ size.** If a level-4 heading needs to look bigger, the hierarchy is wrong, not the size.
- **The eyebrow precedes the heading.** An `.os-eyebrow` (mono label + rule) sits above a section
  heading, never beside or below it.
- **Numerals in tabular contexts always get `font-variant-numeric: tabular-nums`.** Every stat,
  table cell, metric, and pagination number. Non-tabular figures make digits jump between values.
- **Oversized index numerals** (`.os-index-numeral`) are decorative and must be `aria-hidden`.

---

## 8. Spacing Scale

A 4px base scale plus a 2px "engineering" step for hairline detail:

| Token | px | Token | px | Token | px |
|---|---|---|---|---|---|
| `--space-0-5` | 2 | `--space-4` | 16 | `--space-12` | 48 |
| `--space-1` | 4 | `--space-5` | 20 | `--space-16` | 64 |
| `--space-2` | 8 | `--space-6` | 24 | `--space-24` | 96 |
| `--space-3` | 12 | `--space-8` | 32 | `--space-32` | 128 |

**Fluid rhythm:**
- `--space-section-y` = `clamp(3.5rem, 7vw, 8rem)` — vertical padding of a page band
- `--space-section-y-sm` = `clamp(2.5rem, 4vw, 4.5rem)` — compact variant
- `--space-gutter` = `clamp(1rem, 3vw, 2rem)` — container inline padding

**Rules:**
1. **Never an arbitrary value.** `margin-top: 13px` is a defect. Use the nearest scale step or add a
   token with a documented reason.
2. **Adjacent spacing steps relate, they do not stack.** Do not put `--space-8` padding on a card
   *and* `--space-8` margin between cards; the gap reads as 64px, not 32px.
3. **Spacing inside a component < spacing between components < spacing between sections.** Breaking
   this order destroys grouping — it is the single most common layout bug.
4. **Section rhythm belongs to `.os-section`, never to a page-specific rule.**
5. **Tailwind interstitial:** Tailwind's default spacing scale is also a 0.25rem base, so `p-4` and
   `--space-4` are both 16px by construction. Both are permitted; do not mix them within one
   component.

---

## 9. Layout and Grid System

### 9.1 The 12-column grid

- `--grid-columns: 12`, `--grid-gap: clamp(1rem, 2.2vw, 2rem)`
- Collapse behaviour: `.os-grid--4` and `.os-grid--3` → 2 columns below 1024px → 1 column below 768px
- `.os-grid--2` → 1 column below 768px
- Collapsing is explicit, never incidental `auto-fit` surprise

### 9.2 The asymmetric editorial split — the signature layout

Symmetric centred layouts are the default of every template. This system prefers deliberate
imbalance:

| Class | Ratio | Use |
|---|---|---|
| `.os-split--7-5` | 7fr / 5fr | Hero, primary + supporting |
| `.os-split--8-4` | 8fr / 4fr | Featured project, article + aside |
| `.os-split--5-7` | 5fr / 7fr | Media-led, reversed |

All splits collapse to one column below 1024px.

### 9.3 The `.os-project-card` grid

The signature list item is a fixed 5/7 media/body grid (7/5 when `.os-project-card--featured`).
It deliberately **diverges** from the split ratios: the card is a list, and list rhythm must be
uniform — asymmetry on every row would read as misalignment rather than intent.

### 9.4 Composition rules

- **Whitespace is structural.** A section with too little vertical space reads as a bug.
- **Alignment is sacred.** Every element aligns to a column edge or a container edge. Never centre an
  element in the middle of a 12-column grid just because it looks balanced.
- **Full-bleed** (`.os-full-bleed`) is permitted only for galleries and data-dense dashboard rows.
- **Never nest a container inside a container with inline padding** — the gutters double.

---

## 10. Container Dimensions

| Token | Value | Use |
|---|---|---|
| `--container-narrow` | 45rem / **720px** | Long-form article body |
| `--container-default` | 82.5rem / **1320px** | Primary page container — the default |
| `--container-wide` | 100rem / **1600px** | Galleries, CMS dashboard |
| `--container-content` | **65ch** | Prose measure cap (not a width in px) |
| `--container-full` | `none` | Full-bleed only |

**Rules:**
- `.os-container` is the **only** way to constrain page width. A page-specific `max-width` is a defect.
- One container per band. Nesting containers widens gutters and is prohibited.
- The container carries the gutter; children never add their own inline page padding.
- `--container-content` caps **text**, not layout — apply it to the prose element, not the wrapper.

---

## 11. Border and Radius System

### 11.1 The two border tokens — a critical distinction

| Token | Dark | Light | Contrast | Permitted for |
|---|---|---|---|---|
| `--color-border` | `#343D49` | `#DDE2E8` | **1.77:1** / 1.23:1 | **Decorative only.** Dividers, table row rules, same-surface card edges |
| `--color-border-strong` | `#656F7E` | `#828B99` | **3.83:1** / 3.24:1 | **Contrast-bearing.** Form controls, checkboxes, toggles, any control whose shape must be perceivable |

WCAG 2.2 §1.4.11 requires **3:1** non-text contrast for the visual boundary of a UI component.
`--color-border` cannot satisfy it and must never be used on an interactive control.

> **Why one grey cannot serve both themes:** a grey that clears 3:1 against white is visually heavy
> in light mode, and a grey that looks refined against `#0B0D10` is far too dark for white. The
> measurement is unambiguous — greys top out around 2.5–3.3:1 and the two ranges do not overlap.
> Hence two tokens, each correct for its own context. `--color-border-input` aliases
> `--color-border-strong` so the intent is explicit at every call site.

**Border widths:** `--border-width-hairline: 1px` (default), `--border-width-medium: 2px` (active
state, nav underline), `--border-width-heavy: 4px` (the neo-industrial accent rule — section openers
and destructive modal frames only).

**Rules:**
- Borders are **not** elevation. A border separates; a surface step elevates.
- Never combine a heavy border with a strong shadow on the same edge.
- A 4px rule is a **signal**: maximum one per section.

### 11.2 Radius

| Token | px | Use |
|---|---|---|
| `--radius-none` | 0 | Table cells, edge-to-edge media |
| `--radius-xs` | 4 | Badges, checkboxes, menu items, page links |
| `--radius-sm` | 6 | **Buttons**, inputs, alerts, toasts — the default control radius |
| `--radius-md` | 10 | Cards, table wrappers, terminals |
| `--radius-lg` | 14 | Modals, large panels |
| `--radius-xl` / `--radius-2xl` | 20 / 28 | Rare large surfaces |
| `--radius-full` | 9999px | Avatars, toggles, dots, meters — **never** on a rectangular control |

**Rules:**
1. **Controls are 6px, containers are 10px.** A button is never a pill.
2. **Nesting follows the inset rule:** child radius = parent radius − padding. A 10px card holding a
   6px control with 4px inset is correct; a 10px card with a 10px button inside 4px padding is
   visibly wrong.
3. **The chamfer is not a radius.** See §11.3.

### 11.3 The chamfer — signature device

| Token | Notch | Use |
|---|---|---|
| `--chamfer-sm` | 8px | Small feature chips |
| `--chamfer-md` | 16px | Feature surfaces, primary media, hero panels |
| `--chamfer-lg` | 24px | Large feature panels |

Implemented with `clip-path` on two opposite corners (top-left + bottom-right), which is what makes it
read as engineered rather than rounded.

**Rules:**
- **Never combine a chamfer with a large radius on the same corner.** The forms are mutually exclusive.
- `clip-path` removes the box's border, so a chamfered surface gets its edge from an outer wrapper
  (`.os-chamfer--outlined`) or from a background colour.
- A chamfer is a **feature** treatment. Applying it to list items, form controls, or anything repeated
  more than ~6 times per page turns a signature into noise.

---

## 12. Shadow and Elevation

### 12.1 Elevation is three-part

**surface step + border + shadow.** Shadow alone is insufficient in dark mode: a black shadow on a
near-black canvas is nearly invisible. Every elevation therefore changes the surface value *and*
applies a shadow.

| Level | Surface | Shadow | Use |
|---|---|---|---|
| **0 — canvas** | `--color-background` | `none` | Page |
| **1 — raised** | `--color-surface` + `--color-border` | `--shadow-e1` | Cards, table wrappers |
| **2 — interactive** | `--color-surface-hover` on hover | `--shadow-e3` + `--motion-lift` | Card hover |
| **3 — overlay** | `--color-surface-elevated` | `--shadow-e3` | Dropdowns, popovers, menus |
| **4 — modal** | `--color-surface-elevated` | `--shadow-e4` | Modals, dialogs, drawers |
| **5 — critical** | `--color-surface-elevated` | `--shadow-e5` | Command palette only (max 1 in product) |

### 12.2 Glow

`--shadow-glow` is a 1px brand ring plus a 24px soft glow. Permitted on **exactly one** element per
viewport — the focal point. Never on a list, never on more than one card, never on body text.

### 12.3 Focus ring

| Token | Value | Contrast |
|---|---|---|
| `--color-focus-ring` | `light-dark(#00707F, #00D4FF)` | 5.45:1 / 10.99:1 |
| `--focus-width` / `--focus-offset` | `2px` / `2px` | — |

Always applied as an **`outline`**, never as `box-shadow` or `border`, so it can never shift layout.
Verified ≥3:1 against every surface it can land on (background, surface, elevated).

**Rules:**
1. `:focus-visible` only — never remove the focus ring; never style `:focus` in a way that hides
   keyboard focus for pointer users.
2. Focus rings are **never** overridden per component. The global rule in `components.css` is the contract.
3. `--shadow-focus` exists for cases where an outline gets clipped (e.g. inside `overflow: hidden`).
   Using it requires a comment explaining why the outline was insufficient.

---

## 13. Iconography Standards

| Standard | Value |
|---|---|
| **Library** | Lucide (ISC licensed, stroke-only, 24×24 grid) |
| **Style** | Stroke only. `fill: none` on all icons. |
| **Stroke width** | `--icon-stroke-width: 1.5` (default), `2` for emphasis |
| **Colour** | `stroke: currentColor` **always** — an icon inherits its text colour |
| **Sizes** | `xs` 12 · `sm` 16 · `md` 20 · `lg` 24 · `xl` 32 · `2xl` 40 |

**Rules:**
1. **Never hardcode an icon fill.** An icon whose colour does not respond to its text colour is a defect.
2. **Every icon-only control carries an accessible name.** `aria-label` on the control,
   `aria-hidden="true"` on the SVG. A bare icon with no accessible name is a defect, not a warning.
3. **Decorative icons are `aria-hidden="true"`.** An icon that repeats adjacent text is decoration.
4. **Emoji are never icons.** No exception. They render differently on every platform, cannot inherit
   colour, cannot be sized precisely, and cannot be styled.
5. **Icon + text gap is `--space-2` (8px)**, supplied by the container's `gap` — never a margin.
6. **One library only.** No mixing icon sets; that is how a UI starts looking assembled rather than designed.
7. **Sizing is fixed to the scale.** No `width="17"` on an SVG.

---

## 14. Button Design Standards

### 14.1 Hierarchy — the decision table

| Variant | Class | Meaning | Budget |
|---|---|---|---|
| **Primary** | `.os-btn--primary` | The single most important action on this surface | **1 per view** |
| **Secondary** | `.os-btn--secondary` | A sibling of the primary, structural | ≤2 per view |
| **Accent** | `.os-btn--accent` | Highest emphasis; a conversion moment | **1 per page** |
| **Outline** | `.os-btn--outline` | Default non-destructive action | unlimited |
| **Ghost** | `.os-btn--ghost` | Tertiary action in dense UI (rows, toolbars) | unlimited |
| **Danger** | `.os-btn--danger` | Destructive; requires a confirmation dialog | per flow |
| **Danger quiet** | `.os-btn--danger-quiet` | Destructive but low-weight (inline row delete) | per row |
| **Link** | `.os-btn--link` | Inline navigation action | unlimited |

**If two primary buttons appear on one view, one of them is wrong.**

### 14.2 Anatomy (fixed)

Height, inline padding, gap, radius, font size, font weight, and transition are all set by `.os-btn`.
A button that does not use `.os-btn` is a defect, not a variation.

| Size | Height | Token |
|---|---|---|
| `--xs` | 28px | `--control-height-xs` |
| `--sm` | 32px | `--control-height-sm` |
| *(default)* | 40px | `--control-height-md` |
| `--lg` | 48px | `--control-height-lg` |
| `--xl` | 56px | `--control-height-xl` — hero CTA only |

### 14.3 States — all six are mandatory

| State | Implementation | Notes |
|---|---|---|
| Default | — | |
| Hover | `--_btn-bg-hover` | Must differ from default; verified for contrast |
| Active | `translateY(1px)` | Physical press feedback |
| Focus | global `:focus-visible` outline | Never suppressed |
| Disabled | `--color-surface-disabled` + `--color-text-disabled` | **Plus** `aria-disabled` where the control stays focusable |
| Loading | `.is-loading` | Label becomes transparent so **width does not change**; a spinner takes the icon slot |

### 14.4 Rules

1. **Never nest an interactive element inside a button.** An `<a>` inside a `<button>` is invalid.
2. **The label states the outcome.** "Delete permanently" and "Publish", never "OK" / "Submit".
3. **Loading never changes size.** Layout shift on click is a defect.
4. **Disabled is a last resort.** Prefer disabling with a visible reason, or validating on submit. A
   disabled button with no explanation is a dead end.
5. **Touch targets are ≥44×44px.** `--sm` and `--xs` buttons are desktop-density tools; on touch they
   need surrounding padding to reach 44px.
6. **One icon maximum** per button, on the leading side. Icon buttons use `.os-icon-btn`.

---

## 15. Form Design Standards

### 15.1 Anatomy — the mandatory structure

```
.os-field
 ├─ .os-field__label      (always present, always visible)
 ├─ .os-input             (control)
 ├─ .os-field__help       (optional, above the error)
 └─ .os-field__error      (shown on validation failure)
```

**Rules:**
1. **Labels are always visible.** Placeholder-as-label is prohibited — the label disappears the moment
   a user types, which is exactly when they need it.
2. **`for` / `id` are always paired.** A label not programmatically associated with its control is a defect.
3. **Help text comes before error text.** A user reads the instruction, then the failure.
4. **Errors are programmatically linked** via `aria-describedby` and flagged with `aria-invalid="true"`.
5. **Errors state the cause and the fix.** "Slug may only contain lowercase letters, numbers and
   hyphens" — not "Invalid input".
6. **Required fields are marked, optional fields are labelled.** `*` with `aria-hidden` on the glyph
   (the real signal is the `required` attribute); optional fields get a `// optional` micro-label.

### 15.2 Control geometry

| Size | Height | Use |
|---|---|---|
| `--sm` | 32px | Toolbar filters, table search |
| *(default)* | 40px | All forms |
| `--lg` | 48px | Standalone/hero forms |

Control radius is `--radius-sm` (6px). Control boundaries use `--color-border-input`
(= `--color-border-strong`) — see §11.1.

### 15.3 States

| State | Treatment |
|---|---|
| Default | `--color-border-input` boundary, `--color-surface-input` fill |
| Hover | Boundary holds (already contrast-bearing — do not lighten it) |
| Focus | 2px focus ring + `--color-border-brand` boundary |
| Invalid | `--color-border-danger` boundary + error row + `aria-invalid` |
| Disabled | `--color-surface-disabled`, `--color-text-disabled`, `cursor: not-allowed` |
| Read-only | `--color-surface-inset` fill (distinct from disabled — it is still readable and copyable) |

### 15.4 Field width

Field width communicates expected input length. `maxlength="60"` on a meta title with a full-width
input is a lie about the constraint. Size the control to the content: short codes are narrow, prose is full width.

---

## 16. Card Design Standards

**A card is a bordered surface, not a floating shadowed box.** Elevation comes from a surface step plus
a border; shadow only reinforces, never substitutes.

### 16.1 Anatomy

```
.os-card
 ├─ .os-card__media    (optional, edge-to-edge, first child)
 ├─ .os-card__header   (optional, bordered baseline)
 ├─ .os-card__body     (required)
 └─ .os-card__footer   (optional, `margin-top: auto` pins to bottom)
```

### 16.2 Variants

| Variant | Behaviour | Use |
|---|---|---|
| *(default)* | Static bordered surface | Content display |
| `.os-card--elevated` | `--color-surface-elevated` + `--shadow-e1` | Modals, raised panels |
| `.os-card--interactive` | Hover: border strengthens, lifts 2px, `--shadow-e3` | Clickable cards |
| `.os-card--inset` | `--color-surface-inset` | Nested/embedded content |

### 16.3 Rules

1. **One clickable target per card.** Use the stretched-link pattern (`.os-card__link::after`) rather
   than nesting anchors — nested interactive elements are invalid HTML and hostile to screen readers.
2. **Whole-card hover must be reachable by keyboard.** `.os-card--interactive:focus-within` supplies the
   equivalent state.
3. **Cards in a grid are equal height** and align their footers. Ragged card heights read as a bug.
4. **Do not nest a card in a card** with the same surface value — use `--inset` for the inner one.
5. **Cards are not the default layout.** A page of boxes is a template. Prefer rules, dividers, and
   whitespace for content that does not need containment.

---

## 17. Navigation Standards

### 17.1 Navbar

- Height `--header-height` (72px desktop / 56px mobile), sticky at `--z-sticky`, blurred background
  with `color-mix` so content scrolling under it stays hinted at.
- Current page is signalled by `aria-current="page"` — the CSS derives the 2px brand underline from
  that attribute. **Never a `.active` class**; attribute-driven state is the contract.
- Mobile navigation is a **full-height panel**, not a dropdown. Dropdown menus with many items are a
  touch-usability trap.

### 17.2 Sidebar (CMS)

- Width `--sidebar-width` (264px); collapses to 64px icon rail with `.os-admin.is-collapsed`.
- Grouped by task, with `.os-menu__label`-style group headers. Order by frequency of use, not alphabet.
- Current page again via `aria-current="page"`.
- Below 1024px the sidebar is removed from layout, not merely hidden — it must be reachable from the topbar.

### 17.3 Breadcrumbs

- Reflect the hierarchy, not the click history.
- The last item is `aria-current="page"` and is not a link.
- Separators are `aria-hidden` — a screen reader should not announce "slash".

### 17.4 Footer

- Four columns: identity (2fr) + three link groups. Collapses to two, then one.
- Contains the one place a link may be repeated from the nav: the footer is a utility surface.
- `--color-background-subtle` with a `--color-border` top rule.

### 17.5 Rules for all navigation

1. **Mobile targets ≥44px.** `.os-mobile-nav__link` uses `--space-4` vertical padding for exactly this.
2. **Never more than 6 top-level items.** More means an information architecture problem, not a nav problem.
3. **Every nav landmark is labelled** (`aria-label="Breadcrumb"`, `aria-label="Main"`), because a page
   with three `<nav>` elements is ambiguous without names.
4. **The skip link is mandatory and is the first focusable element.** It targets `#main`.

---

## 18. Table and Data Visualization Standards

### 18.1 Data table

| Standard | Value |
|---|---|
| Header height | `--table-header-height` 2.75rem, sticky |
| Row height | `--table-row-height` 3rem (`.os-table--compact` = 2.5rem) |
| Cell padding | `--space-4` inline |
| Row separation | `--color-border-subtle` 1px baseline |
| Numeric cells | `.os-table__num` — right-aligned, `tabular-nums`, mono |
| Row hover | `--color-surface-hover` |
| Row selected | `aria-selected="true"` → `--color-brand-primary-surface` |

**Rules:**
1. **A caption is mandatory** — `.os-visually-hidden` where a heading already names the table.
2. **Numeric columns are right-aligned and tabular.** Left-aligned numbers cannot be compared by scanning.
3. **Header labels are uppercase mono micro-labels** (`.os-label` treatment).
4. **Selection uses `aria-selected`**, not a class — the visual state is derived from the attribute.
5. **Never a data table for layout.** Use grid.
6. **Empty state replaces the table body**, never an empty `<tbody>` with headers only. See §23.
7. **Pagination is server-side** and expressed as query parameters, so a filtered view is linkable.
8. **Bulk actions appear only when rows are selected** and state the count (`2 selected`).

### 18.2 Data visualisation

The **only** sanctioned chart form is a **horizontal labelled bar** (`.os-chart-bars`).

**Rationale:** bars are readable without a legend, comparable without colour, accessible as an ordered
list, and honest about values. They also fit the editorial column grid.

**Prohibited:** pie charts, donut charts, 3D anything, dual axes, treemaps, radar charts, more than two
colours, gradients under data, animated entrances that delay reading.

**Rules:**
- Maximum **two** colours: `--color-brand-primary` (primary series), `--color-brand-secondary` (comparison).
- Every bar carries a **label and a tabular value**. A bar you must hover to read is a failure.
- Axes start at zero. No exceptions.
- Charts are semantically a list or a table; never a `<canvas>` with no text alternative.

---

## 19. Responsive Breakpoints

| Token | Width | Target | Layout behaviour |
|---|---|---|---|
| `--breakpoint-xs` | 480px | Large phone | Single column; reduced display type |
| `--breakpoint-sm` | 640px | Landscape phone | Single column |
| `--breakpoint-md` | 768px | Tablet | 2-column grids; splits still collapse |
| `--breakpoint-lg` | 1024px | Small laptop | Sidebar appears; 3-col grids; splits activate |
| `--breakpoint-xl` | 1280px | Desktop | Full layout |
| `--breakpoint-2xl` | 1536px | Wide desktop | Larger gutters |
| `--breakpoint-3xl` | 1760px | Editorial | Full-bleed galleries |

These are literal Tailwind `@theme` values in `design-tokens.css`. Tailwind must emit the values
directly into `@media`; a `var()` alias there is invalid CSS and cannot act as a responsive breakpoint.

**Rules:**
1. **Mobile-first.** Base styles are single-column; complexity is added at `min-width`.
2. **The 320px floor must work.** A horizontal scrollbar at 320px is a defect.
3. **Breakpoints are structural, not device-specific.** Break where the *content* breaks, and only at
   these seven widths.
4. **Never hide content to fit.** Reflow it. The only legitimate hides are decorative texture
   (`.os-grid-overlay`) and print-irrelevant chrome.
5. **Test at 320 / 768 / 1024 / 1440 / 2560.** Those five catch every real-world failure.
6. **`dvh` over `vh`** for full-height surfaces — mobile browser chrome makes `vh` wrong.

---

## 20. Dark and Light Mode

### 20.1 The mode is carried by the token

Dark and light are **not** separate stylesheets. Each colour token declares both values inline via CSS
`light-dark()`:

```css
--color-surface: light-dark(#FFFFFF, #12161C);  /* light() , dark() */
```

Mode is then a single `color-scheme` switch:

| Selector | `color-scheme` | Result |
|---|---|---|
| `:root` (no attribute) | `dark light` | Follows the OS |
| `[data-theme="dark"]` | `dark` | Forced dark |
| `[data-theme="light"]` | `light` | Forced light |

### 20.2 Why this architecture

- **Exactly one source of truth per colour.** No duplicated `[data-theme]` override blocks, so the two
  themes cannot drift apart.
- **Components need almost no `dark:` variants.** A `dark:` utility should be treated as a code smell
  that usually means a token is missing.
- **Enforced automatically:** `_validate.mjs` fails if `components.css` contains a `[data-theme=...]`
  selector or any raw hex value.

### 20.3 Theme resolution and the no-flash guarantee

A ~0.3 KB inline script in `<head>` reads `localStorage` and sets `data-theme` **before first paint**.
Without it the page renders in the OS theme and then snaps — a visible flash. If storage is unavailable
(private mode, blocked cookies) the script fails silently and the OS preference applies.

Stored key: `os-theme`, values `"dark"` | `"light"` | absent = follow OS.

### 20.4 Rules

1. **Dark is the brand default** and the no-JS fallback; light is a full peer, not a degraded variant.
2. **Every component must be verified in both modes.** The Style Guide has a toggle for exactly this.
3. **Never pair a light text colour with a light fill** — see §4.5. This is the most common dark-mode bug,
   which is why `--color-text-on-*` tokens exist per family rather than a single "inverse".
4. **Shadows are weaker in light mode.** `--color-shadow` is mode-aware; the geometry is not.
5. **`prefers-contrast: more`** promotes hairlines to `--color-border-strong` and secondary text to
   primary, handled globally in the token layer.
6. **Print is light.** `@media print` hides navigation, toasts, and the mobile panel.

---

## 21. Animation and Transition Standards

### 21.1 Duration scale

| Token | ms | Use |
|---|---|---|
| `--duration-instant` | 80 | Colour / border changes on hover |
| `--duration-fast` | 150 | **Default** — most transitions |
| `--duration-normal` | 250 | Reveals, modal entry |
| `--duration-slow` | 400 | Page-level transitions, image zoom |
| `--duration-slower` | 700 | Loops only (spinners, pulse) |

### 21.2 Easing

| Token | Curve | Use |
|---|---|---|
| `--ease-standard` | `cubic-bezier(0.2, 0, 0, 1)` | Default UI motion |
| `--ease-out` | `cubic-bezier(0.16, 1, 0.3, 1)` | Enter / reveal |
| `--ease-in` | `cubic-bezier(0.4, 0, 1, 1)` | Exit / dismiss |
| `--ease-spring` | `cubic-bezier(0.34, 1.56, 0.64, 1)` | Confirmation only |

### 21.3 The justification rule

**Every animation must communicate state, hierarchy, or causality.** If the interface makes equal sense
without it, delete it. Decorative motion is prohibited.

Sanctioned purposes, and nothing else:

| Purpose | Example |
|---|---|
| **State change** | Hover feedback, focus, active press |
| **Causality** | A modal entering from the direction of the control that opened it |
| **Continuity** | A shared element transitioning between two positions |
| **Progress** | Spinner, progress bar, skeleton shimmer |

### 21.4 Rules

1. **Animate `transform` and `opacity` only.** Animating `width`, `height`, `top`, or `left` triggers
   layout and will jank. The one sanctioned exception is `inline-size` on `.os-progress__bar`.
2. **`prefers-reduced-motion` is honoured globally** in the token layer: durations collapse to `0.01ms`
   and `--motion-lift` becomes `none`. **A component must still reach its final visible state** —
   `[data-reveal]` is explicitly forced visible under reduced motion, because a zeroed transition on a
   permanent `opacity: 0` would hide content forever.
3. **No parallax, no auto-playing carousels, no cursor-following effects, no scroll-jacking.**
4. **Stagger is capped at 6 items** (`--motion-stagger-step` 60ms). Beyond that a cascade reads as lag.
5. **Entrance animations never delay reading.** Content is visible within 250ms or it is not animated.
6. **Never loop an animation unless it signals ongoing work** (spinner, skeleton, live pulse).
7. **The complete keyframe set is 8.** Adding a ninth requires a documented justification:
   `os-spin`, `os-pulse`, `os-shimmer`, `os-fade-in`, `os-modal-in`, `os-menu-in`, `os-toast-in`, `os-blink`.

---

## 22. Accessibility Standards

**Target: WCAG 2.2 Level AA.** Not "AA where practical" — AA, with two documented exceptions (§4.3, §22.5).

### 22.1 Contrast — the measured contract

| Requirement | Minimum | Status |
|---|---|---|
| Body text (`--color-text-primary` / `secondary`) | 4.5:1 | ✅ 5.65–18.31 across both modes |
| Large text (≥18.66px bold / ≥24px) | 3:1 | ✅ |
| UI component boundaries (§1.4.11) | 3:1 | ✅ via `--color-border-strong` |
| Focus indicator | 3:1 vs adjacent colours | ✅ 5.45 / 10.99 |
| Solid-fill button text | 4.5:1 | ✅ all 16 pairings verified |
| Text on tinted status surfaces | 4.5:1 | ✅ 4.55–8.21 |
| `--color-text-tertiary` in light mode | 4.5:1 | ⚠️ **4.21** — bounded exception, §4.3 |
| Disabled controls | exempt (§1.4.3) | by design |

These numbers are **recomputed on every validation run** (`_validate.mjs` §6) directly from the token
values. If someone changes a token and breaks contrast, the check fails. The claim is not documentation
— it is a test.

### 22.2 Keyboard

1. **Everything interactive is reachable by Tab**, in an order following the visual reading order.
2. **Focus is always visible** — 2px outline, 2px offset, never suppressed.
3. **The skip link is the first focusable element** on every page and targets `#main`.
4. **Escape closes** every overlay (modal, drawer, dropdown).
5. **Focus is trapped inside modals** and **returned to the trigger** on close.
6. **No keyboard traps.** A user can always Tab out of anything.
7. **No positive `tabindex`.** Order comes from DOM order.
8. **Arrow-key navigation** within composite widgets (tabs, menus, radio groups), per ARIA Authoring Practices.

### 22.3 Semantics and screen readers

1. **Native elements first.** `<button>`, `<a href>`, `<nav>`, `<table>` before any ARIA. The first rule of
   ARIA is not to use ARIA.
2. **Every icon-only control has an accessible name.** `aria-label` on the control, `aria-hidden` on the SVG.
3. **State is expressed with ARIA attributes**, and CSS derives visuals from them:
   `aria-current="page"`, `aria-selected`, `aria-expanded`, `aria-pressed`, `aria-invalid`, `aria-disabled`.
   **No `.active` / `.open` / `.selected` classes for state.**
4. **Live regions announce async changes** — the toast region is `aria-live="polite"`; validation errors are
   announced on submit.
5. **Decorative imagery is `aria-hidden`** (`.os-grid-overlay`, index numerals, texture, separator glyphs).
6. **Images have meaningful `alt`.** Empty `alt=""` for decorative, never a filename, never "image of…".
7. **Landmarks are labelled** when repeated (`aria-label="Breadcrumb"`).

### 22.4 Form accessibility

- Every control has a programmatic label (`<label for>`).
- `aria-invalid="true"` + `aria-describedby` pointing at the error element on failure.
- Errors are announced, and focus moves to the first invalid field on submit.
- Autocomplete tokens (`autocomplete="email"`) on identity fields — WCAG 1.3.5, not a nicety.
- Grouped controls use `<fieldset>` + `<legend>`.

### 22.5 Documented exceptions

| Exception | Value | Justification & bound |
|---|---|---|
| `--color-text-tertiary` light mode | 4.21:1 | Only for ≥18.66px bold / ≥24px text or non-essential metadata. Essential copy uses `--color-text-secondary`. |
| Disabled control text | ~2:1 | WCAG 1.4.3 explicitly exempts inactive components; disabled controls also carry `aria-disabled` so state is never colour-only. |

---

## 23. CMS Design Guidelines

The CMS is where the design system earns its keep: dense, repetitive, used daily. It uses the **same
tokens** as the public site — **there is no "admin theme."**

### 23.1 Density and structure

- Layout: `.os-admin` — sidebar 264px + fluid main. Content padding `--space-6`, section gap `--space-6`.
- **Everything is `--text-body-sm` (14–15px) or smaller.** Reduce size, never clarity: the admin uses the
  same contrast tokens as the public site.
- Sidebar groups by task (Content, Media, Users, System), ordered by frequency of use — not alphabetically.

### 23.2 The dashboard

- Metric tiles (`.os-metric`) show **label, value, delta**: label as a mono micro-label, value as
  `--text-heading-xl` with `tabular-nums`.
- Deltas carry a **direction glyph plus text**, never colour alone (`▲ 3 this month`).

### 23.3 Tables are the primary surface

Most CMS screens are a data table. Requirements:
- Sticky header, 3rem rows, tabular numbers right-aligned (§18.1).
- Toolbar above: search, filters (status, date range), bulk actions right-aligned.
- **Server-side pagination via query params** — every filtered view is a shareable URL.
- Row actions are icon buttons with accessible names; a destructive row action is `--danger-quiet`.
- Empty state replaces the table body (§23.5).

### 23.4 Forms and editing

- Long forms are split with `.os-fieldset` and mono legends. A 20-field single column is hostile.
- **Destructive actions always open a ConfirmationDialog** naming the consequence and listing side
  effects ("3 media files and 2 articles reference this project").
- **Optimistic UI is prohibited for writes.** Show a loading state, wait for the server, then report. A CMS
  that claims success before the write lands is a data-loss generator.
- Autosave, where present, shows a real timestamp and an explicit saved/unsaved indicator.
- Rich text is sanitised server-side on write **and** on render.

### 23.5 Mandatory states

Every CMS surface implements all five. A missing one is a defect.

| State | Component | Requirement |
|---|---|---|
| **Loading** | `.os-skeleton` | Skeletons match the final layout's shape — no spinners for page-level loads |
| **Empty** | `.os-empty-state` | Explains why it is empty **and** offers the primary action |
| **Error** | `.os-error-state` | States the cause and includes a request ID for support |
| **Success** | `.os-toast` | Confirms the write with a concrete noun ("Project published") |
| **Permission denied** | `.os-empty-state` | Explains the role required — never a bare 403 |

### 23.6 Media

- The dropzone states constraints **before** upload: formats, max size, max dimensions.
- Upload items show per-file progress, and a **rejected** state with the reason.
- The picker is a selection grid; selected tiles use `aria-selected` + a visible 2px ring.
- Every media record exposes filename, dimensions, format, and size. Alt text is a required field.

### 23.7 Content authoring guardrails

- Slug is auto-generated from the title and shown; edits are explicit.
- SEO fields show live character counters with the limit visible.
- Publish is a deliberate act with confirmation, not a toggle that silently goes live.
- The CMS must never let an author mistake placeholder content for real content — unpublished is visible.

---

## 24. Component Usage Guidelines

### 24.1 The selection order

Before writing any markup, walk this order and stop at the first hit:

1. **Does an `.os-*` component already do this?** Use it.
2. **Does a documented composition of components do this?** Compose them.
3. **Is this genuinely new?** Then it is a new component — document it in `COMPONENT_LIBRARY.md`
   **before** building it.

Writing a one-off styled `<div>` is a defect, not a shortcut.

### 24.2 Composition rules

| Rule | Reason |
|---|---|
| **Compose, never extend by copy** | Copying a component creates two sources of truth that will diverge |
| **Never reach into a component's internals** from outside | Breaks encapsulation; add a modifier instead |
| **One responsibility per component** | A component that fetches, validates, and renders is three components |
| **Props down, events up** | No shared mutable state between siblings |
| **Business logic never lives in a presentation component** | Blade/JSX renders; Actions/Services decide |
| **No component queries the database** | Views receive data; they do not retrieve it |

### 24.3 Slot and modifier discipline

- Modifiers change **appearance or emphasis**, never behaviour. Behaviour is a prop or attribute.
- A new modifier must be justified by at least **three** real call sites. One-off styling belongs at the
  call site, not in the component.
- Maximum **three** variants per component. Beyond that it is two components wearing a trench coat.

### 24.4 Interaction contract

Every interactive component must define **all** of these, or explicitly document why one is absent:
default, hover, active, focus-visible, disabled, loading, error, empty.

A component that only defines its happy state is incomplete, not minimal.

### 24.5 Verification

Every component is rendered in `docs/styleguide/index.html` in **both themes** and, where relevant, in all
states. The Style Guide is the acceptance test: if a component is not in it, it is not part of the design
system.

---

## 25. Anti-Generic AI Design Rules

These rules exist because the default output of an AI-assisted build is recognisable — and being
recognisable as auto-generated is a brand failure.

### 25.1 Banned outright — no exceptions

| Banned | Why |
|---|---|
| **Purple→blue→pink gradient hero** | The single most recognisable AI-generated signature |
| **Glassmorphism on every surface** | Destroys hierarchy — if everything is translucent, nothing is foreground |
| **Emoji as icons** (🚀 ✨ 💡) | Inconsistent cross-platform, cannot inherit colour, cannot be sized (§13.4) |
| **Floating blob / mesh / wave backgrounds** | Decoration with no informational role |
| **Centred hero: headline + 3-word slogan + two buttons** | The template default. Use the asymmetric editorial split |
| **`border-radius: 9999px` on rectangular controls** | Pill buttons in a data-dense UI read as unresolved |
| **`box-shadow: 0 20px 40px rgba(0,0,0,.3)` on everything** | Elevation without a surface step (§12.1) |
| **Stock 3D illustrations / isometric people** | Adds no information |
| **Lorem ipsum, "Acme Corp", "John Doe"** | Placeholder content hides layout failure. Real content, or an empty state |
| **Generic microcopy** ("Welcome to our website!") | Says nothing. State what happened and what to do |
| **Animating everything on scroll** | Motion without meaning (§21.3) |
| **"Icon + heading + paragraph" ×3 in a row** | The feature-grid template. Three identical cards is not a layout |
| **Reducing opacity to indicate disabled** | Fails contrast, and conflates state with hierarchy. Use the disabled tokens |
| **Centred body text** | Destroys the left reading edge |
| **More than 2 sans families** | This system uses 3 families, one of which is heavily rationed (§6.1) |

### 25.2 Required instead — the affirmative checklist

No screen is finished until every box is true:

- [ ] **Asymmetry present.** At least one section uses a 7/5 or 8/4 split.
- [ ] **A mono micro-label appears** and says something specific and real.
- [ ] **Exactly one accent moment** (`--color-brand-accent`) on the whole viewport.
- [ ] **Real content** — actual project names, actual numbers, actual dates.
- [ ] **Hierarchy from space and rule**, not from boxes and shadows.
- [ ] **Every async region has loading + empty + error** implemented.
- [ ] **Tabular numerals** on every stat, table cell, and metric.
- [ ] **Both themes verified.**
- [ ] **Keyboard-navigable end to end**, with a visible focus ring at every stop.
- [ ] **Nothing decorative remains** — if an element can be deleted without loss of meaning, delete it.

### 25.3 The recognisability test

Show a screenshot cold to someone who has seen the site once before. They should be able to say *which
site it is* without reading the logo. If they cannot, the design has no identity. If they can identify it
but cannot read it, the design has no discipline. **Both must be true at once.**

### 25.4 Governance

1. **Any deviation from this document requires a written entry** in the change log below, with the reason
   and the scope of the exception.
2. **The tokens are the enforcement mechanism.** If a rule can be enforced by removing an option — e.g.
   clearing Tailwind's default palette so `bg-blue-500` cannot exist — enforce it there rather than relying
   on discipline.
3. **Finding that a rule is wrong is a legitimate outcome.** But it changes this document first, then the
   tokens, then the code. Silent drift is the only prohibited outcome.

---

## Change Log

| Date | Change | Reason |
|---|---|---|
| 2026-08-31 | Initial system defined (Phase 0.1) | Foundation gate |
| 2026-08-31 | Corrected 3 button contrast failures; added `--color-text-on-{solid,secondary,accent,overlay}`; defined breakpoints as literal Tailwind `@theme` tokens | Measured failures: dark danger 2.89:1, light accent 1.41:1, secondary hover 4.47:1; CSS variables cannot be used as `@media` breakpoints |

---

*Next: `COMPONENT_LIBRARY.md` → `ARCHITECTURE.md` → `DATABASE_SCHEMA.md` → `PROJECT_STRUCTURE.md` →
`CLEAN_CODE_STANDARDS.md`.*
