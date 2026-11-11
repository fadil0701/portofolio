# ARCHITECTURE.md

**OBSIDIAN SIGNAL** — system architecture

| | |
|---|---|
| **Status** | ✅ Approved — Phase 0.4 complete |
| **Pattern** | Laravel-native layered modular monolith |
| **Stack** | Laravel 12 · PHP 8.4 · Blade · Alpine.js 3 · Tailwind CSS 4 · MySQL 8 |
| **Verifier** | This document is the contract; `PROJECT_STRUCTURE.md` is its file-level projection |

---

## 0. Stack Decision and Trade-offs

| Concern | Decision | Why | Trade-off accepted |
|---|---|---|---|
| **Backend framework** | Laravel 12 | Batteries-included auth, queue, scheduler, migrations, testing — all needed here | Framework conventions constrain structure; we design *with* them |
| **Rendering** | **Server-rendered Blade** | A portfolio is a content/SEO artefact. Server HTML = no hydration cost, no SEO gap, no blank first paint | Less app-like interactivity than an SPA |
| **Interactivity** | **Alpine.js 3** | Islands of behaviour for modals, menus, tabs, pickers. ~15KB, no router | Not suited to complex client state — deliberately out of scope |
| **Styling** | **Tailwind CSS 4 + token layer** | Tailwind for layout/composition; tokens own colour/type/elevation | Two systems need a documented boundary (`app.css`) |
| **Frontend framework** | **None (no Vue/React/Inertia)** | An SPA adds a second rendering model and a hydration boundary for no benefit | Media picker is more manual |
| **Auth** | **Laravel Fortify (headless)** | 2FA, rate limiting, reset, verification without imposing views | Must build the auth UI ourselves — which we want, for design control |
| **Livewire** | **Not used** | Server-rendered forms/pagination already are the model; Livewire adds a second state model | Slightly more explicit admin code |
| **Database** | **MySQL 8** / SQLite for tests | InnoDB, full-text, generated columns, Laragon-native | SQLite tests require avoiding dialect-specific SQL |
| **Rich text** | **Sanitised HTML + `content_format` enum** | Non-technical editing; the enum keeps a Markdown escape hatch without a migration | Sanitise on write **and** render |
| **Media** | **Local disk, driver-abstracted** | Simple, cheap, fast. S3 becomes a config change, not a rewrite | No CDN until needed |

**Deliberately rejected:** Inertia+React (two state models), Livewire (second state model, SPA-ish latency),
a headless CMS (this project *is* the CMS), a full DDD folder tree (fights Laravel conventions at this size).

---

## 1. System Architecture

A **single deployable Laravel application** with clean internal layering. One codebase, one deploy, two
surfaces (public site + CMS) sharing one domain layer.

```
┌──────────────────────────────────────────────────────────────────────┐
│                          CLIENT (browser)                            │
│  Public site: server HTML + Alpine islands                           │
│  CMS:         server HTML + Alpine islands                           │
└───────────────────────────────┬──────────────────────────────────────┘
                                │ HTTPS
┌───────────────────────────────▼──────────────────────────────────────┐
│                  PRESENTATION  (app/Http, resources/views)            │
│  Controllers · Form Requests · Resources · Middleware · Blade views   │
│  RESPONSIBILITY: translate HTTP ⇄ domain. NO business rules.          │
└───────────────────────────────┬──────────────────────────────────────┘
                                │ typed args / DTOs
┌───────────────────────────────▼──────────────────────────────────────┐
│                APPLICATION  (app/Actions, app/Jobs)                   │
│  One class · one use case · one public method (__invoke)              │
│  RESPONSIBILITY: orchestrate a single business operation atomically.  │
└───────────────────────────────┬──────────────────────────────────────┘
                                │
┌───────────────────────────────▼──────────────────────────────────────┐
│                      DOMAIN  (app/Models, app/Enums,                  │
│                               app/Services, app/Contracts)            │
│  Eloquent models · enums · query scopes · invariants · policies        │
│  RESPONSIBILITY: the rules of the business.                           │
└───────────────────────────────┬──────────────────────────────────────┘
                                │
┌───────────────────────────────▼──────────────────────────────────────┐
│             INFRASTRUCTURE  (app/Support, config, database)           │
│  MediaManager · Sanitiser · Slugger · Settings · cache · queue         │
│  RESPONSIBILITY: talk to the outside world, behind an interface.      │
└──────────────────────────────────────────────────────────────────────┘
```

