# CLEAN_CODE_STANDARDS.md

**OBSIDIAN SIGNAL** — development standards

| | |
|---|---|
| **Status** | ✅ Approved — Phase 0.5 complete |
| **Enforcement** | Pint · PHPStan level 6 (Larastan) · `tests/Architecture/*` · `_validate.mjs` · code review |
| **Governing principle** | A rule that is not enforced is a preference. Every rule below states how it is checked |

---

## 0. The Standard

> Write code that a competent stranger can change confidently in six months without asking you a question.

That is the whole standard. Everything below is a specific, checkable consequence of it.

**Four principles, applied in this order:**

| Principle | Applied as |
|---|---|
| **Correctness** | Tests exist and pass. Types are explicit. Edge cases handled |
| **Clarity** | A name explains *why*, not *what*. Control flow is linear |
| **Changeability** | One reason to change per unit. Dependencies point one way |
| **Efficiency** | Optimise when measured, never speculatively — except N+1s, which are always fixed immediately |

**Optimise for reading, not writing.** Code is read far more often than written. A clever one-liner that
costs thirty seconds to parse has already cost more than it saved.

---

## 1. The Fifteen Mandated Rules

Transcribed from the project directive, each with its enforcement mechanism.

| # | Rule | Enforced by |
|---|---|---|
| 1 | Descriptive names for classes, functions, variables, files | Review + `pint` |
| 2 | No over-long functions | `toHaveMaxMethodLines` (12 controllers, 30 default) |
| 3 | No excessive nested conditionals | Review; guard clauses mandated (§3) |
| 4 | No duplicate code | Review + static analysis; extract on the **third** occurrence |
| 5 | No magic numbers or strings | Enums + constants; architecture test blocks bare status strings |
| 6 | Business logic separated from controllers and UI | Architecture tests on namespace boundaries |
| 7 | Centralised validation (Form Requests) | Architecture test: no `validate()` in controllers |
| 8 | Service / action layer only where needed | Review — a Service forwarding to one model is rejected |
| 9 | DB transactions for multi-step writes | Review + one test per Action asserting atomicity |
| 10 | Typed properties and return types | **PHPStan level 6** |
| 11 | No unnecessary global state | Review; singletons registered only in providers |
| 12 | Centralised exception handling | Single `Handler`; domain exceptions in `App\Exceptions\Domain` |
| 13 | Structured logging | JSON context required; `Log::info($msg)` with no context is rejected |
| 14 | Reusable query scopes | Review + `Queries/` for read composition |
| 15 | Every dependency has a clear purpose | Review — unused constructor dependencies are rejected |

**Rule 4 nuance.** Do not extract a helper for two occurrences. Two similar blocks are often *coincidentally*
similar, and extracting too early produces a function with a conditional inside — worse than the duplication.
**Extract on the third.** The rule of three is the standard.

---

## 2. Naming

| Kind | Convention | Example |
|---|---|---|
| Class | `PascalCase`, singular noun phrase | `ProjectPolicy`, `MediaManager` |
| Interface | `PascalCase`, **no `I` prefix** | `MediaStorage`, not `IMediaStorage` |
| Action | Imperative verb phrase | `PublishProject`, `ConvertMediaToWebp` |
| Query object | Noun phrase describing the read | `PublishedProjectIndex` |
| Boolean | `is_`, `has_`, `can_`, `should_` | `isFeatured()`, `canPublish()` |
| Method | Verb phrase | `publish()`, not `publishStatus()` |
| Variable | Noun, no type prefix | `$project`, not `$projectModel` |
| Enum case | `PascalCase` | `ContentStatus::InReview` |
| DB column | `snake_case` | `published_at` |
| Migration | `verb_noun_table` | `create_projects_table` |
| Blade view | `kebab-case` | `project-card.blade.php` |
| CSS class | `.os-kebab` | `.os-project-card__title` |
| Test | Sentence stating behaviour | `it_publishes_a_reviewed_article` |

