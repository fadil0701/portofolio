# DATABASE_SCHEMA.md

**OBSIDIAN SIGNAL** — data model

| | |
|---|---|
| **Status** | ✅ Approved — Phase 0.4 complete |
| **Engine** | InnoDB · `utf8mb4` · `utf8mb4_unicode_ci` |
| **Keys** | `bigIncrements` PKs, never exposed publicly; public routes use `slug` |
| **Enums** | `VARCHAR(32)` + PHP enum cast (see `ARCHITECTURE.md` §4.2) |
| **Timezone** | All timestamps stored UTC; rendered in the configured app timezone |

---

## 0. Overview

```
                          ┌──────────┐
                          │  users   │
                          └────┬─────┘
        ┌──────────────────────┼───────────────────────┐
        │                      │                       │
   ┌────▼─────┐          ┌─────▼──────┐          ┌─────▼───────┐
   │ profiles │          │  articles  │          │activity_logs │
   └──────────┘          └─────┬──────┘          └─────────────┘
                               │
   ┌───────────┐   ┌───────────▼───┐   ┌──────────┐
   │   media   │◀──│   projects    │──▶│   tags   │
   └─────┬─────┘   └───────┬───────┘   └──────────┘
         │                 │
         │          ┌──────▼────────┐
         │          │ technologies  │
         │          └───────────────┘
   ┌─────▼──────┐  ┌───────────────┐  ┌──────────────┐
   │ conversions│  │ project_media │  │ experiences  │
   └────────────┘  └───────────────┘  └──────────────┘

   ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
   │ skill_groups │ │    skills    │ │  expertise   │
   └──────────────┘ └──────────────┘ └──────────────┘

   ┌──────────┐ ┌──────────────┐ ┌──────────┐ ┌──────────┐
   │ services │ │ testimonials │ │  pages   │ │ settings │
   └──────────┘ └──────────────┘ └──────────┘ └──────────┘

   ┌──────────────────┐ ┌─────────────────┐ ┌────────────────────┐
   │ contact_messages │ │ newsletter_subs │ │ content_revisions  │
   └──────────────────┘ └─────────────────┘ └────────────────────┘
```

---

## 1. Users & Identity

### `users`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK, auto | |
| `name` | varchar(120) | NOT NULL | |
| `email` | varchar(190) | NOT NULL, UNIQUE | Lowercased on save |
| `email_verified_at` | timestamp | NULL | Required before CMS access |
| `password` | varchar(255) | NOT NULL | bcrypt cost 12 / argon2id |
| `role` | varchar(32) | NOT NULL, default `author` | `super_admin` \| `editor` \| `author` |
| `two_factor_secret` | text | NULL | Encrypted |
| `two_factor_recovery_codes` | text | NULL | Encrypted |
| `two_factor_confirmed_at` | timestamp | NULL | Mandatory for `super_admin` |
| `last_login_at` | timestamp | NULL | |
| `last_login_ip_hash` | char(64) | NULL | SHA-256 — never the raw IP |
| `is_active` | boolean | default true | Soft disable without deletion |
| `remember_token` | varchar(100) | NULL | |
| `created_at` / `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULL | Soft delete — the audit trail must survive |

**Indexes.** `email` UNIQUE · `role` · `is_active`.
**Why `is_active` in addition to soft delete:** disabling access and removing a record are different
operations. FK history in `activity_logs` must remain intact after an offboarding.

### Framework tables
`password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `notifications`.