**The rule that makes this real:** dependencies point **downward only**. A Model never calls a Controller.
An Action may call a Service. A Controller may call an Action. Violating that direction is the single defect
that destroys this architecture.

### 1.1 Why a layered monolith, not microservices

- One team, one deploy target, one database. Distributed transactions and network failure modes would be
  cost with no benefit.
- Extracting a service is a *refactor* (move a namespace behind an interface). Starting distributed is a
  *permanent* cost.
- Interfaces in `app/Contracts` mean any layer can be extracted later without touching call sites — the
  option is preserved without paying for it now.

### 1.2 Runtime topology

| Component | Purpose | Scale |
|---|---|---|
| **Web** (php-fpm + nginx) | Serves HTTP | 1 → N |
| **Queue worker** | Image conversion, email, notifications | 1 → N |
| **Scheduler** (cron) | Scheduled publishing, pruning | **exactly 1** |
| **MySQL 8** | Primary datastore | 1 (+ replica later) |
| **Redis** | Cache + queues (optional; `database` driver is the fallback) | 1 |

**Why exactly one scheduler:** scheduled publishing flips content live. Two schedulers double-fire. The
dispatch is idempotent, but the constraint is documented to prevent a "just add another container" mistake.

### 1.3 Request lifecycle (public page)

```
GET /work/{slug}
  → Route model binding (Project by slug, published scope)
  → Middleware: cache headers, security headers
  → Controller\Work\ShowProjectController  (≈8 lines: authorize, call action, return view)
  → Action\Portfolio\BuildProjectDetailPage   (loads relations, composes the view model)
  → View: resources/views/work/show.blade.php  (renders only; no queries)
  → Response + ETag/Last-Modified
```

### 1.4 Request lifecycle (CMS write)

```
POST /admin/projects
  → Middleware: auth, verified, can:cms.access
  → StoreProjectRequest            (validation — the only place input rules live)
  → Authorize via ProjectPolicy::create
  → Controller\Admin\ProjectController::store
  → DB::transaction(fn () => Action\Cms\CreateProject::__invoke($request->validated(), $user))
        ├─ materialise the model
        ├─ attach technologies (pivot)
        ├─ sync tags
        ├─ create the initial revision
        └─ write an activity log entry
  → Redirect + flash (PRG pattern)
```

Every multi-step write is wrapped in `DB::transaction`. Partial writes are not a state this system allows.

---

## 2. Frontend Architecture

### 2.1 Layer separation

| Layer | Owns | Never does |
|---|---|---|
| Blade layouts | Document shell, meta, nav/footer slots | Data retrieval |
| Blade components | Composition + ARIA wiring | Business rules, queries |
| Alpine.js | Local interaction state (open/closed, selected) | Fetching business data or authorising |
| CSS tokens | All visual values | — |
| `components.css` | Control anatomy | Content-specific styling |

### 2.2 Progressive enhancement

The site **works without JavaScript**. Alpine enhances; it does not enable.

- Navigation, article reading, project browsing, and form submission are plain HTML.
- Where an affordance is JS-only (a modal, a menu), a non-JS path exists — the mobile nav renders from the
  DOM and is toggled by `[hidden]`, so it is reachable even if Alpine fails.
- The contact form is `<form method="POST">` with server-side validation. No JS required to submit.

### 2.3 Bundle and rendering budgets

| Budget | Target |
|---|---|
| CSS (prod, gzipped) | < 45 KB — tokens + components + used Tailwind utilities |
| JS (prod, gzipped) | < 25 KB — Alpine + app modules |
| Fonts | 3 families, self-hosted via `@fontsource-variable`, `font-display: swap`, latin subset |
| LCP | < 1.8s on mid-tier phone / 4G |
| CLS | < 0.05 — intrinsic `width`/`height` on every `<img>` |
| INP | < 150ms |
| TTFB | < 300ms cached, < 700ms uncached |