**Banned names** — they claim meaning they do not have:

> `data` · `info` · `temp` · `obj` · `item` · `thing` · `stuff` · `handle` (as a noun) · `process` (as a noun) ·
> `util` · `helper` · `manager` (unless it genuinely manages a lifecycle) · `$d` · `$x` · `$arr` · `$res`

A name that needs a comment to explain it is the wrong name.

---

## 3. Function Design

| Standard | Value |
|---|---|
| Max lines — controller action | **12** |
| Max lines — Action `__invoke` | **30** |
| Max lines — any other method | **30** |
| Max parameters | **4** (beyond that, introduce a DTO) |
| Nesting depth | **2 levels** max |
| Return statements | 1 happy path; guard clauses handle the rest |
| Side effects per method | One thing — and the name says so |

**A function should do one thing.** The test: describe it in one sentence without using the word "and".

### The early-return rule (rule 3, in practice)

```php
// ❌ Nested — the reader must hold three conditions in their head at once
public function publish(Article $article): void
{
    if ($article->status === ContentStatus::InReview) {
        if (Gate::allows('publish', $article)) {
            if ($article->content !== null) {
                $this->doPublish($article);
            } else {
                throw new CannotPublishUnreviewedContent('Article has no content.');
            }
        } else {
            throw new AuthorizationException();
        }
    } else {
        throw new CannotPublishUnreviewedContent('Article is not in review.');
    }
}
```

```php
// ✅ Guard clauses — each precondition is one readable line
public function publish(Article $article): void
{
    throw_unless($article->status === ContentStatus::InReview,
        new CannotPublishUnreviewedContent('Article is not in review.'));

    throw_unless($article->content !== null,
        new CannotPublishUnreviewedContent('Article has no content.'));

    Gate::authorize('publish', $article);

    $this->doPublish($article);
}
```

The happy path sits at the bottom, unindented, readable at a glance.

---

## 4. Constants and Magic Values

**Every literal that carries meaning gets a name.**

```php
// ❌ What is 8? Why 4096?
if ($file->size() > 8 * 1024 * 1024 || $image->width() > 4096) { … }

// ✅ The rule has a name, and the name appears in the error message
final class MediaRules
{
    public const int MAX_SIZE_BYTES = 8 * 1024 * 1024;   // 8 MB
    public const int MAX_DIMENSION  = 4096;
}
```

**Where each value belongs:**

| Kind of value | Home |
|---|---|
| Valid set of domain states | **Enum** (`app/Enums`) |
| Tunable operational limit | `config/media.php` |
| Owner-editable copy or value | `settings` table |
| Visual value | **CSS token** (`design-tokens.css`) |
| Local, single-use, self-evident | Inline (`$index + 1`) |
| Anything else | A class constant |

**The CSS corollary:** `components.css` may contain **zero** raw hex values. That is not a preference — the
validator fails the build (`DESIGN_SYSTEM.md` §4).

---

## 5. Type Safety

PHPStan level 6 is the floor, not the goal.

```php
// Every method declares its return type — including void and never
public function publish(Article $article, User $actor): Article

// Constructor promotion with readonly for immutable collaborators
public function __construct(
    private readonly MediaStorage $storage,
    private readonly HtmlSanitiser $sanitiser,
) {}

// A DTO instead of five scalar parameters
final readonly class ProjectData
{
    public function __construct(
        public string $title,
        public string $summary,
        public ?string $description = null,
        public ContentStatus $status = ContentStatus::Draft,
    ) {}
}
```

**Rules.**
1. **No `mixed` without a comment** justifying it.
2. **No bare `array`** without a shape docblock or a DTO.
3. **Nullable is explicit** — `?string`, never implied.
4. **`readonly` by default** on value objects and injected dependencies.
5. **Casts on every model attribute** that is not a plain string/int/bool.
6. **`declare(strict_types=1)`** in every PHP file.

---

## 6. Class Design (SOLID, applied not recited)