### `profiles` — singleton

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `user_id` | bigint unsigned | FK → users, UNIQUE | One profile per user |
| `display_name` | varchar(120) | NOT NULL | |
| `headline` | varchar(180) | NULL | One-line positioning statement |
| `bio` | text | NULL | Sanitised HTML |
| `bio_format` | varchar(16) | default `html` | `html` \| `markdown` |
| `avatar_media_id` | bigint unsigned | FK → media, NULL, `nullOnDelete` | |
| `resume_media_id` | bigint unsigned | FK → media, NULL, `nullOnDelete` | |
| `location` | varchar(120) | NULL | |
| `email_public` | varchar(190) | NULL | Distinct from the login email |
| `phone_public` | varchar(40) | NULL | |
| `availability` | varchar(32) | default `unavailable` | `available` \| `limited` \| `unavailable` |
| `availability_note` | varchar(180) | NULL | e.g. "Available · Q1 2026" |
| `years_experience` | tinyint unsigned | NULL | |
| `projects_delivered` | smallint unsigned | NULL | |
| `timezone` | varchar(64) | NULL | |
| `created_at` / `updated_at` | timestamp | | |

**Why the stat columns are real fields, not computed:** they are *claims* the owner makes, not derived facts.
Computing them from the project count would silently rewrite the homepage the moment a draft is added.

---

## 2. Content — Projects