These are budgets, not aspirations: a regression above them blocks the release.

### 2.4 Alpine conventions

- One component per `x-data` scope; keep it under ~30 lines. Beyond that it belongs in a module.
- **No fetches for business data.** Alpine may toggle, copy, and validate client-side; it may not *decide*.
- Server state reaches Alpine through `data-*` attributes into `x-data`, never invented client-side.
- `x-cloak` is mandatory on anything that starts hidden, with a single global CSS rule.

---

## 3. Backend Architecture

### 3.1 Controllers — orchestration only

A controller action is **authorise → delegate → respond**. Target: **under 12 lines.**

```php
public function show(Project $project, BuildProjectDetailPage $build): View
{
    $this->authorize('view', $project);

    return view('work.show', $build($project));
}
```

**Prohibited in a controller:** queries beyond route-model binding, business rules, `if` chains over domain
state, HTML construction, direct media manipulation, email sending.

### 3.2 Actions — one use case each

| Property | Rule |
|---|---|
| Signature | Single `__invoke()`, typed parameters, typed return |
| Naming | Imperative verb phrase: `CreateProject`, `PublishArticle`, `ConvertMediaToWebp` |
| Size | One reason to change. Two reasons → split |
| Transaction | Owns `DB::transaction` if it performs multiple writes |
| Test | Exactly one feature test per Action |

```php
final class PublishArticle
{
    public function __invoke(Article $article, User $actor): Article
    {
        return DB::transaction(function () use ($article, $actor): Article {
            $article->update([
                'status'       => ArticleStatus::Published,
                'published_at' => $article->published_at ?? now(),
            ]);
            ActivityLog::record('article.published', $article, $actor);
            return $article->refresh();
        });
    }
}
```

### 3.3 Services — only for real orchestration or an external boundary

A Service exists when it either (a) coordinates **multiple different** Actions/Domain objects, or (b) wraps
an **external system** behind an interface. A Service that merely forwards to one model is a needless layer
and must be deleted.

### 3.4 Choosing the right construct

| Situation | Use |
|---|---|
| Single Eloquent write | The model, called from an Action |
| Multi-step write, one use case | **Action** |
| Same operation from web + console + queue | **Action** |
| Coordinates 3+ Actions | **Service** |
| Wraps an external API/filesystem behind a swappable boundary | **Service** + **Contract** |
| Read-side composition for one page | **Query object** (readonly DTO with `get()`) |

### 3.5 Form Requests — the only place validation lives

- One Form Request per write endpoint: `rules()`, `authorize()`, `messages()`, `attributes()`.
- **Named-array messages**: `'title.required' => '…'`, never a naked string.
- `prepareForValidation()` normalises (trim, slugify, empty string → null).
- Controllers never call `$request->validate()`.

### 3.6 Queues and jobs

**Queued:** image conversion, WebP/AVIF transcoding, email, notification fanout, sitemap regeneration, cache
warming. **Never queued:** anything the user needs a synchronous result for.

| Concern | Rule |
|---|---|
| Idempotency | Every job must be safe to run twice — check state before acting |
| Retries | Explicit `$tries`, `$backoff`, and a `failed()` handler |
| Timeout | Explicit `$timeout`; the 60s default is not a decision |
| Payload | IDs only, never serialised models — a model may change or vanish |

---

## 4. Database Architecture

### 4.1 Conventions

| Concern | Rule |
|---|---|
| Engine / charset | InnoDB, `utf8mb4`, `utf8mb4_unicode_ci` |
| Primary key | `id` — `bigIncrements`, never exposed publicly |
| Foreign key | `{model}_id`, always indexed, explicit `onDelete` behaviour |
| Pivot tables | Singular model names, **alphabetical**: `project_technology`, `article_tag` |
| Timestamps | `created_at`/`updated_at` everywhere; `published_at` nullable; `deleted_at` where soft delete applies |
| Booleans | `is_`/`has_` prefix, `boolean`, explicit default |
| Enums | Stored as `string` with a PHP enum cast (readable in SQL, typed in PHP) |
| JSON | Only for non-queried structure (achievements, blocks, conversions). Never for anything filtered |

