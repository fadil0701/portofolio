# PROJECT_STRUCTURE.md

**OBSIDIAN SIGNAL** — folder and file structure

| | |
|---|---|
| **Status** | ✅ Approved — Phase 0.6 complete |
| **Projection of** | `ARCHITECTURE.md` (this is that document, at file level) |
| **Principle** | Indonesian domain terms + standard technical terms; preserve Laravel/framework contracts |

---

## 0.1 Naming convention

- Application-owned names use Indonesian for business/domain terms and keep standard technical
  vocabulary in English where that is what developers commonly use (for example `admin`, `dashboard`,
  `login`, `middleware`, `controller`, `model`, `route`, `view`, `migration`, `seeder`, `email`, and `password`).
- PHP class names and matching filenames use PascalCase (for example `PengalamanKerja.php`).
- Application table/column names for domain data use Indonesian `snake_case`; route names and Blade
  view paths use the project's standard technical vocabulary (for example `admin.login` and `admin.dashboard`).
- Keep Laravel/framework contracts unchanged, including `User`, `DatabaseSeeder`, `Controller`,
  `routes/web.php`, the `users` table, `id`, timestamps, and authentication credential keys.
- Keep interoperability terms such as `email`, `slug`, `status`, and URL suffixes where translating
  them would obscure or break established framework and web conventions.
- Keep the required timestamp prefix on migration filenames; once a migration has run, add a new
  migration for later schema changes rather than renaming or rewriting its history.

Current domain examples: `Profil` → `profil`, `Proyek` → `proyek`,
`KategoriKeahlian` → `kategori_keahlian`, `PengalamanKerja` → `pengalaman_kerja`,
`Artikel` → `artikel`, and `PengaturanSitus` → `pengaturan_situs`. Admin routes use `/admin/login`
and `/admin/dashboard`.

---

## 1. Root Structure

```
portofolio-fadil/
├── app/                        # Application code (see §2)
├── bootstrap/                  # Framework bootstrap
├── config/                     # Configuration — one file per concern
├── database/
│   ├── factories/
│   ├── migrations/
│   ├── seeders/
│   └── schema/                 # SQL snapshots for review
├── docs/
│   ├── styleguide/
│   │   └── index.html          # ✅ Component Showcase (Phase 0.3 artefact)
│   ├── QUALITY_GATE.md         # ✅ Phase 0.7 checklist + evidence
│   └── IMPLEMENTATION_APPROVAL.md # ✅ Phase 0.8 summary
├── lang/                       # en/ · id/ — UI strings, no hardcoded copy in views
├── public/                     # Document root. ONLY index.php + built assets
│   ├── build/                  # Vite output (gitignored)
│   └── index.php
├── resources/
│   ├── css/
│   │   ├── design-tokens.css   # ✅ Design tokens — single source of truth
│   │   ├── components.css      # ✅ Component layer (53 primitives)
│   │   └── app.css             # ✅ Tailwind 4 entry + @theme bridge
│   ├── js/
│   │   ├── app.js              # Alpine bootstrap + registrations
│   │   └── modules/            # One file per non-trivial Alpine component
│   └── views/                  # see §3
├── routes/
│   ├── web.php                 # Public site
│   ├── admin.php               # CMS (auth-gated group)
│   ├── console.php             # Scheduler definitions
│   └── api.php                 # Minimal — see ARCHITECTURE.md §9
├── storage/                    # app/ · framework/ · logs/ (never public)
├── tests/
│   ├── Feature/                # HTTP-level, one per Action/controller behaviour
│   ├── Unit/                   # Pure domain logic
│   └── Architecture/           # Rule-enforcement tests (see §8)
├── .env.example
├── _validate.mjs               # ✅ Design-system validator (76 checks)
├── ARCHITECTURE.md             # ✅
├── CLEAN_CODE_STANDARDS.md     # ✅
├── COMPONENT_LIBRARY.md        # ✅
├── DATABASE_SCHEMA.md          # ✅
├── DESIGN_SYSTEM.md            # ✅
├── PROJECT_STRUCTURE.md        # ✅ (this file)
└── README.md                   # ✅
```

