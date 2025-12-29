<h1 align="center">After Eight Booking Website</h1>
<p align="center"><em>Event & ticket management platform with transparent pricing, QR validation, invitations, and role-based access.</em></p>

---

## ✨ Overview
After Eight Booking Website is a Laravel 12 application for managing events, bookings, ticket types (with configurable fees), QR-code tickets, and event-bound invitations. It emphasizes clear pricing (Base + Fee), dependable scanning, and simple operations for three roles: Admin, Staff/Operator, and User.

## 🚀 Key Features
- Event management (capacity, schedule, multi-artist display)
- Ticket types with optional fees (percentage or fixed)
- Immutable, fee-inclusive ticket pricing for auditability
- Booking flow with full fee breakdown and confirmation email
- QR code generation for tickets and invitations
- Invitations are scoped to specific events (no generic invites)
- Approval/request workflow for event requests
- Role-based access and policies for safe operations
- User profile completion and demographic fields (age, gender, birthday)

## 🔐 Roles — A to Z Journeys
The platform is designed so each role can complete their end-to-end tasks without unnecessary access to others’ data. Below are typical, security-safe user journeys with no secrets or internal endpoints exposed.

### Admin
1. Authenticate and access the admin views.
2. Create or manage events: title, description, date/time, capacity, image, terms.
3. Define ticket types per event: base price, optional fee type (percentage/fixed), and fee amount.
4. Publish or update event status over time (e.g., draft → published → completed/cancelled).
5. Monitor bookings and ticket issuance; export or review operational reports as needed.
6. Send event-specific invitations (each invite has its own QR and is tied to a single event).
7. Oversee approvals for event requests and manage staff/operator access.
8. Perform post-event reviews and archive/retire events safely.

### Staff / Operator
1. Authenticate with operator access.
2. Open the scanning screen on a device with a camera (mobile or desktop-supported hardware).
3. Scan attendee QR codes to validate tickets instantly (valid/used/cancelled/expired states).
4. Prevent re-use automatically (a used ticket will not validate again).
5. Continue scanning throughout entry operations; results are attributed for auditability.

### User (Attendee)
1. Browse upcoming events and open event details.
2. Select ticket type(s) and quantity; see transparent fee breakdown before confirming.
3. Complete booking; receive confirmation and QR-code tickets via email.
4. View/manage bookings and tickets from the account area; download or present QR at entry.
5. If invited, open the event-bound invitation and follow the flow to attend.

> Note: The application enforces access control by role and only exposes the minimum information required for each action.

## 🧮 Pricing & Fee Model
Each `TicketType` can define:
- `fee_type`: `percentage | fixed | null`
- `fee_amount`: numeric (0–100 if percentage; >= 0 if fixed)

When tickets are created during booking:
1) Base price comes from the ticket type.
2) Fee is calculated based on fee type and amount.
3) The ticket’s stored price = Base + Fee and is not recomputed later.

All breakdowns (booking details, tickets, admin summaries) consistently show:
Total = Base + Fee (including when the fee is zero).

### Example
| Base | Fee Type   | Fee Amount | Computed Fee | Stored Ticket Price |
|------|------------|------------|--------------|---------------------|
| 100  | percentage | 5          | 5.00         | 105.00              |
| 250  | fixed      | 20         | 20.00        | 270.00              |
| 80   | (none)     | 0          | 0.00         | 80.00               |

## 🗃 Data Integrity
- A migration introduced fee fields on ticket types.
- A backfill normalized `fee_amount` null → `0` to ensure explicit zero-fee semantics.
- Tickets preserve historical prices even if fee rules change later.

## 🧑‍💻 Tech Stack
- PHP 8.2, Laravel 12
- Blade + Vite + Tailwind CSS
- MySQL (primary) and SQLite for tests
- Email via a provider integration; QR codes via a QR library

## � Security & Privacy
- No secrets are stored in the repository. Environment variables are read from a local `.env` file that is not committed.
- Role-based authorization and policies enforce least-privilege access.
- CSRF protection and Laravel security defaults are enabled.
- Validation prevents capacity over-allocation and enforces fee constraints (e.g., percentage within 0–100).
- Password reset flows are provided via email without exposing any sensitive data.

## 📧 Notifications
- Booking confirmation (showing Base + Fee breakdown)
- Invitation sent (QR attached)
- Password reset
- Approval / payment-related notifications (as applicable)

## 🧱 Project Structure (Highlights)
```
app/
  Models/ (Event, Booking, Ticket, TicketType, Invitation, User)
  Http/Controllers/ (Admin, Booking, Event, Ticket, Invitation, etc.)
  Enums/ (BookingStatus, EventStatus, TicketStatus, Role)
resources/views/ (events, tickets, bookings, invitations, admin panels)
database/migrations/ (schema evolution + fee backfill)
```

## 🛠 Local Development Setup
```bash
git clone <repo-url>
cd after-eight-booking-website
cp .env.example .env    # provide environment values locally (do not commit .env)
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev             # or: npm run build
php artisan serve       # visit the local URL (e.g., http://localhost:8000)
```

### Test Suite
```bash
php artisan test
```
The test configuration uses SQLite (see `phpunit.xml`).

## 🔄 Common Commands
```bash
php artisan migrate          # run migrations
php artisan migrate:rollback # rollback last migration batch
php artisan tinker           # interactively test small snippets
php artisan queue:work       # run queues (if queue driver is not sync)
```

## 🧩 Design Principles
- Immutable ticket pricing for historical accuracy
- Explicit zeros instead of NULL for financial fields
- UI transparency (always present Base + Fee)
- Event-bound invitations for tight access control
- Progressive enhancement and clear separation of concerns

## 🧭 Operations Notes
- Scanning flow is optimized to prevent ticket reuse.
- Listings use pagination and eager-loading where applicable.
- Logging and audits support operational traceability.

## 📚 Further Reading
- System architecture and deeper details: `SYSTEM_DOCUMENTATION.md`
- Invitation flow specifics: `README_INVITATIONS.txt`

## 🤝 Contributing
1. Create a feature branch.
2. Add or adjust tests for any behavior changes.
3. Ensure code style (e.g., Pint) and tests are passing.
4. Open a PR with a summary and screenshots for UI updates.

## � License
This project builds on Laravel (MIT). Custom application code is MIT unless noted otherwise.