### 4.2 Why string enums rather than native MySQL ENUM

A native `ENUM` requires a migration to add a value and cannot be reordered safely. A `VARCHAR(32)` with a
PHP enum cast gives typed access in code, readable values in SQL, and adding a case is a code-only change.

### 4.3 Indexing strategy

| Index | Purpose |
|---|---|
| `slug` UNIQUE per content table | Public lookup path |
| `(status, published_at)` composite | The published-listing query |
| `is_featured`, `sort_order` | Manually ordered gallery/list queries |
| `{model}_id` on every FK | Join performance + FK integrity |
| Pivot composite UNIQUE `(a_id, b_id)` | Prevents duplicate relationships |
| `media.hash` | Deduplication on upload |

Every index is justified by a named query. An index with no query behind it is removed.

### 4.4 Query discipline

- **Eager loading is mandatory** on any path rendering a collection — enforced with
  `Model::preventLazyLoading()` outside production.
- Query logic lives in **model scopes** or dedicated query objects, never in a view or controller.
- No `SELECT *` in a read path that needs three columns.
- An N+1 in development is a **test failure**, not a performance note.

### 4.5 Migrations

- One concern per migration.
- **Every migration is reversible** — `down()` implemented, not stubbed.
- Adding a NOT NULL column to a populated table needs a default, or a three-step migration (add nullable →
  backfill → set not-null). Never a one-shot NOT NULL against live data.
- Destructive migrations live in their own commit so they can be reverted independently.

---

## 5. Authentication Architecture

### 5.1 Approach

**Laravel Fortify (headless)** + custom Blade views built from the design system.

| Feature | Provider | Notes |
|---|---|---|
| Login / logout | Fortify | Session-based, `Auth::attempt` under the hood |
| Registration | **Disabled publicly** | Admin accounts are provisioned by an existing admin or via console |
| Password reset | Fortify | Signed, expiring, single-use tokens |
| Email verification | Fortify | Required before CMS access |
| 2FA (TOTP) | Fortify | **Required for `super_admin`, optional for others** |
| Remember me | Fortify | 30 days, rotated on privilege change |
| Rate limiting | Fortify + custom | 5 attempts/min/IP + per-account lockout |

### 5.2 Why registration is closed

This is a single-owner portfolio CMS. A public registration endpoint is an attack surface with no product
value. Accounts are created by an admin or seeded via console. This is a deliberate, documented decision —
not an omission.

### 5.3 Password policy

| Rule | Value |
|---|---|
| Minimum length | **12 characters** |
| Complexity | No forced character classes — length beats composition |
| Breach check | `Password::uncompromised()` (k-anonymity HIBP check) on set and on reset |
| Hashing | bcrypt cost 12 (or argon2id where available) |
| Rotation | Never forced. Forced rotation degrades password quality |
| Reuse | Last 3 hashes retained and rejected |

### 5.4 Session security

| Setting | Value |
|---|---|
| Driver | `database` (revocable server-side) |
| `http_only` | `true` |
| `secure` | `true` in production |
| `same_site` | `lax` (strict breaks external return links; lax is the correct default here) |
| Lifetime | 120 min idle timeout, invalidated on password change |
| Regeneration | On login and on any privilege change |
| Concurrent sessions | Visible and individually revocable in the CMS |

### 5.5 Auth flow (password reset — a security-sensitive path)

```
POST /forgot-password      → rate limited 5/min → generic response ALWAYS (no user enumeration)
POST /reset-password       → validate signed token + expiry + single use
                           → update password
                           → invalidate ALL other sessions
                           → log the event with IP + UA
                           → send a "your password changed" notification
```

**No user enumeration:** identical response and timing regardless of whether the email exists.

---

## 6. Authorization and RBAC

### 6.1 The model: fixed roles + policies