**Why the governance documents sit at the root:** they are the project's contract, not incidental
documentation. Root placement makes them impossible to miss and forces a deliberate act to remove one.

---

## 2. `app/` — the layered application

```
app/
├── Actions/                    # APPLICATION LAYER — one use case per class
│   ├── Auth/
│   │   ├── ResetUserPassword.php
│   │   └── UpdateUserPassword.php
│   ├── Cms/
│   │   ├── CreateProject.php
│   │   ├── UpdateProject.php
│   │   ├── PublishProject.php
│   │   ├── ArchiveProject.php
│   │   ├── StoreMediaUpload.php
│   │   └── RestoreContentRevision.php
│   ├── Contact/
│   │   └── StoreContactMessage.php
│   └── Portfolio/
│       ├── BuildProjectDetailPage.php
│       └── BuildHomePage.php
│
├── Console/Commands/
│   ├── PublishScheduledContent.php
│   ├── PruneContactMessages.php
│   └── RegenerateSitemap.php
│
├── Contracts/                  # Interfaces — the only seam for substitution
│   ├── MediaStorage.php
│   └── HtmlSanitiser.php
│
├── DTOs/                       # Explicit data contracts between layers
│   ├── MediaUploadData.php
│   └── ProjectData.php
│
├── Enums/                      # Domain vocabulary — DATABASE_SCHEMA.md §7
│   ├── ContentStatus.php
│   ├── ContentFormat.php
│   ├── MediaProcessingStatus.php
│   ├── ProjectType.php
│   ├── SkillLevel.php
│   ├── TechnologyUsage.php
│   └── UserRole.php
│
├── Exceptions/
│   ├── Domain/
│   │   ├── CannotPublishUnreviewedContent.php
│   │   └── MediaProcessingFailed.php
│   └── Handler.php             # Centralised rendering (ARCHITECTURE.md §12.3)
│
├── Http/                       # PRESENTATION LAYER
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── ArticleController.php
│   │   │   ├── MediaController.php
│   │   │   ├── MessageController.php
│   │   │   ├── SettingsController.php
│   │   │   └── UserController.php
│   │   ├── Auth/
│   │   └── Public/
│   │       ├── HomeController.php
│   │       ├── ProjectController.php
│   │       ├── ArticleController.php
│   │       ├── PageController.php
│   │       ├── ContactController.php
│   │       └── SitemapController.php
│   ├── Middleware/
│   │   ├── EnsureUserIsActive.php
│   │   ├── EnsureTwoFactorIsConfirmed.php
│   │   ├── ForceHttps.php
│   │   ├── SecurityHeaders.php        # CSP nonce, HSTS, etc.
│   │   └── SetLocale.php
│   ├── Requests/                      # Validation lives HERE and nowhere else
│   │   ├── Admin/
│   │   │   ├── StoreProjectRequest.php
│   │   │   ├── UpdateProjectRequest.php
│   │   │   └── StoreMediaRequest.php
│   │   └── Public/
│   │       └── StoreContactMessageRequest.php
│   └── Resources/                     # Explicit output shape — never a raw model
│       ├── ProjectResource.php
│       └── MediaResource.php
│
├── Jobs/                       # Queued work — IDs only, idempotent
│   ├── ConvertMediaToWebp.php
│   ├── GenerateMediaConversions.php
│   ├── SendContactNotification.php
│   └── IncrementViewCount.php
│
├── Models/                     # DOMAIN LAYER — relations, casts, scopes
│   ├── ActivityLog.php    ├── Media.php           ├── Setting.php
│   ├── Article.php        ├── MediaConversion.php ├── Skill.php
│   ├── ContactMessage.php ├── Page.php            ├── SkillGroup.php
│   ├── Experience.php     ├── Profile.php         ├── Tag.php
│   ├── Technology.php     ├── Project.php         ├── Testimonial.php
│   └── User.php
│
├── Policies/                   # AUTHORIZATION AUTHORITY
│   ├── ArticlePolicy.php
│   ├── MediaPolicy.php
│   ├── ProjectPolicy.php
│   └── UserPolicy.php
│
├── Providers/
│   ├── AppServiceProvider.php
│   ├── AuthServiceProvider.php       # Policy + Gate registration
│   └── MediaServiceProvider.php      # Binds the MediaStorage implementation
│
├── Queries/                    # Read-side composition, one per page/screen
│   ├── PublishedProjectIndex.php
│   ├── AdminProjectIndex.php
│   └── AdminDashboardMetrics.php
│
├── Services/                   # Only for real orchestration or an external boundary
│   ├── MediaManager.php              # implements MediaStorage
│   ├── HtmlSanitiser.php             # implements HtmlSanitiser
│   ├── SettingsService.php
│   └── Slugger.php
│
├── Support/                    # Cross-cutting, dependency-free helpers
│   ├── Blocks/
│   │   ├── BlockRegistry.php         # whitelist for page blocks
│   │   └── Renderers/
│   └── Logging/
│       └── RedactSensitiveData.php   # PII redaction processor
│
└── View/
    ├── Components/             # Blade class-components (§3.4)
    └── Composers/              # Shared view data (nav, footer, settings)
```