### `projects`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `user_id` | bigint unsigned | FK → users, NOT NULL | Author / owner |
| `title` | varchar(180) | NOT NULL | |
| `slug` | varchar(190) | NOT NULL, UNIQUE | Generated, editable |
| `summary` | varchar(320) | NOT NULL | Card copy — length enforced, not suggested |
| `description` | longtext | NULL | Sanitised rich text |
| `content_format` | varchar(16) | default `html` | `html` \| `markdown` |
| `cover_media_id` | bigint unsigned | FK → media, NULL, `nullOnDelete` | |
| `client` | varchar(140) | NULL | |
| `role` | varchar(140) | NULL | What the owner actually did |
| `team_size` | tinyint unsigned | NULL | |
| `project_type` | varchar(32) | default `web_app` | `web_app` \| `platform` \| `library` \| `experiment` \| `client_work` |
| `status` | varchar(32) | NOT NULL, default `draft` | `draft` \| `in_review` \| `scheduled` \| `published` \| `archived` |
| `published_at` | timestamp | NULL | A future value means scheduled |
| `is_featured` | boolean | default false | |
| `featured_rank` | smallint unsigned | NULL | Manual ordering; NULL when unranked |
| `sort_order` | int | default 0 | |
| `year` | smallint unsigned | NULL | |
| `started_on` / `ended_on` | date | NULL | |
| `is_ongoing` | boolean | default false | |
| `repo_url` / `live_url` / `case_study_url` | varchar(500) | NULL | |
| `view_count` | int unsigned | default 0 | Denormalised counter, incremented asynchronously |
| `meta_title` | varchar(190) | NULL | Falls back to `title` |
| `meta_description` | varchar(320) | NULL | Falls back to `summary` |
| `og_media_id` | bigint unsigned | FK → media, NULL, `nullOnDelete` | |
| `canonical_url` | varchar(500) | NULL | |
| `is_noindex` | boolean | default false | |
| `structured_data` | json | NULL | JSON-LD overrides |
| `created_at` / `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULL | Soft delete |

**Indexes.** `slug` UNIQUE · `status` · `(status, published_at)` · `is_featured` · `user_id` · `sort_order` ·
FULLTEXT `(title, summary)`.
**Casts.** `status` → `ProjectStatus` · `project_type` → `ProjectType` · `is_*` → bool · dates → `date` ·
`structured_data` → `array`.
**Soft deletes.** Yes — a deleted project must be recoverable, and a hard delete would break `activity_logs`.

### `technologies`

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK |
| `name` | varchar(80) | NOT NULL, UNIQUE |
| `slug` | varchar(80) | NOT NULL, UNIQUE |
| `category` | varchar(32) | NOT NULL — `language` \| `framework` \| `database` \| `tool` \| `cloud` \| `design` |
| `icon_media_id` | bigint unsigned | FK → media, NULL, `nullOnDelete` |
| `brand_colour` | char(7) | NULL — only for a restrained tint, never a rainbow |
| `url` | varchar(500) | NULL |
| `created_at` / `updated_at` | timestamp | |

### `project_technology` (pivot, alphabetical)

| Column | Type | Constraints |
|---|---|---|
| `project_id` | bigint unsigned | FK → projects, `cascadeOnDelete` |
| `technology_id` | bigint unsigned | FK → technologies, `cascadeOnDelete` |
| `usage` | varchar(16) | default `primary` — `primary` \| `supporting` |

**PK.** Composite `(project_id, technology_id)` — a project cannot list the same technology twice.
**Why the `usage` column:** it lets a card surface two or three headline technologies and relegate the rest,
instead of rendering an undifferentiated wall of badges (`DESIGN_SYSTEM.md` §5.5).

### `project_media` (gallery)

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK |
| `project_id` | bigint unsigned | FK → projects, `cascadeOnDelete` |
| `media_id` | bigint unsigned | FK → media, `cascadeOnDelete` |
| `caption` | varchar(280) | NULL |
| `sort_order` | int | default 0 |
| `created_at` / `updated_at` | timestamp | |

**Index.** `(project_id, sort_order)`.

---

## 3. Content — Articles

### `articles`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `user_id` | bigint unsigned | FK → users, NOT NULL | |
| `title` | varchar(180) | NOT NULL | |
| `slug` | varchar(190) | NOT NULL, UNIQUE | |
| `excerpt` | varchar(320) | NOT NULL | |
| `content` | longtext | NOT NULL | Sanitised |
| `content_format` | varchar(16) | default `html` | |
| `cover_media_id` | bigint unsigned | FK → media, NULL, `nullOnDelete` | |
| `status` | varchar(32) | NOT NULL, default `draft` | same lifecycle as projects |
| `published_at` | timestamp | NULL | |
| `reading_time_minutes` | tinyint unsigned | NULL | Computed on save from word count |
| `view_count` | int unsigned | default 0 | |
| `is_featured` | boolean | default false | |
| `meta_title` / `meta_description` | varchar | NULL | |
| `og_media_id` | bigint unsigned | FK → media, NULL, `nullOnDelete` | |
| `canonical_url` | varchar(500) | NULL | |
| `is_noindex` | boolean | default false | |
| `created_at` / `updated_at` / `deleted_at` | timestamp | | |

**Indexes.** `slug` UNIQUE · `(status, published_at)` · FULLTEXT `(title, excerpt, content)`.

### `tags` and pivots

**`tags`.** `id` · `name` varchar(60) UNIQUE · `slug` varchar(60) UNIQUE · `description` varchar(280) NULL.

**`article_tag` / `project_tag`.** `article_id`|`project_id` FK `cascadeOnDelete` · `tag_id` FK
`cascadeOnDelete`. Composite PK.
**Why one shared `tags` table:** a tag means the same thing on an article and a project, so a single taxonomy
keeps the archive coherent. Two tables would drift apart.

---

## 4. Content — Pages, Experience, Skills, Services

### `pages`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `title` | varchar(180) | NOT NULL | |
| `slug` | varchar(190) | NOT NULL, UNIQUE | |
| `template` | varchar(32) | default `default` | `default` \| `about` \| `contact` \| `legal` |
| `content` | longtext | NULL | Sanitised |
| `content_format` | varchar(16) | default `html` | |
| `blocks` | json | NULL | Typed block array — rendered by a whitelist registry |
| `status` | varchar(32) | default `draft` | |
| `published_at` | timestamp | NULL | |
| `meta_title` / `meta_description` | varchar | NULL | |
| `is_noindex` | boolean | default false | |
| `sort_order` | int | default 0 | Navigation order |
| `show_in_nav` | boolean | default false | |
| `created_at` / `updated_at` / `deleted_at` | timestamp | | |

**The `blocks` contract.** A JSON array of `{ type, data }`. `type` must match a registered renderer; an
unknown type is **skipped and logged**, never rendered raw. This makes the block system a whitelist by
construction rather than by care.

### `experiences`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `company` | varchar(140) | NOT NULL | |
| `role` | varchar(140) | NOT NULL | |
| `employment_type` | varchar(32) | default `full_time` | `full_time` \| `contract` \| `freelance` \| `internship` |
| `location` | varchar(140) | NULL | |
| `is_remote` | boolean | default false | |
| `started_on` | date | NOT NULL | |
| `ended_on` | date | NULL | NULL = current |
| `is_current` | boolean | default false | Must agree with `ended_on IS NULL` |
| `description` | text | NULL | Sanitised |
| `achievements` | json | NULL | Array of strings — never queried, so JSON is correct |
| `company_url` | varchar(500) | NULL | |
| `logo_media_id` | bigint unsigned | FK → media, NULL, `nullOnDelete` | |
| `sort_order` | int | default 0 | |
| `created_at` / `updated_at` | timestamp | | |

**Why `achievements` is JSON:** it is a presentation list, never filtered or joined. A child table would add a
join and a model for zero query capability.

### `skill_groups` / `skills`

**`skill_groups`.** `id` · `name` varchar(80) · `slug` UNIQUE · `description` varchar(280) NULL ·
`icon` varchar(40) NULL (Lucide name) · `sort_order` int · `is_active` bool.

**`skills`.** `id` · `skill_group_id` FK `cascadeOnDelete` · `name` varchar(80) · `proficiency` tinyint
unsigned (0–100) · `level` varchar(16) — `beginner` \| `intermediate` \| `advanced` \| `expert` · `years`
tinyint unsigned NULL · `sort_order` int · `is_featured` bool.
**Index.** `(skill_group_id, sort_order)`.
**Why both `proficiency` and `level`:** the bar is decorative; `level` is the value a screen reader announces
(`DESIGN_SYSTEM.md` §18.2). A bar with no textual value is inaccessible.

### `services` / `testimonials`

**`services`.** `id` · `title` varchar(140) · `slug` UNIQUE · `description` text · `icon` varchar(40) NULL ·
`deliverables` json NULL · `sort_order` int · `is_active` bool.

**`testimonials`.** `id` · `quote` text NOT NULL · `author_name` varchar(120) NOT NULL · `author_role`
varchar(140) NULL · `author_company` varchar(140) NULL · `avatar_media_id` FK → media NULL · `project_id`
FK → projects NULL `nullOnDelete` · `rating` tinyint unsigned NULL (1–5) · `is_featured` bool · `sort_order` int.

---

## 5. Media

### `media`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `uploaded_by` | bigint unsigned | FK → users, NULL, `nullOnDelete` | |
| `disk` | varchar(32) | default `public` | Resolved through `MediaStorage`, never used directly |
| `directory` | varchar(255) | NOT NULL | `media/{yyyy}/{mm}` |
| `filename` | varchar(255) | NOT NULL | Generated — never the client's name |
| `original_filename` | varchar(255) | NULL | Retained for reference only |
| `extension` | varchar(16) | NOT NULL | |
| `mime_type` | varchar(120) | NOT NULL | **Sniffed from content** |
| `size_bytes` | bigint unsigned | NOT NULL | |
| `width` / `height` | smallint unsigned | NULL | Intrinsic dimensions — required to avoid CLS |
| `hash` | char(64) | NOT NULL | SHA-256 — deduplication key |
| `alt_text` | varchar(280) | NULL | **Required by the UI** for non-decorative media |
| `title` / `caption` | varchar | NULL | |
| `dominant_colour` | char(7) | NULL | Placeholder background while loading |
| `conversions` | json | NULL | `{ label: path }` map for fast rendering |
| `processing_status` | varchar(16) | default `pending` | `pending` \| `processing` \| `ready` \| `failed` |
| `processing_error` | varchar(500) | NULL | Surfaced in the CMS so failures are visible, not silent |
| `created_at` / `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULL | Soft delete first; the file is removed later |