**Decision: enum roles + Laravel Policies. No `spatie/laravel-permission`.**

| Option | Verdict |
|---|---|
| Runtime-editable permissions table (`spatie`) | **Rejected.** This CMS has three roles and one owner. A permission-matrix UI nobody edits is pure overhead |
| Enum role column + Policies | **Chosen.** Typed, greppable, testable, no extra table, no cache-invalidation problem |

The trade-off is explicit: **adding a fourth role is a code change plus a deploy.** Given the role set changes
approximately never, and the alternative adds a permissions table, a cache, and a UI, this is the right side
of the trade. Recorded as an accepted limitation, not an oversight.

### 6.2 Roles

| Role | Can | Cannot |
|---|---|---|
| `super_admin` | Everything, including user management, settings, destructive operations | — |
| `editor` | All content (create/edit/publish/delete), all media | Manage users, change settings, run destructive maintenance |
| `author` | Create/edit **own** content, upload media, submit for review | Publish anything, edit others' content, delete published content |

2FA is **mandatory** for `super_admin`, enforced at the middleware level rather than by convention.

### 6.3 Enforcement — defence in depth

Three independent layers. Any one alone would be insufficient:

1. **Route middleware** — `auth`, `verified`, `can:cms.access`, `role:editor|super_admin` on the route group.
   A coarse gate, not the real check.
2. **Policies** — `ProjectPolicy`, `ArticlePolicy`, `MediaPolicy`, `UserPolicy`. Every ownership rule lives
   here, called via `$this->authorize()` in controllers **and** `@can` in Blade for conditional UI.
3. **Query scoping** — an author's list query is scoped to their own `user_id`. **Hiding a button is not
   authorization; the data must not be in the response at all.**

**The rule:** the policy is the authority. Blade `@can` and route middleware are conveniences that must agree
with it. A UI element hidden by `@can` but permitted by the policy is a bug in the policy.

### 6.4 Policy method conventions

| Ability | Applies to | Rule |
|---|---|---|
| `viewAny` | list screens | role may access the section |
| `view` | single record | published → anyone; draft → owner or editor+ |
| `create` | — | role permits authorship |
| `update` | single record | owner, or editor+ |
| `delete` | single record | owner of a draft; editor+ for published |
| `publish` | single record | editor+ only |
| `restore` / `forceDelete` | single record | `super_admin` only |

### 6.5 Auditability

Every authorization failure is logged with actor, ability, subject, and route. A 403 spike is a signal, and
the log is how it becomes visible.

---

## 7. CMS Architecture

### 7.1 Surface

| Concern | Decision |
|---|---|
| Technology | Plain Blade + Alpine, in the same Laravel app |
| Route prefix | `/admin` |
| Layout | `layouts/admin.blade.php` — same tokens, denser composition |
| Navigation | Server-rendered sidebar; no client router |
| Pagination | Server-side via query params (shareable, filterable URLs) |
| Tables | Scrollable, sticky headers, bulk actions on selection |

**Why not a separate admin SPA:** it would duplicate the design system, add a second auth surface, and create
a second place where authorization could be forgotten. One app, one policy layer.

### 7.2 Content lifecycle

```
draft ──submit──▶ in_review ──approve──▶ scheduled ──(scheduler)──▶ published ──archive──▶ archived
  ▲                   │                        │                          │
  └────reject─────────┘                        └────cancel──────◀─────────┘
```

Transitions are **enforced in the domain**, not the UI. Every transition is an Action with its own
authorization check and activity-log entry. The UI only ever offers valid next states.

### 7.3 Rich text pipeline

```
Editor (headless) → HTML → Sanitiser (allow-list) → DB (content_format = 'html')
                                                        │
                          render ──▶ Sanitise AGAIN ──▶ .os-prose
```

| Layer | Rule |
|---|---|
| Allow-list | `p, br, strong, em, a, ul, ol, li, h2, h3, blockquote, code, pre, img, figure, figcaption, hr, table` |
| Attributes | `href` (scheme-restricted), `src`, `alt`, `title`, `class` (whitelisted only) |
| Forbidden | `<script>`, `<style>`, `<iframe>`, `<object>`, all `on*` handlers, `javascript:` URLs, `data:` URLs |
| Links | External links automatically get `rel="noopener noreferrer"` |
| Double sanitise | **Yes.** Write-time protects the DB; render-time protects against sanitiser regressions |