**Named departures from Laravel defaults, each with a reason:**

| Departure | Why it exists |
|---|---|
| `Actions/` | Laravel has no first-class use-case layer; without it, logic accretes in controllers |
| `Queries/` | Read composition is a different concern from write orchestration; mixing them yields Actions that do both badly |
| `DTOs/` | An explicit data contract between HTTP and domain, so validation shape and domain shape can differ safely |
| `Contracts/` | The only seam through which infrastructure can be replaced |
| `Support/Blocks/` | The registry *is* the enforcement mechanism for the page-block whitelist |

---

## 3. `resources/views/` — presentation

```
resources/views/
├── layouts/
│   ├── app.blade.php           # Public shell: <head>, skip link, nav, footer, theme script
│   └── admin.blade.php         # CMS shell: sidebar + topbar
│
├── components/                 # Blade components, grouped to mirror COMPONENT_LIBRARY.md
│   ├── foundation/             # container, section, stack, grid, divider, eyebrow, icon
│   ├── navigation/             # navbar, mobile-nav, breadcrumb, footer, sidebar, admin-nav
│   ├── ui/                     # button, icon-button, input, textarea, select, checkbox,
│   │                           # radio, toggle, modal, dialog, dropdown, tooltip, tabs,
│   │                           # accordion, pagination, toast, alert, skeleton,
│   │                           # empty-state, error-state, badge, avatar
│   ├── portfolio/              # hero, project-card, skill-group, expertise-card,
│   │                           # experience-timeline, article-card, contact-form,
│   │                           # social-links, terminal, arch-diagram, chart-bars
│   └── cms/                    # data-table, table-toolbar, search-filter, date-filter,
│                               # form-field, image-upload, media-picker, rich-text-editor,
│                               # status-badge, confirmation-dialog, activity-timeline,
│                               # file-preview, metric
│
├── public/
│   ├── home.blade.php
│   ├── about.blade.php
│   ├── work/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── writing/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── contact.blade.php
│   └── pages/
│       └── show.blade.php      # generic CMS page with blocks
│
├── admin/
│   ├── dashboard.blade.php
│   ├── projects/               # index · create · edit · show
│   ├── articles/
│   ├── media/                  # index (library) · picker
│   ├── messages/
│   ├── settings/
│   └── users/
│
├── auth/                       # login · forgot-password · reset-password · two-factor
├── errors/                     # 403 · 404 · 419 · 429 · 500 · 503 (designed, not framework defaults)
└── partials/
    ├── head.blade.php          # meta, OG, JSON-LD, theme script, fonts, CSS
    └── scripts.blade.php
```

### 3.1 The view rules

1. **A view renders. It does not retrieve.** No queries, no `Model::all()`, no business conditionals.
2. **Data arrives as a view model** built by a Query object or an Action — never as a raw collection the
   view must interpret.
