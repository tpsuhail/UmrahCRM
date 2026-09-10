# Umrah CRM

Operations CRM for an Umrah operator: agents submit bookings, operators approve
them, and the resulting group is tracked through visas, hotels, transport orders,
and day-by-day movements — with vouchers, run sheets, and reports at the end.

The UI is bilingual (Arabic / English, RTL-aware) and ships as a single
self-contained page; the whole back end is Laravel.

## Requirements

- PHP 8.3+ with `mbstring`, `gd` (PDF export), and `zip` (Excel export)
- Composer
- A database — SQLite works out of the box; MySQL and PostgreSQL are supported

## Getting started

```bash
composer setup       # install, key, migrate, seed, link storage
composer dev         # http://127.0.0.1:8000
```

`composer setup` seeds the master lists (cities, ports, movement types, vehicle
types with their seat counts) and one operator account:

| Username | Password     |
| -------- | ------------ |
| `admin`  | `Admin@1447` |

**Change that password on first sign-in**, or set `CRM_ADMIN_PASSWORD` before
seeding.

Run the test suite with `composer test`, and format with `vendor/bin/pint`.

## Configuration

Beyond the standard Laravel keys, `config/crm.php` reads:

| Variable                | Default       | Meaning                                                     |
| ----------------------- | ------------- | ----------------------------------------------------------- |
| `CRM_SESSION_TTL`       | `21600`       | Idle timeout for an API token, in seconds. Renewed on use.  |
| `CRM_TIMEZONE`          | `Asia/Riyadh` | Operational clock, whatever the server is set to.           |
| `CRM_MAX_UPLOAD_BYTES`  | `8388608`     | Ceiling for one base64 upload (ticket, host ID, logo).      |

Uploads are written to the `public` disk, so keep `FILESYSTEM_DISK=public` and
run `php artisan storage:link`.

Tokens live in the cache, so in production point `CACHE_STORE` at a shared store
(`database` or `redis`) — a per-process array cache would sign users out on
every request.

## How it fits together

Two endpoints serve the entire application:

- `POST /api/login` → `{ ok, token, user }`
- `POST /api` → `{ token, action, payload }`

`ApiController::registry()` maps each action to the roles allowed to run it and
the service that handles it. **Every role and agent-code filter is applied on the
server**; the browser never decides what a user may see. An agent's requests are
scoped to their own `agentCode` in the service layer as well, so a forged group
id returns `FORBIDDEN` rather than another agent's data. Actions that write run
inside a database transaction.

### The domain, in the order work actually happens

1. **Request** — an agent submits a booking (group details, hotels, BRN
   agreements, movements). Drafts are saved unvalidated so a half-finished
   wizard survives a refresh; only a submitted request is reviewable.
2. **Review** — an operator approves, returns (agent fixes and resubmits), or
   rejects. Approval is what creates the live group and its child records.
3. **Stages** — `Visa → Transport → Operation → Completed`. Forward moves are
   gated on real progress: no Transport without a visa, no Operation without a
   confirmed order, no Completed until every live trip is done. Backward moves
   are always allowed, and always logged.
4. **Transport** — assigning an order confirms the group's trips. Only groups
   with a *confirmed* (`Booked`) order appear on the operations board; an order
   still `Requested` is just waiting on the company.
5. **Movements** — completing the arrival trip moves the group to `InKingdom`,
   the departure trip to `Departed`. A trip cannot be marked done before its
   date arrives.

### The cascade engine

Group travel details and their trips describe the same reality, so
`CascadeService` keeps them honest. Changing an arrival date moves the arrival
trip, shifts mazarat trips by the same delta, and slides the first hotel's
check-in; changing pax rewrites every trip's pax and recomputes the vehicle
count from the seat capacity on the vehicle master. Departure is asymmetric on
purpose: the bus leaves hours before the flight (5h Makkah→JED, 12h
Madinah→JED, 4h Madinah→MED), so the trip time is *derived* from the flight,
never pushed back onto it.

`groups.editImpact` previews all of it before anything is written.

### Layout

```
app/Http/Controllers/ApiController.php   action registry, auth, dispatch
app/Services/                            one service per domain area
app/Support/                             dates, ids, session, shared vocabulary
app/Models/                              flat, string-keyed Eloquent models
resources/views/app.blade.php            the entire client
tests/Feature/CrmWorkflowTest.php        the flows above, end to end
```

## Notes on the port

This system began life as a Google Apps Script bound to a spreadsheet. A few
choices preserve continuity with that origin:

- **Values are stored as strings.** Dates stay `YYYY-MM-DD` and timestamps stay
  `YYYY/MM/DD HH:mm:ss`, and a blank reads as `""` rather than `null`. The
  client was written against that shape and still receives it byte for byte.
- **Application-generated primary keys.** Ids keep their sortable
  `G-260824110944-x7f2qa` form instead of moving to auto-increment integers, so
  file references stay stable and meaningful.
- **Passwords are bcrypt now**, not the old SHA-256-plus-salt. Existing sheet
  users cannot be carried over; recreate them, or have an operator reset each
  password from the Users screen.
- **Files live on the `public` disk**, not Drive. Logos therefore render inside
  generated PDFs without a round trip.
- **PDF and Excel export** are handled by Dompdf and PhpSpreadsheet, replacing
  the Drive conversion tricks. Both still return base64 on the same actions
  (`pdf.fromHtml`, `reports.excel`), so the client was unchanged.
