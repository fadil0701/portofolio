# IMPLEMENTATION_APPROVAL.md

**Phase 0.8 — Implementation approval request**

| | |
|---|---|
| **Status** | ✅ **Phase 1 complete — awaiting approval for Phase 2** |
| **Requested by** | Engineering |
| **Date** | 2026-11-10 |
| **Gate result** | Phase 0.7 PASSED; Phase 1 verified 2026-11-10 |

> **This document is the stop point.** Phase 1 installed the framework and development toolchain only. Product
> implementation (domain models, schema migrations, public pages, and CMS features) remains blocked until Phase 2
> is approved.

---

## 1. Design System Summary

**OBSIDIAN SIGNAL** — *Premium Technology × Swiss Editorial × Neo-Industrial Engineering*

**Thesis:** an editorial magazine laid out by an engineer, printed on a precision surface. A quasar: an
intensely luminous point against a near-black field.

### Palette

| Role | Dark | Light | Verified contrast |
|---|---|---|---|
| Brand primary (the signal) | `#00D4FF` | `#00707F` | 10.99 / 5.79 |
| Brand secondary (structural) | `#3678FF` | `#2159D8` | fill only — **4.13:1, not a text colour** |
| Brand accent (once per viewport) | `#C6F277` | `#4F6B00` | 15.17 / 5.75 |
| Success | `#A3D977` | `#2F6B21` | 11.81 / 6.09 |
| Warning | `#F4BE5E` | `#8A5A00` | 11.46 / 5.58 |
| Danger | `#F16F75` | `#C0373F` | 6.74 / 5.12 |
| Info | `#00D4FF` | `#00707F` | 10.99 / 5.45 |

### Typography — three families, each with one job

| Token | Family | Job | Budget |
|---|---|---|---|
| `--font-display` | Geist Variable | Headings, UI, numerals | unrestricted |
| `--font-mono` | Geist Mono Variable | Code, data, micro-labels | unrestricted |
| `--font-editorial` | Instrument Serif | Pull-quotes, index numerals | **max 2 per page** |

**Scale:** 13 fluid steps, `clamp()`-generated between 320px and 1440px (11px → 104px), each paired 1:1 with its
own leading and tracking value.

### Structural system

| System | Values |
|---|---|
| Spacing | 4px base + a 2px engineering step; `--space-section-y` is fluid |
| Radius | 4 / 6 / 10 / 14 / 20 / 28px + `--radius-full` |
| Chamfer (signature) | 8 / 16 / 24px notched corner via `clip-path` |
| Elevation | 6 levels, each = surface step **+** border **+** shadow |
| Motion | 5 durations, 4 easings, an 8-keyframe closed set |
| Breakpoints | 7, from 480px to 1760px |

### Signature devices — the four things that make it recognisable

1. **Signal rule** — a 4px brand rule with tick marks, opening a major section
2. **Chamfer** — a notched corner replacing a large radius on feature surfaces
3. **Mono micro-label** — uppercase, 0.085em tracked, and it says something real
4. **Editorial split** — asymmetric 7/5 and 8/4 grids instead of centred symmetry

### Accessibility

**WCAG 2.2 AA**, verified by computation rather than assertion:

| Requirement | Result |
|---|---|
| Body text | 5.65:1 – 18.31:1 across both modes ✅ |
| UI boundaries (§1.4.11) | 3.83:1 / 3.24:1 via `--color-border-strong` ✅ |
| Focus indicator | 5.45:1 / 10.99:1 ✅ |
| Solid-fill control text | **16/16 pairings ≥ 4.5:1** ✅ |
| Status tints | 4.55:1 – 8.21:1 ✅ |
| Documented exceptions | 2, both bounded and justified |

### Anti-generic

15 patterns banned outright — AI-gradient heroes, glassmorphism everywhere, emoji as icons, floating blobs, the
centred three-word hero, pill radius on controls, shadows without surface steps, stock 3D, placeholder content,
generic microcopy, scroll-animation on everything, the three-card feature grid, opacity-for-disabled, centred
body text, and more than two sans families.

**Enforcement:** Tailwind's default colour palette is **deleted**, so `bg-blue-500` cannot exist.