3. **No inline styles** except dynamic values that cannot be a class (e.g. a progress `inline-size`).
   Every exception is commented.
4. **No inline event handlers** (`onclick="…"`). This is what makes a strict CSP possible
   (`ARCHITECTURE.md` §11.2).
5. **No hardcoded copy.** All user-facing strings go through `lang/` — the site ships in Indonesian and
   English, and a literal in a Blade file makes the second locale unreachable.
6. **Components over partials.** A partial is a component that refused to declare its props.

### 3.2 `{!! !!}` policy

Exactly two legitimate uses, each requiring an inline comment:
- Sanitised rich text inside `.os-prose`
- Sanitised SVG icon markup

Anything else is an XSS defect.

### 3.3 The theme script

`partials/head.blade.php` contains the ~0.3KB inline theme script. It carries the CSP nonce and must run
**before** any stylesheet-dependent paint to avoid a flash of incorrect theme (`DESIGN_SYSTEM.md` §20.3).

### 3.4 Blade component conventions

```blade
{{-- Explicit props with types; no implicit $attributes soup --}}
@props([
    'project',            // Project — required
    'index' => null,      // ?int — decorative index numeral
    'featured' => false,  // bool
])

<article @class(['os-project-card', 'os-project-card--featured' => $featured])>
    @if ($index !== null)
        <span class="os-project-card__index" aria-hidden="true">{{ str_pad($index, 2, '0', STR_PAD_LEFT) }}</span>
    @endif
    {{-- … --}}
    {{ $slot }}
</article>
```

- Props are declared with `@props` and, where non-obvious, typed in a comment.
- `@class()` over string concatenation — conditional classes must be readable.
- The component name mirrors the `.os-*` primitive it wraps (`x-ui.button` → `.os-btn`).
- A component that queries the database is a bug, not a shortcut.

---

## 4. `routes/` — organisation

```php
// routes/web.php — public site, grouped by concern
Route::middleware(['force.https'])->group(function () {
    Route::get('/',                     HomeController::class)->name('home');
    Route::get('/work',                 [ProjectController::class, 'index'])->name('work.index');
    Route::get('/work/{project:slug}',  [ProjectController::class, 'show'])->name('work.show');
    Route::get('/writing/{article:slug}', [ArticleController::class, 'show'])->name('writing.show');
    Route::get('/contact',              [ContactController::class, 'create'])->name('contact.create');
    Route::post('/contact',             [ContactController::class, 'store'])
        ->middleware('throttle:contact')->name('contact.store');
    Route::get('/{page:slug}',          PageController::class)->name('page.show'); // MUST be last
});
```

```php
// routes/admin.php — CMS, gated as a group so no route can forget
Route::prefix('admin')->name('admin.')
    ->middleware(['auth', 'active', 'verified', 'can:cms.access'])
    ->group(function () {
        // Projects: index/create/store/show/edit/update/destroy
        // Media, Messages, Settings, Users
        // Destructive + role-restricted actions carry an extra `role:` middleware
    });
```

**Rules.**
1. **Route groups carry the guard rails.** A route outside the group cannot accidentally be public.
2. **Middleware is named**, never an inline closure.
3. **Route model binding uses `:slug`** for public content — never an exposed ID.
4. **The catch-all page route is always last**, or it shadows everything after it.
5. **Every route has a `name`.** `route('work.show', $project)` survives URL changes; a literal does not.
6. **Destructive routes are `DELETE`.** A GET that mutates state is a CSRF hole waiting for a crawler.

---

## 5. `config/` — one file per concern

`app.php` · `auth.php` · `cache.php` · `cms.php` · `database.php` · `filesystems.php` · `fortify.php` ·
`logging.php` · `media.php` · `queue.php` · `security.php` · `session.php`

**Rules.**
- **No `env()` outside `config/`.** After `config:cache`, `env()` returns `null` everywhere else — a silent
  production-only failure. This rule exists to make that impossible.
