<h1 align="center">After Eight Booking Website</h1>
<p align="center"><em>Event & ticket management platform with per-ticket fee logic, QR validation, invitations, and multi-role access.</em></p>

---

## ✨ Overview
After Eight Booking Website is a Laravel 12 application for managing events, bookings, ticket types (with configurable fees), invitations (QR based), and user access. It supports transparent pricing (base + fee), role-based dashboards, and operational tooling for approvals and scanning.

## 🚀 Key Features
- Event management (capacity, date/time, multi-artist listing)
- Per-ticket-type fees (percentage or fixed) with automatic total calculation
- Fee-inclusive pricing stored immutably on tickets for audit consistency
- Booking flow with fee breakdown and confirmation email
- QR code generation for tickets & invitations
- Invitation system tied strictly to events (no generic invites)
- Approval/request workflow for event requests
- Roles: Admin, Staff/Operators, Users (with access guards & policies)
- User profile completion & demographic fields (age, gender, birthday)
- Secure password reset & email notifications (Resend integration)

## 🧮 Fee Model
Each `TicketType` optionally defines:
- `fee_type`: `percentage | fixed | null`
- `fee_amount`: numeric (0–100 if percentage; >=0 if fixed)

When tickets are created during booking:
1. Base price = `ticket_types.price`
2. Fee = `calculateFee()` (percentage * base / 100 or fixed value)
3. Stored ticket price = base + fee (NOT recomputed later)

All breakdowns (booking details, ticket views, admin lists) display:
`Total = Base + Fee` even when fee = 0 (for consistency & reporting).

## 🗃 Data Integrity & Backfill
- Migration `add_fees_to_ticket_types_table` introduced fee fields.
- Backfill migration normalizes `fee_amount` null → `0` for explicit zero-fee semantics.
- Tickets retain historical price even if future fee rules change.

## 🧑‍💻 Tech Stack
- PHP 8.2 + Laravel 12
- Blade templates + Vite + Tailwind CSS (4.x)
- MySQL (primary) / SQLite (tests)
- Resend (mail), Simple QrCode package

## 🔐 Roles & Access
| Role | Capabilities |
|------|--------------|
| Admin | Manage events, users, ticket types, approvals, bookings |
| Staff / Operator | QR scanning & operational tasks |
| User | Browse events, book tickets, manage their profile |

Policies & middleware enforce isolation (e.g., `RoleMiddleware`, profile completion checks, staff restrictions).

## 📧 Notifications
- Booking confirmation (fee-inclusive total)
- Invitation sent (with QR code)
- Password reset
- Payment / approval notifications

## 🧱 Project Structure (Highlights)
```
app/
	Models/ (Event, Booking, Ticket, TicketType, Invitation, User)
	Http/Controllers/ (Admin controllers, Booking, Event, Ticket, Invitation)
	Enums/ (BookingStatus, EventStatus, TicketStatus, Role)
resources/views/ (events, tickets, bookings, invitations, admin panels)
database/migrations/ (... incremental schema evolution + fee backfill)
```

## 🛠 Local Development Setup
```bash
git clone <repo-url>
cd after-eight-booking-website
cp .env.example .env   # or provide environment values
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev             # or: npm run build
php artisan serve       # visit http://localhost:8000 or configured APP_URL
```

### Test Suite
```bash
php artisan test
```
Uses in-memory SQLite (configured in `phpunit.xml`).

## 🧪 Fee Calculation Example
| Base | Fee Type | Fee Amount | Computed Fee | Stored Ticket Price |
|------|----------|------------|--------------|---------------------|
| 100  | percentage | 5        | 5.00         | 105.00              |
| 250  | fixed       | 20       | 20.00        | 270.00              |
| 80   | (none)      | 0        | 0.00         | 80.00               |

## 🔄 Common Commands
```bash
php artisan migrate         # Run migrations
php artisan migrate:rollback
php artisan tinker          # Experiment
php artisan queue:work      # (if queue driver switched from sync)
```

## 🧩 Design Principles
- Immutable ticket pricing for historical accuracy
- Explicit zero values instead of NULL for financial fields
- UI transparency (always show Base + Fee even when zero)
- Progressive enhancement: per-type fees replacing deprecated event-level fees

## 🛡 Security / Validation Notes
- Percentage fee constrained 0–100
- Capacity validation prevents over-allocation & lowering below sold count
- Profile completion middleware gates certain flows
- CSRF + Laravel defaults + CSP headers middleware

## 📄 License
This project is derived from Laravel (MIT). Custom application code is MIT unless specified otherwise.

## 🤝 Contributing
1. Create a feature branch
2. Add or adjust tests for changed behavior
3. Ensure: lint (pint), tests pass, no debug code
4. Open PR with clear summary & screenshots for UI changes

## 🗺 Future Ideas
- Reporting dashboard (revenue = base vs fee split)
- Refund / cancellation financial adjustments
- Dynamic per-event fee policies
- API endpoints for mobile scanning clients

---
For deeper architectural details see `SYSTEM_DOCUMENTATION.md` and invitation specifics in `README_INVITATIONS.txt`.
