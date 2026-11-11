# OBSIDIAN SIGNAL

**Portfolio & CMS** — *Premium Technology × Swiss Editorial × Neo-Industrial Engineering*

> ✅ **Portfolio and CMS are implemented.** Admin access is available at `/admin/login`.

---

## What this repository currently contains

The project includes a database-backed portfolio, admin login, and CMS for profile, projects, skills, experience,
articles, contact messages, and site settings.

| | |
|---|---|
| **Application code** | Portfolio homepage, admin authentication, CMS dashboard, and content management |
| **Foundation** | 7 governance documents · 3 CSS layers · Component Showcase · Laravel/Vite toolchain · CI |
| **Verification** | 12 Pest feature tests · Pint · PHPStan level 6 |
| **Stack** | Laravel 12 · PHP 8.4 · Blade · Alpine.js 3 · Tailwind CSS 4 · SQLite (local) |

---

## Start here

### Run the app and create the first admin

Set `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` in your local `.env`, then run:

```sh
php artisan migrate
php artisan db:seed --class=DataPortofolioSeeder
php artisan admin:create --from-env
php artisan serve
```

Use a unique password with at least 12 characters. The password is hashed before it is saved. If configuration
is cached, run `php artisan config:clear` before `admin:create --from-env`. Remove `ADMIN_PASSWORD` from `.env`
after the account is created, then run `php artisan config:clear` again to clear any cached copy. For secure
interactive entry instead, run `php artisan admin:create` without `--from-env`. After starting the app, open
`/admin/login`.

**1. Look at the design system.** It is the fastest way to judge whether the direction is right.

```
open docs/styleguide/index.html
```

No build step, no server, no dependencies. It renders the design tokens and reusable component primitives, and it
has a theme toggle. It is the acceptance test for the visual system.

**2. Read the decision document:**

```
docs/IMPLEMENTATION_APPROVAL.md
```

**3. Verify the claims.** Everything asserted about contrast and structure is reproducible:

```
node _validate.mjs
```

---

## Document map

| Document | Answers | Sections |
|---|---|---|
| [`DESIGN_SYSTEM.md`](DESIGN_SYSTEM.md) | What does it look like, and why? | 25 |
| [`COMPONENT_LIBRARY.md`](COMPONENT_LIBRARY.md) | What components exist, and what is their contract? | A–I |
| [`ARCHITECTURE.md`](ARCHITECTURE.md) | How is the software structured? | 12 |
| [`DATABASE_SCHEMA.md`](DATABASE_SCHEMA.md) | What is the data model? | 11 |
| [`PROJECT_STRUCTURE.md`](PROJECT_STRUCTURE.md) | Which file goes where? | 9 |
| [`CLEAN_CODE_STANDARDS.md`](CLEAN_CODE_STANDARDS.md) | How is code written and reviewed? | 16 |
| [`docs/QUALITY_GATE.md`](docs/QUALITY_GATE.md) | Did the foundation actually pass? | 7 |
| [`docs/IMPLEMENTATION_APPROVAL.md`](docs/IMPLEMENTATION_APPROVAL.md) | What is being asked for approval? | 8 |

**Reading order for a reviewer:** `docs/IMPLEMENTATION_APPROVAL.md` → the Style Guide → then whichever document
answers your first question.

---

## Code artefacts

```
resources/css/
├── design-tokens.css    # 500 lines — ALL colour, type, spacing, radius, elevation, motion
├── components.css       # 2,059 lines — reusable `.os-` primitive layer
└── app.css              # Tailwind 4 entry + token integration

docs/styleguide/
└── index.html           # 1,353 lines — the Component Showcase

_validate.mjs            # 76 checks: structure, tokens, primitives, contrast
```

The Laravel framework skeleton, Pest/Pint/PHPStan, self-hosted fonts, and CI workflow are installed. The root route
is intentionally not a product page; use `/up` for the framework health check.

---

## The design system in one paragraph

**OBSIDIAN SIGNAL** treats the interface as an instrument, not a poster. The palette is a quasar: an intensely
luminous cyan signal (`#00D4FF`) against a near-black obsidian field (`#0B0D10`), with a single lime accent
(`#C6F277`) rationed to one moment per viewport. Hierarchy comes from space, rule, and surface step — not from
shadows or gradients. Typography is three families with one job each: **Geist** for UI, **Geist Mono** for code and
technical micro-labels, and **Instrument Serif** for editorial pull-quotes only, maximum two per page. Four devices
carry the identity: a **signal rule**, a **chamfer**, a **mono micro-label**, and the **asymmetric 7/5 editorial
split**.