### 7.4 Settings

A `settings` table with typed values (`string|integer|boolean|json|text`) accessed via a `SettingsService` with
a request-level cache. Config the owner edits lives here; config requiring a deploy stays in `config/`. The
boundary is documented per key.

---

## 8. Media Storage Architecture

### 8.1 Layout

```
storage/app/public/media/
  {yyyy}/{mm}/
    {ulid}-{slug}.{ext}          ← original, preserved
    {ulid}-{slug}-{width}.webp   ← generated conversion
```

Served through a controller (or `storage:link`) so access can be authorised and headers controlled.

### 8.2 Pipeline

```
upload → validate (MIME sniff by CONTENT, not extension; size; dimensions)
       → hash (SHA-256) → duplicate? reuse the existing row, discard the new file
       → strip EXIF (privacy + size)
       → store the original
       → dispatch ConvertMediaToWebp (queued)
            ├─ generate 400 / 800 / 1600 / 2400 wide variants
            ├─ write MediaConversion rows
            └─ record the failure reason on the row if it fails
       → extract intrinsic width/height (CLS-free rendering)
```

### 8.3 Validation rules

| Rule | Value |
|---|---|
| Allowed types | jpg, jpeg, png, webp, avif, svg (sanitised), pdf |
| MIME check | **Content sniffing** — never the extension or the client-sent type |
| Max size | 8 MB |
| Max dimensions | 4096 × 4096 |
| SVG | Sanitised (strip `<script>`, external refs, event handlers) or rejected |
| Filename | Never trusted — regenerated from a slug |

### 8.4 Persistence abstraction

`MediaManager` implements `MediaStorage` (`app/Contracts`). The local-disk implementation is the default; an
S3 implementation can be bound in a service provider without touching any consumer. **No application code
references a disk name directly.**

### 8.5 Deletion policy

Soft delete the row first. Files are removed by a queued job after the retention window, and only if no other
row references the same hash. Deleting a shared file because one record was removed is data loss.

---

## 9. API Architecture

### 9.1 Position

**There is no public API in Phase 1.** The site is server-rendered; the CMS is server-rendered. Adding a
public REST API would create a second surface to authenticate, version, and secure, with no consumer.

### 9.2 What does exist, and why

| Endpoint class | Auth | Purpose |
|---|---|---|
| `POST /contact` | CSRF + rate limit + honeypot | Contact form (progressive enhancement, no JS needed) |
| `GET /media/{media}/{conversion}` | Signed URL | Authorised media delivery |
| `GET /sitemap.xml`, `/robots.txt` | None (public by design) | Crawler discovery |
| `GET /admin/api/search` | Session + `can:cms.access` | Autocomplete for the CMS pickers only |

### 9.3 Rules for any future API

If one is added, these are pre-decided so the decision is not revisited under pressure:

1. **Versioned from day one** (`/api/v1`). Unversioned APIs cannot evolve.
2. **Token auth (Sanctum) with explicit abilities** — never session cookies for a cross-origin consumer.
3. **Rate limited per token**, not per IP.
4. **Never returns a model directly** — always an API Resource with an explicit field list. Returning models
   leaks columns the moment someone adds one.
5. **Errors use RFC 9457 `application/problem+json`** with a stable `type` URI.
6. **Pagination is cursor-based** for anything that can grow.

---

## 10. Deployment Architecture

### 10.1 Environments

| Environment | Purpose | Data |
|---|---|---|
| **Local** (Laragon) | Development | Seeded + factories |
| **Staging** | Pre-release verification | Anonymised production copy |
| **Production** | Live | Real |

### 10.2 Release pipeline

```
push → CI (lint · static analysis · test · build) → artifact → deploy → migrate → warm → health check
                                                                              │
                                                          failure ──▶ automatic rollback
```