**Indexes.** `hash` · `processing_status` · `uploaded_by`.
**Why `hash` deduplication:** the same hero image uploaded twice should cost disk once, and the CMS should say
"this image already exists" instead of silently doubling storage.

### `media_conversions`

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK |
| `media_id` | bigint unsigned | FK → media, `cascadeOnDelete` |
| `label` | varchar(32) | NOT NULL — `thumb` \| `sm` \| `md` \| `lg` \| `xl` |
| `width` / `height` | smallint unsigned | NOT NULL |
| `format` | varchar(16) | NOT NULL — `webp` \| `avif` \| `jpg` |
| `path` | varchar(500) | NOT NULL |
| `size_bytes` | bigint unsigned | NOT NULL |
| `created_at` | timestamp | |

**Index.** UNIQUE `(media_id, label, format)`.

---

## 6. CMS Support

### `contact_messages`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `name` | varchar(120) | NOT NULL | |
| `email` | varchar(190) | NOT NULL | |
| `company` | varchar(140) | NULL | |
| `subject` | varchar(190) | NULL | |
| `message` | text | NOT NULL | |
| `budget_range` | varchar(40) | NULL | Fixed enum set, never free text |
| `status` | varchar(16) | default `new` | `new` \| `read` \| `replied` \| `archived` \| `spam` |
| `replied_at` | timestamp | NULL | |
| `internal_notes` | text | NULL | Never exposed publicly |
| `ip_hash` | char(64) | NULL | SHA-256 — the raw IP is never stored |
| `user_agent` | varchar(255) | NULL | Truncated |
| `spam_score` | tinyint unsigned | NULL | From the layered checks |
| `created_at` / `updated_at` | timestamp | | |