| Principle | How it shows up here |
|---|---|
| **S**ingle responsibility | One Action = one use case. One Policy = one model's rules. The line limits are a proxy for this |
| **O**pen/closed | A new project type is a new enum case + a renderer, not an `if` in five places |
| **L**iskov | Implementations of `MediaStorage` are interchangeable; no implementation throws where the interface does not |
| **I**nterface segregation | `MediaStorage` and `HtmlSanitiser` are separate — nothing implements methods it ignores |
| **D**ependency inversion | Actions depend on `MediaStorage`, never on `Storage::disk('public')` |

**Dependency injection discipline:**
- Constructor injection only. **`app()` inside a method is rejected** — it hides the dependency and makes the
  class untestable without booting the container.
- Every injected dependency is used or removed. An unused constructor parameter is a defect.

**Composition over inheritance.** Inheritance is permitted only for a base class that shares *implementation*
(e.g. an abstract `Query`). "Reuse" alone is not a reason to extend.

---

## 7. Separation of Concerns

The hardest boundary to keep honest: **presentation renders, domain decides.**

```php
// ❌ Controller deciding business rules
public function store(Request $request)
{
    $data = $request->validate([...]);
    if (Project::where('slug', Str::slug($data['title']))->exists()) {
        return back()->withErrors(['title' => 'Slug taken.']);
    }
    $project = Project::create($data);
    foreach ($data['technologies'] ?? [] as $id) {
        $project->technologies()->attach($id);
    }
    Mail::to(config('mail.admin'))->queue(new ProjectCreated($project));
    return redirect()->route('admin.projects.index');
}
```

```php
// ✅ Controller orchestrates
public function store(StoreProjectRequest $request, CreateProject $create): RedirectResponse
{
    $this->authorize('create', Project::class);

    $project = $create(ProjectData::fromRequest($request), $request->user());

    return redirect()
        ->route('admin.projects.edit', $project)
        ->with('status', __('cms.projects.created'));
}
```

**The boundary, stated once:**

| Layer | Owns | Must never |
|---|---|---|
| Controller | HTTP translation, authorisation call, response | Business rules, queries, HTML |
| Form Request | Input validation + normalisation | Persistence, decisions |
| Action | One use case, atomic, transactional | HTTP concerns, view data |
| Model | Relations, casts, scopes, invariants | HTTP, mail, filesystem side effects |
| Service | Orchestration across Actions, external boundaries | Request/response awareness |
| View | Rendering | Queries, business conditionals |
| Alpine | Local UI state | Business decisions, authoritative data |

---

## 8. Transactions

**Rule 9 in practice:** any operation performing more than one write is wrapped in a transaction, and the
transaction belongs to the **Action**, not the controller.

```php
final class CreateProject
{
    public function __invoke(ProjectData $data, User $author): Project
    {
        return DB::transaction(function () use ($data, $author): Project {
            $project = $author->projects()->create([
                'title'   => $data->title,
                'slug'    => $this->slugger->unique($data->title, Project::class),
                'summary' => $data->summary,
                'status'  => $data->status,
            ]);

            $project->technologies()->sync($data->technologyIds);
            $project->tags()->sync($data->tagIds);

            ActivityLog::record('project.created', $project, $author);

            return $project->refresh();
        });
    }
}
```

**Rules.**
1. **Side effects that cannot be rolled back go outside the transaction** — email, queue dispatch, and file
   writes happen *after* commit, never inside.
2. **Never swallow an exception inside a transaction** to "continue anyway".
3. **Short transactions.** Do not hold a transaction open across an HTTP call.
4. **`DB::transaction` takes a closure** so a forgotten `rollBack()` is impossible.
5. **One test per Action asserts atomicity:** force a failure mid-way and assert nothing was written.

---

## 9. Validation

**All validation lives in Form Requests.** This is rule 7, and it is enforced by an architecture test.