---

## 2. Planned Folder Structure

```
portofolio-fadil/
├── app/
│   ├── Actions/          # 1 class = 1 use case (Auth · Cms · Contact · Portfolio)
│   ├── Console/          # scheduled publishing, pruning, sitemap
│   ├── Contracts/        # MediaStorage · HtmlSanitiser — the only substitution seams
│   ├── DTOs/             # explicit data contracts between layers
│   ├── Enums/            # domain vocabulary (11 enums)
│   ├── Exceptions/       # Domain/* + one central Handler
│   ├── Http/             # Controllers · Middleware · Requests · Resources
│   ├── Jobs/             # queued work: ids only, idempotent
│   ├── Models/           # 17 models — relations, casts, scopes
│   ├── Policies/         # the authorization AUTHORITY (4 policies)
│   ├── Providers/
│   ├── Queries/          # read-side composition, one per screen
│   ├── Services/         # only for real orchestration or an external boundary
│   ├── Support/          # Blocks registry · Logging redaction
│   └── View/             # Blade class-components + composers
├── config/               # 12 files, one concern each
├── database/             # migrations · factories · seeders · schema snapshots
├── docs/
│   ├── styleguide/index.html          ✅ Component Showcase
│   ├── QUALITY_GATE.md                ✅
│   └── IMPLEMENTATION_APPROVAL.md     ✅
├── lang/                 # en/ · id/
├── public/               # document root — index.php + built assets only
├── resources/
│   ├── css/              ✅ design-tokens.css · components.css · app.css
│   ├── js/               # app.js + modules/ (one per non-trivial Alpine component)
│   └── views/            # layouts · components/{foundation,navigation,ui,portfolio,cms}
│                         # · public · admin · auth · errors
├── routes/               # web · admin · console · api
├── tests/                # Feature · Unit · Architecture
├── _validate.mjs         ✅ design-system validator (76 checks)
└── 7 governance documents ✅
```

**Full detail:** `PROJECT_STRUCTURE.md`.

---

## 3. Application Architecture

**Pattern:** Laravel-native **layered modular monolith**. Dependencies point **downward only**.

```
CLIENT (server HTML + Alpine islands)
   │
PRESENTATION     app/Http · resources/views     → translate HTTP ⇄ domain. No business rules.
   │
APPLICATION      app/Actions · app/Jobs         → one use case per class, atomic, transactional.
   │
DOMAIN           app/Models · Enums · Policies  → the rules of the business.
   │
INFRASTRUCTURE   app/Support · Contracts        → the outside world, behind an interface.
```

**Stack:** Laravel 12 · PHP 8.4 · Blade · Alpine.js 3 · Tailwind CSS 4 · MySQL 8 · Fortify · Pest.

**Two surfaces, one app:** the public site and the CMS share one domain layer, one policy layer, and one design
system — there is no separate admin theme.

**Enforcement:** the layering is not a diagram. `tests/Architecture/*` fails the build if a controller touches the
database, if a model imports the HTTP layer, or if a controller action exceeds 12 lines.

**Full detail:** `ARCHITECTURE.md` (12 sections).

---

## 4. Reusable Component Library

**53 canonical CSS primitive selectors**, defined in `components.css` and represented in the Style Guide. The
families below are organizational groupings, not additive counts: some entries are compound components or
compositions built from multiple primitive selectors.

| Family | Components and compositions |
|---|---|
| **Foundation** | container, section, stack, row, grid, split, divider, typography, icon, badge, avatar, dot |
| **Navigation** | navbar, nav link, mobile navigation, breadcrumb, footer, sidebar, admin shell, social links, skip link |
| **UI** | button, icon button, input, textarea, select, checkbox/radio, toggle, fieldset, field, card, alert, skeleton, empty state, error state, spinner, progress, modal/dialog, dropdown/menu, tooltip, tabs, accordion, pagination, toast, link, timeline |
| **Data & CMS** | table, toolbar, media tile, dropzone, upload item, metric, prose, status badge, revisions |
| **Portfolio** | hero, project card, skill group, expertise card, experience timeline, terminal, architecture diagram, chart bars |
| **Signature** | chamfer, signal rule, grid overlay, grain |