**Indexes.** `status` · `created_at` · `email`.
**Retention.** Pruned at 24 months by a scheduled job (`ARCHITECTURE.md` §11.7).

### `activity_logs`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `causer_id` | bigint unsigned | FK → users, NULL, `nullOnDelete` | NULL = system action |
| `subject_type` | varchar(190) | NOT NULL | Morph |
| `subject_id` | bigint unsigned | NOT NULL | Morph |
| `event` | varchar(64) | NOT NULL | `project.published`, `media.uploaded`, `user.role_changed` |
| `description` | varchar(500) | NULL | Human-readable |
| `properties` | json | NULL | Before/after diff — **secrets redacted before write** |
| `ip_hash` | char(64) | NULL | |
| `created_at` | timestamp | | Immutable — there is no `updated_at` |

**Indexes.** `(subject_type, subject_id)` · `causer_id` · `event` · `created_at`.
**Rule.** Append-only, and `properties` is redacted **before** insert, so a secret can never reach the audit trail.

### `content_revisions`

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK |
| `revisionable_type` | varchar(190) | NOT NULL (morph) |
| `revisionable_id` | bigint unsigned | NOT NULL (morph) |
| `user_id` | bigint unsigned | FK → users, NULL, `nullOnDelete` |
| `payload` | longtext | NOT NULL — serialised snapshot |
| `created_at` | timestamp | |

**Index.** `(revisionable_type, revisionable_id, created_at)`.
**Purpose.** Restore a previous version after a bad edit. Retains the newest 20 revisions per subject.

### `settings`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `key` | varchar(120) | NOT NULL, UNIQUE | Dot notation: `site.tagline` |
| `value` | text | NULL | |
| `type` | varchar(16) | default `string` | `string` \| `integer` \| `boolean` \| `json` \| `text` |
| `group` | varchar(64) | default `general` | CMS settings tab |
| `is_public` | boolean | default false | Whether it may be exposed to the front end |
| `created_at` / `updated_at` | timestamp | | |

**Why a `type` column:** a settings table without types becomes a stringly-typed mess where `"false"` is
truthy. The type drives the cast on read and the widget in the CMS.

### `newsletter_subscribers`

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK |
| `email` | varchar(190) | NOT NULL, UNIQUE |
| `token` | char(64) | NOT NULL — double opt-in confirmation |
| `confirmed_at` | timestamp | NULL |
| `unsubscribed_at` | timestamp | NULL |
| `source` | varchar(40) | NULL |
| `ip_hash` | char(64) | NULL |
| `created_at` / `updated_at` | timestamp | |