| Step | Command | Gate |
|---|---|---|
| 1. Lint | `vendor/bin/pint --test` | Fails on style drift |
| 2. Static analysis | `vendor/bin/phpstan analyse` (Larastan, level 6) | Fails on type errors |
| 3. Tests | `php artisan test --parallel` | Fails on any failure |
| 4. Frontend build | `npm run build` | Fails on type/asset errors |
| 5. Design validation | `node _validate.mjs` | **Fails on any token/contrast/primitive regression** |
| 6. Deploy | atomic symlink release | Zero-downtime |
| 7. Migrate | `php artisan migrate --force` | Backup taken before |
| 8. Warm | `config:cache`, `route:cache`, `view:cache`, `event:cache` | — |
| 9. Health check | `/up` | Rollback if unhealthy |

**Step 5 is unusual and deliberate:** the design system is verified by CI in the same way code is. A PR that
changes a token and breaks contrast cannot merge.

### 10.3 Production configuration

| Concern | Setting |
|---|---|
| PHP | 8.4, opcache enabled, `opcache.validate_timestamps=0` |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` — **a stack trace in production is a security incident** |
| HTTPS | Forced; HSTS with `includeSubDomains; preload` |
| Backups | Nightly DB dump + media sync, encrypted, off-site, **restore tested monthly** |
| Logging | `daily`, 30-day retention, shipped off-box |
| Queue | Supervised worker, restarted on deploy |
| Scheduler | Single cron entry: `* * * * * php artisan schedule:run` |

**A backup that has never been restored is not a backup.** The monthly restore test is part of the runbook,
not an aspiration.

---

## 11. Security Architecture

### 11.1 Baseline controls

| Control | Implementation |
|---|---|
| **Transport** | HTTPS only, TLS 1.2+, HSTS `max-age=31536000; includeSubDomains; preload` |
| **CSRF** | `VerifyCsrfToken` on all state-changing routes; `SameSite=Lax` cookie as second layer |
| **XSS** | Blade auto-escaping everywhere. `{!! !!}` is **forbidden** except for sanitised rich text, and each usage carries a comment |
| **SQL injection** | Eloquent / query builder with bindings. Raw SQL requires a comment and a review note |
| **Mass assignment** | `$fillable` on every model. `$guarded = []` is prohibited |
| **Clickjacking** | `X-Frame-Options: DENY` + `frame-ancestors 'none'` |
| **MIME sniffing** | `X-Content-Type-Options: nosniff` |
| **Referrer leakage** | `Referrer-Policy: strict-origin-when-cross-origin` |
| **Feature exposure** | `Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()` |
| **Dependency risk** | `composer audit` + `npm audit` in CI, fail on high/critical |

### 11.2 Content Security Policy

```
default-src 'self';
script-src  'self' 'nonce-{per-request}';
style-src   'self' 'nonce-{per-request}';
img-src     'self' data: blob:;
font-src    'self';
connect-src 'self';
frame-ancestors 'none';
base-uri    'self';
form-action 'self';
object-src  'none';
```

**No `unsafe-inline`, no `unsafe-eval`.** This is achievable only because the "no inline handlers" rule is
enforced from day one (`CLEAN_CODE_STANDARDS.md` §12.4) — Alpine directives are attributes, not inline
scripts, and the single bootstrapping script carries a nonce.

### 11.3 Input trust boundaries

Every one of these is untrusted and validated at the boundary:

| Input | Validation |
|---|---|
| All request data | Form Request (`rules()` + `prepareForValidation()`) |
| File uploads | MIME sniffed by **content**, size + dimension limits, EXIF stripped, filename discarded |
| Rich text | Allow-list sanitiser on write **and** render |
| URL parameters | Route-model binding with typed keys; slugs validated as slugs |
| Search / filter / sort | Allow-listed columns and directions — never interpolated into `orderBy` |
| Webhook payloads (if added) | HMAC signature verified before parsing |

### 11.4 Secrets

- All secrets live in `.env`, never committed; `.env.example` documents every key with a placeholder.
- `APP_KEY` rotation invalidates sessions and encrypted columns — the procedure is in the runbook.
- Secrets are **never** logged, never returned in a response, and never rendered back in the CMS settings UI
  (write-only fields).

### 11.5 Rate limiting

| Route | Limit | Key |
|---|---|---|
| `POST /login` | 5/min | IP + email |
| `POST /forgot-password` | 5/min | IP |
| `POST /contact` | 3/min, 10/hour | IP |
| CMS writes | 60/min | user id |
| CMS media upload | 20/min | user id |
| Public reads | 120/min | IP |

Exceeding a limit returns `429` with `Retry-After`, and repeat violations are logged for review.

### 11.6 Spam protection on the contact form

Layered, cheapest first: (1) honeypot field hidden from humans, (2) timestamp check (a sub-2s submission is a
bot), (3) rate limit, (4) optional CAPTCHA only if the first three still leak. CAPTCHA is a last resort because
it costs every legitimate user attention.

### 11.7 Privacy

- No third-party analytics, no external font CDNs, no tracking pixels.
- IPs are **hashed**, never stored raw, in the activity log and contact submissions.
- Contact messages are retained 24 months, then pruned by a scheduled job.
- A privacy page states exactly what is collected and for how long.

---

## 12. Logging and Error Handling

### 12.1 Logging strategy

| Channel | Contents | Retention |
|---|---|---|
| `daily` | Application events, warnings | 30 days |
| `security` | Auth success/failure, authorization denials, password changes, 2FA events | 180 days |
| `audit` (DB table) | Every CMS write, as an `activity_logs` row | indefinite |
| `performance` | Slow queries (>200ms), slow requests (>1s) | 14 days |

**Structured, not prose.** Every entry is JSON carrying `event`, `actor_id`, `subject_type`, `subject_id`,
`context`, and `request_id`. A log line nobody can filter is a log line nobody reads.

### 12.2 PII redaction

The log processor redacts passwords, tokens, session ids, `Authorization` headers, cookies, and any key
matching `password|secret|token|key|authorization`. **Logging a password-reset token is a security incident** —
redaction is systemic, not per-call-site discipline.

### 12.3 Error handling

```
Throwable
   ├─ Domain exception (app/Exceptions/Domain/*)  → user-safe message, 4xx, logged at warning
   ├─ Validation exception (Illuminate)           → field errors, 422
   ├─ Authorization (Illuminate)                  → 403, logged with ability + subject at notice
   ├─ Model not found (Illuminate)                → 404
   └─ Unhandled Throwable                         → 500, generic message to user,
                                                     full detail + request_id to the log only
```

| Rule | Detail |
|---|---|
| **Never leak internals** | Production shows "Something went wrong" plus a request ID — no class names, paths, or SQL |
| **Always correlate** | Every error response carries a `request_id` that also appears in the log |
| **Fail loudly in dev** | `APP_DEBUG=true` locally; a swallowed exception in development is a bug |
| **Fail closed on auth** | An exception inside an authorization check **denies** access — it never falls through to allow |
| **Custom error pages** | 404, 403, 419, 429, 500, 503 are designed pages using the design system, not bare framework output |

### 12.4 Health and observability

| Signal | Action |
|---|---|
| `GET /up` | Liveness — used by the deploy health gate |
| Queue depth | Alert above 500 pending jobs |
| Failed jobs | Alert on **any** new failed job |
| Media volume disk usage | Alert at 80% |
| 5xx rate | Alert above 1% over 5 minutes |
| 403 rate | Alert on a spike (probing signal) |

**Every alert has a documented response action.** An alert nobody knows how to act on is noise, and noise
trains people to ignore alerts.

---

## Change Log

| Date | Change | Reason |
|---|---|---|
| 2026-08-31 | Initial architecture defined (Phase 0.4) | Foundation gate |
| 2026-11-10 | Breakpoints are literal Tailwind `@theme` tokens in `design-tokens.css` | CSS custom properties cannot be resolved inside `@media`; literal values keep responsive variants valid |

---

*Projection: `PROJECT_STRUCTURE.md` maps this architecture to concrete files and directories.*