**Contract per component:** purpose · structure · variants · states · accessibility requirements · dependencies.
Every interactive component defines all eight states (default, hover, active, focus-visible, disabled, loading,
error, empty) — or documents why one is absent.

**Two-layer model:** CSS owns the *anatomy* (fixed height, padding, focus ring). Blade owns the *meaning* (loops,
conditionals, ARIA wiring). Blade components compose primitives; primitives never depend on Blade.

The exact 53-selector verification set is maintained by `_validate.mjs`; family membership is deliberately not
counted as a separate total because compound controls (for example, tabs and form fields) span multiple selectors.

**Full detail:** `COMPONENT_LIBRARY.md` (sections A–I).

---

## 5. Database Schema

**31 tables** — 23 domain + 8 framework. InnoDB, `utf8mb4`, bigint PKs never exposed publicly (routes use slugs).

| Group | Tables |
|---|---|
| **Identity** | `users`, `profiles` |
| **Projects** | `projects`, `technologies`, `project_technology`, `project_media`, `project_tag` |
| **Articles** | `articles`, `tags`, `article_tag` |
| **Pages & narrative** | `pages`, `experiences`, `skill_groups`, `skills`, `services`, `testimonials` |
| **Media** | `media`, `media_conversions` |
| **CMS support** | `contact_messages`, `activity_logs`, `content_revisions`, `settings`, `newsletter_subscribers` |
| **Framework** | sessions, cache, jobs, failed_jobs, notifications, etc. |

**11 enums** carry the domain vocabulary: `UserRole`, `ContentStatus`, `ContentFormat`, `ProjectType`,
`TechnologyCategory`, `TechnologyUsage`, `SkillLevel`, `Availability`, `MediaProcessingStatus`, `MessageStatus`,
`PageTemplate`.

**Content lifecycle:** `draft → in_review → scheduled → published → archived`, enforced in the domain layer — the
UI only ever offers valid next states.

**Key schema decisions:**

| Decision | Why |
|---|---|
| `VARCHAR(32)` + PHP enum cast, not native MySQL `ENUM` | Adding a case is a code change, not a migration |
| `project_technology.usage` (`primary` / `supporting`) | Lets a card show 2–3 headline technologies instead of a wall of badges |
| `skills.proficiency` **and** `skills.level` | The bar is decorative; the level is what a screen reader announces |
| `experiences.achievements` as JSON | A presentation list, never queried — a child table would add a join for nothing |
| `media.hash` UNIQUE dedup | The same image uploaded twice costs disk once |
| `content_format` enum on content | HTML now, Markdown possible later, no migration |
| `settings.type` | Without it, a settings table becomes stringly-typed and `"false"` is truthy |
| `activity_logs` append-only, redacted before insert | A secret can never reach the audit trail |
| Soft deletes on all content | Hard deleting would break the audit trail's FK history |

**Full detail:** `DATABASE_SCHEMA.md`.

---

## 6. Development Roadmap

### Phase 1 — Foundation install *(no product features)*
Toolchain: Laravel 12 install · Vite + Tailwind 4 wiring (`app.css` becomes live) · fonts self-hosted ·
Pint · PHPStan level 6 · Pest · CI with all 5 gates · `docker`-free Laragon dev flow.
**Exit:** `npm run build` produces ≤45KB gzipped CSS, `_validate.mjs` still passes, `/up` returns 200.
**Result:** ✅ Complete. Production CSS is 15.85KB gzipped; foundation validation, Pest, Pint, PHPStan, and `/up`
have been checked locally.

### Phase 2 — Domain & data
All 23 domain migrations · 11 enums · 17 models with casts, relations and scopes · factories + seeders ·
4 policies · architecture tests.
**Exit:** migrations reversible, factories seed a realistic full dataset, policy matrix tested.

### Phase 3 — Design system in Blade
Blade class-components for all 53 canonical primitives · layouts (public + admin) · error pages (403/404/419/429/500/503) ·
a Blade-rendered twin of the Style Guide.
**Exit:** every component renders in both themes in the Blade Style Guide.