```php
final class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) ?? false;
        // Authorisation here is the FIRST gate; the Policy remains the authority.
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title'   => trim((string) $this->input('title')),
            'summary' => trim((string) $this->input('summary')),
            // Empty string -> null, so "unset" and "empty" are one state, not two.
            'client'  => $this->filled('client') ? $this->input('client') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:180'],
            'summary'           => ['required', 'string', 'max:320'],
            'description'       => ['nullable', 'string'],
            'status'            => ['required', Rule::enum(ContentStatus::class)],
            'cover_media_id'    => ['nullable', Rule::exists('media', 'id')],
            'technology_ids'    => ['array'],
            'technology_ids.*'  => [Rule::exists('technologies', 'id')],
            'repo_url'          => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('validation.projects.title_required'),
            'summary.max'    => __('validation.projects.summary_too_long'),
        ];
    }
}
```

**Rules.**
1. **`messages()` is always a named array** — never a naked string.
2. **`attributes()` maps field names to human labels** so an error reads "Meta title is required", not
   "meta_title is required".
3. **`prepareForValidation()` normalises** — trimming and empty-to-null happen here, in one place.
4. **`Rule::enum()` and `Rule::exists()`** rather than string comparisons and manual lookups.
5. **Client-side validation is an affordance, never a guarantee.** Alpine may warn; the server decides.
6. **The same rules are not duplicated anywhere else.** No validation in models, controllers, or Vue-less JS.

---

## 10. Error Handling and Logging

### 10.1 Domain exceptions

```php
namespace App\Exceptions\Domain;

final class CannotPublishUnreviewedContent extends DomainException
{
    public static function notInReview(Article $article): self
    {
        return new self(__('exceptions.publish.not_in_review', ['title' => $article->title]));
    }
}
```

| Rule | Detail |
|---|---|
| One exception class per failure mode | Named for the *problem*, not the *layer* |
| Always extend a domain base class | `DomainException` — so `Handler` can treat them uniformly |
| Named constructors | `::notInReview($article)` produces a message with real context |
| User-safe messages | The message is rendered to the user, so it must be free of internals |
| Never a bare `\Exception` | A generic exception cannot be caught specifically |

### 10.2 Centralised rendering

All rendering lives in `App\Exceptions\Handler` — never `try/catch` in a controller to produce a response.

```php
public function register(): void
{
    $this->renderable(function (DomainException $e, Request $request) {
        return response()->view('errors.domain', ['message' => $e->getMessage()], 422);
    });
}
```

### 10.3 Structured logging (rule 13)

```php
// ❌ Unfilterable prose
Log::info('Project published');

// ✅ Structured, correlated, redaction-safe
Log::info('project.published', [
    'actor_id'      => $actor->id,
    'subject_type'  => Project::class,
    'subject_id'    => $project->id,
    'status'        => $project->status->value,
    'request_id'    => app(RequestId::class)->value(),
]);
```

**Rules.**
1. **The first argument is a stable event key** (`resource.action`), not a sentence. It is greppable forever.
2. **Context is an array**, never string interpolation.
3. **Never log a secret.** Redaction is systemic (`Support/Logging/RedactSensitiveData`) — but do not rely on
   it as the first line of defence.
4. **Log level means something:** `debug` (dev only) · `info` (state change) · `warning` (recoverable
   surprise) · `error` (action needed) · `critical` (page someone).
5. **If nothing will ever read it, do not log it.** Log noise is worse than no log.

---

## 11. Queries and Model Scopes

**Rule 14 in practice.** Query logic lives in scopes or query objects — never in a view, never in a controller.

```php
// Model scope — reusable, composable, testable
public function scopePublished(Builder $query): Builder
{
    return $query->where('status', ContentStatus::Published)
                 ->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
}

// Scopes compose
Project::query()->published()->featured()->ordered()->get();
```

**Rules.**
1. **Eager-load explicitly** — `with(['technologies', 'coverMedia'])`. Never rely on lazy loading.
2. **Select only what is needed** in read paths: `->select(['id', 'title', 'slug', 'summary'])`.
3. **`orderBy` columns come from an allow-list**, never from request input.
4. **Read composition that spans models belongs in a `Queries/` class**, which returns a view model — not a
   collection the view must interpret.