**The recognisability test:** a screenshot should be identifiable *and* readable. Either alone is a failure.

---

## Three architectural commitments worth knowing

**1. The token carries the mode.** Every colour is declared once with `light-dark()`:

```css
--color-surface: light-dark(#FFFFFF, #12161C);
```

Dark and light therefore cannot drift apart, and components need almost no `dark:` variants. An automated check
fails the build if any component file contains a `[data-theme]` override or a raw hex value.

**2. Tailwind cannot produce an off-system colour.** Tailwind's default palette is **deleted** in `app.css`.
`bg-blue-500` does not exist. Tailwind is confined to layout and composition; colour, type, radius, and elevation
come from tokens. Design drift is prevented structurally rather than by discipline.

**3. Accessibility is computed, not asserted.** `_validate.mjs` recalculates WCAG contrast from the token values on
every run. All 16 solid-fill/text pairings clear 4.5:1. Two exceptions are documented and bounded. A token change
that breaks AA **cannot pass CI**.

---

## What the quality gate caught

The Phase 0.7 gate found four real defects in the foundation itself — recorded in full in
[`docs/QUALITY_GATE.md`](docs/QUALITY_GATE.md) §3:

| Defect | Measured | Fixed to |
|---|---|---|
| White text on the dark-mode danger fill | **2.89:1** ❌ | 6.74:1 |
| Light text on the light-mode accent fill | **1.41:1** ❌ | 13.82:1 |
| Secondary button hover | **4.47:1** ⚠️ | 4.99:1 |
| A glob inside a CSS comment closing it 34 lines early, exposing prose as CSS | — | rewritten |

The first three were found by measuring instead of eyeballing. The fourth was found by a validator rule written
specifically to detect that class of bug.

---

## Roadmap

| Phase | Scope | Gate to proceed |
|---|---|---|
| **1** | ✅ Foundation install (Laravel, Vite, Tailwind, fonts, CI) | 15.85KB gzipped CSS; validator and health check pass |
| **2** | Domain & data (23 tables, 11 enums, 17 models, policies) | Migrations reversible; policy matrix tested |
| **3** | Design system in Blade (53 canonical primitives, layouts, error pages) | Every primitive renders in both themes |
| **4** | Public site (home, work, writing, about, contact, SEO) | Lighthouse ≥95 in all categories |
| **5** | CMS (auth + 2FA, CRUD, media, messages, settings, audit) | Owner can run the site without code |
| **6** | Hardening & launch (CSP, backups, monitoring, deploy) | Restore rehearsal completed |

**Order is fixed.** Each phase's exit criteria gate the next.

---

## Verification

```
$ node _validate.mjs
...
```

Additional Phase 1 checks:

```
composer test
composer lint
composer analyse
npm run build
INFO  colour tokens: 73 total, 64 mode-aware, 9 deliberate single-mode
INFO  solid-fill/text pairs clearing AA (4.5:1): 16/16

ALL CHECKS PASSED
```

The validator checks six groups:

1. **Structural** — brace/paren balance, comment termination and disambiguation
2. **Tokens** — `light-dark()` arity, the WCAG 1.4.11 contract, literal Tailwind breakpoint tokens
3. **Component layer** — layer integrity, all 53 primitives, zero raw hex, zero mode overrides, keyframe closure
4. **Tailwind integration** — imports, cleared namespaces, valid breakpoint values, no circular alias
5. **Style guide** — tag balance, wiring, skip link, theme toggle, unique ids
6. **Contrast** — every declared solid-fill/text pairing, both modes

---

## Environment

| | |
|---|---|
| Platform | Windows 11 |
| Runtime | Laragon workflow; PHP 8.4.27 installed locally via WinGet |
| Node | v24.15.0 · npm 11.14.1 |
| Validator | `node _validate.mjs` — **zero dependencies** |

The validator uses the **Node standard library only**, so it runs on a clean checkout with no `npm install`.

---

## Change discipline

- **The document changes first, then the tokens, then the components.** Never the reverse.
- Any deviation from `DESIGN_SYSTEM.md` requires a written entry in its change log, with the reason and scope.
- A `design`-type commit that changes a token **must** include the updated `DESIGN_SYSTEM.md` in the same commit.
- Silent drift is the only prohibited outcome. Finding a rule is wrong is a legitimate result — but it changes the
  document first.

---

*Phase 0 approved · Phase 1 complete · Phase 2 awaiting approval.*