### Phase 4 — Public site
Home · Work index + detail · Writing index + detail · About · Contact · generic CMS pages with the block
registry · SEO (meta, OG, JSON-LD, sitemap, robots) · performance budget verification.
**Exit:** Lighthouse ≥95 across all four categories at 320px and 1440px, both themes.

### Phase 5 — CMS
Auth (Fortify + 2FA) · dashboard · Projects CRUD with the full lifecycle · Articles · Media library with picker
and conversions · Messages · Settings · Users · activity timeline · content revisions.
**Exit:** the owner can run the entire site without touching code; every write is audited.

### Phase 6 — Hardening & launch
Rate limiting · CSP with nonces verified · security headers · logging channels + PII redaction · backup +
**restore rehearsal** · deploy pipeline with rollback · health checks + alerts · seed real content · go live.

### Timeline

| Phase | Scope | Complexity |
|---|---|---|
| 1 | Foundation install | Low |
| 2 | Domain & data | Medium |
| 3 | Design system in Blade | **High** (53 canonical primitives) |
| 4 | Public site | Medium |
| 5 | CMS | **High** |
| 6 | Hardening & launch | Medium |

**Order is fixed.** Each phase's exit criteria gate the next — no CMS work begins before the Blade component
layer exists, because otherwise the CMS would invent its own components and the design system would fork.

---

## 7. Key Technical Decisions and Trade-offs

Ranked by how much they shape everything downstream.

| # | Decision | Alternative rejected | Why | Cost accepted |
|---|---|---|---|---|
| 1 | **Server-rendered Blade, no SPA** | Inertia + React/Vue | A portfolio is a content artefact: instant SEO, no hydration, no blank first paint, one rendering model | Richer interactivity is manual |
| 2 | **Tokens via `light-dark()`** | Duplicated `[data-theme]` blocks per component | One source of truth per colour, so the themes cannot drift. Components need almost no `dark:` variants | Requires 2024+ browsers |
| 3 | **Tailwind's default palette deleted** | Full Tailwind + discipline | Makes `bg-blue-500` structurally impossible rather than merely discouraged | Utility classes must be learned |
| 4 | **Tailwind confined to layout** | Tailwind for everything | A strict boundary prevents two competing styling systems | Colours are less terse to write |
| 5 | **Two border tokens** | One border colour | Measured necessity: greys clear 3:1 on white **or** on `#0B0D10`, not both. WCAG 1.4.11 is not negotiable | `border` vs `border-strong` must be understood |
| 6 | **Per-family text-on-fill tokens** | One `--color-text-inverse` | This caused two of the four gate defects. "Inverse of the surface" ≠ "readable on this fill" | More tokens |
| 7 | **Enum roles + Policies, no `spatie`** | Permission table + UI | 3 roles, 1 owner. A permission matrix nobody edits is overhead | A new role needs a deploy |
| 8 | **Closed registration** | Public signup | Single-owner system; a signup endpoint is attack surface with no product value | Account creation is manual |
| 9 | **`Action` per use case** | Fat controllers / services | One operation, testable, callable from web *and* console, transactionally complete | More files |
| 10 | **`Queries/` for reads** | Actions that read and write | Read composition and write orchestration have different shapes; mixing them produces neither | Another namespace |
| 11 | **String enums, not MySQL `ENUM`** | Native `ENUM` | Adding a case should be a code change, not a migration | No DB-level constraint |
| 12 | **Sanitise on write *and* render** | Write-time only | Render-time sanitising survives a sanitiser regression in stored data | A small render cost |
| 13 | **Media dedup by SHA-256** | Always store a new file | The same upload twice should cost disk once | A hash lookup per upload |
| 14 | **Content revisions (last 20)** | No history | A bad edit should be recoverable by the owner, not by a developer | Storage per revision |
| 15 | **Architecture tests in CI** | Code review only | Boundaries rot silently; a failing test does not | Test maintenance |
| 16 | **Design system validated in CI** | Manual visual review | A token change that breaks contrast **cannot merge** | Deliberate friction on design changes |
| 17 | **`_validate.mjs` at repo root** | Documentation only | The accessibility claim is computed, not asserted | One more CI step |
| 18 | **No public API in Phase 1** | REST from the start | No consumer exists; a public API is a surface to authenticate, version and secure for nobody | Integrations wait |