5. **`Model::preventLazyLoading()` is on** outside production, so an N+1 fails loudly in development.

---

## 12. Frontend Standards

### 12.1 CSS

| Rule | Enforced by |
|---|---|
| Zero raw hex values in `components.css` | `_validate.mjs` |
| Zero arbitrary spacing / font-size values | `_validate.mjs` |
| Every class prefixed `.os-` | `_validate.mjs` |
| No `[data-theme]` override in a component file | `_validate.mjs` |
| No `!important` | Review |
| No `overflow: hidden` on `body` | Review |

**Tokens first.** If a value is missing, add a token — never hardcode it at the call site. A one-off value is
how a design system dies.

**Tailwind's role is layout and composition** — flex, grid, gap, spacing, responsive variants. Colour, type,
radius, elevation, and motion come from tokens and `.os-*` classes. The boundary is documented in
`resources/css/app.css`.

### 12.2 JavaScript / Alpine

```js
// ✅ A module for anything beyond trivial state
export function mediaPicker({ selected }) {
  let current = [...selected]

  return {
    current,
    isSelected: (id) => current.includes(id),
    toggle(id) {
      current = current.includes(id)
        ? current.filter((x) => x !== id)
        : [...current, id]
    },
  }
}
```

| Rule | Detail |
|---|---|
| **No business logic** | Alpine toggles views; it never decides outcomes |
| **State comes from the server** | Read from `data-*` attributes, never invented |
| **One scope per concern** | Under ~30 lines; beyond that, extract a module |
| **No inline handlers** | `onclick` in HTML is banned (CSP) |
| **Clean up listeners** | Anything added in `x-init` is removed on `destroy` |
| **No `innerHTML` with user data** | XSS — use `textContent` or a sanitised path |
| **No globals** | Everything lives in a module or an Alpine scope |

### 12.3 Blade

| Rule | Detail |
|---|---|
| `{{ }}` always; `{!! !!}` only for sanitised content, with a comment | XSS |
| No queries, no `Model::` calls | Layering |
| No hardcoded user-facing strings | `__()` for every label |
| `@class()` over string concatenation | Readability |
| `@props` with typed comments | Contracts |
| Accessibility attributes are mandatory | `aria-*`, `role`, labels |

### 12.4 The no-inline-handler rule — why it is architectural

Rules 12.2 and 12.3 exist for one concrete reason: they make a **strict CSP possible with no `unsafe-inline`**.
A single `onclick` anywhere would force the whole application to weaken its policy. The rule is therefore
structural, not stylistic (`ARCHITECTURE.md` §11.2).

---

## 13. Comments and Documentation

**Write code that does not need comments. When a comment is necessary, it explains WHY, not WHAT.**

```php
// ❌ WHAT — the code already says this
// Increment the counter by one
$project->increment('view_count');

// ✅ WHY — explains a non-obvious decision
// Incremented asynchronously so a bot crawling the index cannot skew the
// number or hold a write lock during page render.
IncrementViewCount::dispatch($project->id)->onQueue('metrics');
```

| Comment type | Bar |
|---|---|
| Why-decision | **Required** for any non-obvious choice |
| Contract / invariant | **Required** — e.g. "callers pass IDs, not models" |
| Escape hatch | **Required** — any `{!! !!}`, raw SQL, or disabled rule explains itself |
| Reference | **Required** — link to the design doc or issue |
| WHAT-comment | **Deleted.** The code says it |
| Change log in a comment | **Deleted.** Git records history |
| Commented-out code | **Deleted.** Not a backup |

**Docblocks** are required where the signature is not self-evident (array shapes, generics, thrown
exceptions) and unnecessary on a well-named one-line method.

---

## 14. DRY, KISS, YAGNI — with thresholds

These are the most-abused principles in software, so each gets a concrete threshold instead of a slogan.