- **Every key has a default**, so the app boots without a complete `.env`.
- Owner-editable values live in the `settings` table; deploy-level values live here. The boundary is
  documented per key (`ARCHITECTURE.md` §7.4).

---

## 6. Testing Structure

```
tests/
├── Feature/
│   ├── Auth/                   # login, reset, 2FA, session invalidation
│   ├── Cms/                    # one file per Action — create, publish, archive, upload
│   ├── Public/                 # one file per page: status, content, SEO, a11y basics
│   └── Security/               # authorization matrix, CSRF, rate limits, XSS payloads
├── Unit/
│   ├── Actions/ · Enums/ · Support/     # Slugger, sanitiser, block registry
├── Architecture/               # rule enforcement — see §8
└── TestCase.php
```

**Rules.**
- One test file per Action, per Page, per Policy.
- **Pest** syntax; factories for state, rarely fixtures.
- `RefreshDatabase` everywhere; SQLite in CI for speed, MySQL in the release job.
- Every bug fix ships with a regression test. A bug that cannot be tested is not yet understood.
- `Model::preventLazyLoading()` is enabled in all non-production environments.

---

## 7. Forbidden Patterns

Each is enforced by an architecture test (§8) where mechanically possible.

| Forbidden | Why |
|---|---|
| Query in a Blade view | Destroys the layering and hides N+1s where nobody looks |
| Business rule in a controller | The controller becomes untestable and unusable from console/queue |
| `$request->validate()` in a controller | Scatters validation; Form Requests are the single location |
| `$guarded = []` | Mass-assignment hole |
| `env()` outside `config/` | Silently returns `null` after `config:cache` |
| Raw hex or `px` in `components.css` | Design drift — enforced by `_validate.mjs` |
| Arbitrary spacing or font size in a view | Same |
| `{!! !!}` without a sanitised source + comment | XSS |
| Inline `onclick` / `onload` | Blocks a strict CSP |
| Hardcoded user-facing string in a view | Breaks the second locale |
| `DB::raw()` with interpolated input | SQL injection |
| A Service that only forwards to one model | Needless layer — delete it |
| A Blade component that queries the database | Presentation layer doing retrieval |
| A second icon library | Visual inconsistency (`DESIGN_SYSTEM.md` §13.6) |
| `overflow: hidden` on `body` to fix a layout bug | Hides the symptom and breaks scrolling |
| `!important` | Almost always a specificity failure; fix the cascade instead |
| Commented-out code | Git remembers. Delete it |

---

## 8. Architecture Tests

Real tests that fail the build — not conventions in a document.

```php
// tests/Architecture/LayerTest.php

test('controllers do not use the database directly')
    ->expect('App\Http\Controllers')
    ->not->toUse(['Illuminate\Support\Facades\DB', 'Illuminate\Database\Eloquent\Builder']);

test('models do not depend on the HTTP layer')
    ->expect('App\Models')->not->toUse('App\Http');

test('the domain layer never depends on the presentation layer')
    ->expect('App\Actions')->not->toUse('App\Http\Controllers');

test('no debugging calls survive')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])->not->toBeUsed();

test('status strings only exist in enums')
    ->expect('App\Actions')->not->toUse(['"published"', "'published'"]);

test('controller actions stay under 12 lines')
    ->expect('App\Http\Controllers')->toHaveMaxMethodLines(12);
```

Three suites cover the three things that actually decay:

| Suite | Guards |
|---|---|
| `tests/Architecture/*` | **Structure** — layering, boundaries, forbidden constructs |
| `_validate.mjs` | **Design** — tokens, contrast, primitives, drift |
| PHPStan level 6 (Larastan) | **Types** — nullability, return types, implicit `mixed` |

---

## 9. Change Log

| Date | Change | Reason |
|---|---|---|
| 2026-08-31 | Initial structure defined (Phase 0.6) | Foundation gate |

---

*Projection of `ARCHITECTURE.md`. Rule enforcement lives in `tests/Architecture/*` and `_validate.mjs`.*