### The three decisions carrying the most risk

| Decision | Risk | Mitigation |
|---|---|---|
| **No SPA** (#1) | If the CMS later needs genuinely complex client state, Alpine will strain | Server-rendered pagination/forms are the model and the block registry is server-side. A future richer surface would be additive, not a rewrite |
| **Tailwind palette deleted** (#3) | A developer reaches for `bg-white` and finds it gone | Documented in `app.css` with the reason. The alternative is silently drifting colour |
| **Enum roles** (#7) | A fourth role needs a deploy | Accepted deliberately for a single-owner system, and recorded as a known limitation in `ARCHITECTURE.md` §6.1 |

### What this architecture deliberately does *not* have

Recorded so their absence is a decision rather than an oversight: no repository layer over Eloquent · no event bus
with one subscriber · no generic `BaseService` · no GraphQL · no microservices · no separate admin SPA · no
client-side state manager · no CSS-in-JS · no second icon library · no abstraction justified solely "for the future".

---

## 8. Approval Request

**Built and verified so far:** 7 governance documents, design tokens, the component CSS layer, the Component
Showcase, and a 76-check automated validator. **No application code exists.**

### Deliverables for review

| Artefact | Path |
|---|---|
| Design system | `DESIGN_SYSTEM.md` |
| Component library | `COMPONENT_LIBRARY.md` |
| Architecture | `ARCHITECTURE.md` |
| Database schema | `DATABASE_SCHEMA.md` |
| Project structure | `PROJECT_STRUCTURE.md` |
| Clean code standards | `CLEAN_CODE_STANDARDS.md` |
| Design tokens | `resources/css/design-tokens.css` |
| Component layer | `resources/css/components.css` |
| Tailwind bridge | `resources/css/app.css` |
| **Component Showcase** | `docs/styleguide/index.html` |
| Quality gate report | `docs/QUALITY_GATE.md` |
| Automated validator | `_validate.mjs` |

### Verification summary

```
15 / 15  documentation checklist items         ✅
76 / 76  automated checks                      ✅
16 / 16  contrast pairs ≥ 4.5:1 (AA)           ✅
 4       defects found and fixed by the gate   ✅
 2       documentation inconsistencies fixed   ✅
 0       unresolved architecture decisions     ✅
```

### What is needed from you

**Approve, or redirect.** Specifically:

1. **Is the OBSIDIAN SIGNAL design direction correct?** Open `docs/styleguide/index.html` in a browser and toggle
   the theme. For a design system, a direct look beats any description.
2. **Is the stack right?** Laravel 12 + Blade + Alpine + Tailwind 4 — no SPA, no Livewire.
3. **Is the CMS scope right?** Projects, Articles, Pages, Media, Messages, Settings, Users, revisions, audit log —
   or is something missing, or excess?
4. **Is the roadmap order acceptable?** Six phases, each gated by exit criteria.
5. **Are the two documented accessibility exceptions acceptable?** (`--color-text-tertiary` at 4.21:1 in light mode
   for non-essential metadata; disabled controls at ~2:1, which WCAG 1.4.3 exempts.)

**On approval, work begins at Phase 1 — Foundation install.** No product feature will be built before its
prerequisite phase has passed its exit criteria.

```
┌────────────────────────────────────────────────────────────────────┐
│  PHASE 0 — FOUNDATION                                              │
│  ────────────────────────────────────────────────────────────────  │
│  0.1  Design System           ✅ COMPLETE                          │
│  0.2  Design Tokens           ✅ COMPLETE                          │
│  0.3  Component Library       ✅ COMPLETE                          │
│  0.4  Software Architecture   ✅ COMPLETE                          │
│  0.5  Clean Code Standards    ✅ COMPLETE                          │
│  0.6  Codebase Structure      ✅ COMPLETE                          │
│  0.7  Quality Gate            ✅ PASSED                            │
│  0.8  Implementation Approval 🛑 AWAITING YOUR DECISION            │
└────────────────────────────────────────────────────────────────────┘
```

**Status: BLOCKED pending approval.** Implementation will not start unprompted.