| Principle | Applied literally | Threshold |
|---|---|---|
| **DRY** | Do not repeat *knowledge*, not *characters*. Two similar blocks with different reasons to change are not duplication | Extract on the **third** occurrence |
| **KISS** | The simplest thing that satisfies the requirement and its tests | If it needs a diagram to explain, simplify it |
| **YAGNI** | Build for the requirement, not the imagined one | No abstraction with fewer than **three** real call sites |
| **Law of Demeter** | Talk to collaborators, not their internals | `$order->customer->address` (3 hops) is a smell |
| **Boy Scout** | Leave it better, *within the scope of the current task* | Never drive-by refactor someone else's file |

### The anti-over-engineering guard

This project explicitly rejects: a repository layer over Eloquent for every model · a generic `BaseService` ·
an event bus with one subscriber · interfaces with one implementation and no test double · and any abstraction
justified by "for the future".

Each is a cost paid now for a benefit that may never arrive.

---

## 15. Git and Commits

### 15.1 Conventional commits

```
type(scope): imperative subject, max 72 chars

Body: why this change was necessary, wrapped at 72 columns.
Not what changed — the diff already shows that.

Refs: ARCHITECTURE.md §7.3
```

| Type | Use |
|---|---|
| `feat` | A new capability |
| `fix` | A bug fix |
| `refactor` | Behaviour-preserving change |
| `perf` | Performance change |
| `test` | Tests only |
| `docs` | Documentation only |
| `style` | Formatting, no behaviour change |
| `chore` | Build, dependencies, tooling |
| `a11y` | Accessibility improvement |
| `design` | Design system / token change |

### 15.2 Rules

1. **One logical change per commit.** A commit that adds a feature *and* reformats a file cannot be reviewed.
2. **The subject is imperative:** "add", not "added" or "adds".
3. **The body explains why.** "Fix bug" tells a future reader nothing.
4. **No commit without passing CI** — lint, analyse, test, build, validate.
5. **A `design` commit that changes a token must include the updated `DESIGN_SYSTEM.md`** in the *same*
   commit. This is how design drift is prevented structurally rather than by memory.

---

## 16. Code Review Checklist

The reviewer confirms each line. "Looks fine" is not a review.

**Correctness**
- [ ] Does what the description claims, and tests prove it
- [ ] Edge cases handled: empty, null, single item, maximum, concurrent
- [ ] Failure paths behave — no swallowed exceptions
- [ ] Multi-step writes are transactional

**Design**
- [ ] Dependencies point downward (controller → action → domain)
- [ ] No business logic in a controller, view, or Alpine component
- [ ] One responsibility per class; names say what it is
- [ ] No abstraction with fewer than three real call sites

**Security**
- [ ] Input validated at the boundary
- [ ] Output escaped; `{!! !!}` justified in a comment
- [ ] Authorization enforced by policy, not just a hidden button
- [ ] No secret in code, log, or response

**Design system**
- [ ] No raw hex, arbitrary spacing, or arbitrary font size
- [ ] Uses `.os-*` primitives rather than bespoke markup
- [ ] Verified in both light and dark mode
- [ ] Focus states present and visible

**Accessibility**
- [ ] Keyboard-operable end to end
- [ ] Icon-only controls have accessible names
- [ ] State conveyed by more than colour
- [ ] Headings follow document order

**Performance**
- [ ] No N+1 (verified with `preventLazyLoading`)
- [ ] Queries select only the needed columns
- [ ] No unbounded collection loaded into memory

**Documentation**
- [ ] Why-comments on non-obvious decisions
- [ ] Governing document updated if a rule or token changed
- [ ] No commented-out code

---

## Change Log

| Date | Change | Reason |
|---|---|---|
| 2026-08-31 | Initial standards defined (Phase 0.5) | Foundation gate |

---

*Enforcement surfaces: `pint.json` · `phpstan.neon` (level 6) · `tests/Architecture/*` · `_validate.mjs` ·
`.github/workflows/ci.yml` gates 1–5.*