**Rule.** Double opt-in. An unconfirmed address is never emailed.

---

## 7. Enumerations — single source of truth

| Enum | Cases |
|---|---|
| `UserRole` | `super_admin`, `editor`, `author` |
| `ContentStatus` | `draft`, `in_review`, `scheduled`, `published`, `archived` |
| `ContentFormat` | `html`, `markdown` |
| `ProjectType` | `web_app`, `platform`, `library`, `experiment`, `client_work` |
| `TechnologyCategory` | `language`, `framework`, `database`, `tool`, `cloud`, `design` |
| `TechnologyUsage` | `primary`, `supporting` |
| `SkillLevel` | `beginner`, `intermediate`, `advanced`, `expert` |
| `Availability` | `available`, `limited`, `unavailable` |
| `MediaProcessingStatus` | `pending`, `processing`, `ready`, `failed` |
| `MessageStatus` | `new`, `read`, `replied`, `archived`, `spam` |
| `PageTemplate` | `default`, `about`, `contact`, `legal` |

Each is a PHP backed enum in `app/Enums` and is the **only** place the valid set is written down. A migration
never hardcodes the list — the column is `VARCHAR(32)` (`ARCHITECTURE.md` §4.2).

---

## 8. Data Integrity Rules

Rules the database cannot express, so they live in the domain layer and are enforced by tests:

| # | Rule |
|---|---|
| 1 | `experiences.is_current` must equal `ended_on IS NULL` |
| 2 | `ended_on >= started_on` when both are present |
| 3 | `published_at` must be non-null when `status = published` |
| 4 | A `profiles` row is public only when its user `is_active` **and** verified |
| 5 | `media.alt_text` must be set before a media row may be attached as a project's cover |
| 6 | `content_revisions` keeps the newest 20 per subject; older rows are pruned |
| 7 | `skills.proficiency` and `skills.level` must agree within one bucket (an `expert` cannot be `20`) |
| 8 | A `slug` change on published content writes a redirect record |

Rule 7 is deliberate: two fields describing the same thing **will** drift. Rather than pretend otherwise, the
drift is bounded and tested.

> Note: multiple `primary` technologies per project are **legitimate** when two technologies carry equal
> weight, so no constraint is placed on `usage` cardinality. Documented so it is not "fixed" later.

---

## 9. Migration Order

Dependencies must be satisfied in this order:

```
1. users, password_reset_tokens, sessions, cache, jobs, notifications
2. profiles
3. media, media_conversions
4. technologies, tags
5. projects, project_technology, project_media, project_tag
6. articles, article_tag
7. pages, experiences, skill_groups, skills, services, testimonials
8. contact_messages, newsletter_subscribers, activity_logs, content_revisions, settings
```

---

## 10. Table Inventory

| Group | Tables | Count |
|---|---|---|
| Identity | `users`, `profiles` | 2 |
| Projects | `projects`, `project_technology`, `project_media`, `project_tag` | 4 |
| Articles | `articles`, `article_tag`, `tags` | 3 |
| Pages & narrative | `pages`, `experiences`, `skill_groups`, `skills`, `services`, `testimonials` | 6 |
| Media | `media`, `media_conversions` | 2 |
| CMS support | `contact_messages`, `activity_logs`, `content_revisions`, `settings`, `newsletter_subscribers` | 5 |
| Shared taxonomy | `technologies` | 1 |
| Framework | `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `notifications` | 8 |
| **Total** | | **31** (23 domain + 8 framework) |

---

## 11. Change Log

| Date | Change | Reason |
|---|---|---|
| 2026-08-31 | Initial schema defined — 31 tables (Phase 0.4) | Foundation gate |

---

*Related: `ARCHITECTURE.md` §4 (database architecture), §7 (CMS architecture), §8 (media architecture).*
