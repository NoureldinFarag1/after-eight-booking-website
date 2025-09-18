# After Eight Booking Website - Laravel Event Ticketing System

## Overview
A comprehensive event booking and ticket management system built with Laravel 12, featuring three distinct user roles and QR code-based ticket validation.

## Features

### User Roles & Permissions
- **Admin**: Full CRUD operations on events, user management, booking oversight
- **Operator**: QR code scanning and ticket validation at events
- **User**: Event browsing and ticket booking

### Core Functionality
- **Event Management**: Create, edit, publish events with capacity and pricing
- **Booking System**: User-friendly booking with automatic ticket generation
- **QR Code Generation**: Unique QR codes for each ticket with validation
- **Real-time Validation**: Instant ticket status checking (valid, used, expired, invalid)
- **Role-based Access Control**: Secure middleware and authorization policies

## Database Schema

### Users Table
- Basic user information (name, email, password)
- Role field (admin, operator, user)
- Phone number for contact
- Email verification support

### Events Table
- Event details (title, description, location, date, time)
- Capacity management and pricing
- Status tracking (draft, published, cancelled, completed)
- Soft deletes for data integrity

### Bookings Table
- User-event relationships
- Unique booking references
- Quantity and total amount tracking
- Status management (pending, confirmed, cancelled, refunded)

### Tickets Table
- Individual ticket records
- Unique ticket numbers and QR codes
- Validation tracking (scanned date/time, operator)
- Status management (valid, used, cancelled, expired)

## Security Features

### Database Security
- Foreign key constraints with cascade deletes
- Indexed columns for performance
- Soft deletes to maintain data integrity
- Unique constraints on critical fields

### Application Security
- Role-based middleware protection
- Authorization policies for resource access
- Input validation through Form Requests
- CSRF protection on all forms
- Secure password hashing

### QR Code Security
- UUID-based QR codes (impossible to guess)
- One-time use validation
- Operator tracking for accountability
- Timestamp logging for audit trails

## Code Architecture

### Models & Relationships
```php
User hasMany Bookings, Tickets
Event hasMany Bookings, Tickets
Booking belongsTo User, Event; hasMany Tickets
Ticket belongsTo User, Event, Booking
```

### Enums for Type Safety
- `Role`: ADMIN, OPERATOR, USER
- `EventStatus`: DRAFT, PUBLISHED, CANCELLED, COMPLETED
- `BookingStatus`: PENDING, CONFIRMED, CANCELLED, REFUNDED
- `TicketStatus`: VALID, USED, CANCELLED, EXPIRED

### Controllers & Middleware
- Resource controllers with role-based restrictions
- Custom middleware for role validation
- API endpoints for AJAX QR scanning
- Proper authorization using policies

## Key Routes

### Public Routes
- `GET /events` - Browse published events
- `GET /events/{event}` - View event details

### User Routes (Auth Required)
- `GET /events/{event}/book` - Booking form
- `POST /bookings` - Create booking
- `GET /bookings` - User's bookings
- `GET /tickets` - User's tickets

### Admin Routes
- `GET /admin/dashboard` - Admin overview
- Full CRUD on `/admin/events`
- Booking and ticket management

### Operator Routes
- `GET /scan` - QR scanning interface
- `POST /tickets/validate/{qr_code}` - Ticket validation
- `GET /operator/dashboard` - Scan history

## Development Setup

### Default Users (password: 'password')
- `admin@aftereight.com` - Administrator
- `operator@aftereight.com` - Operator
- `john@example.com` - Regular user
- `jane@example.com` - Regular user

### Sample Events
6 pre-configured events ranging from jazz nights to comedy shows

### Database Commands
```bash
php artisan migrate
php artisan db:seed
```

## Scalability & Maintenance

### Code Reusability
- Service classes for business logic
- Traits for common functionality
- Repository pattern for data access
- Event-driven architecture support

### Performance Optimizations
- Database indexing on frequently queried columns
- Eager loading to prevent N+1 queries
- Caching strategies for event listings
- Pagination for large datasets

### Maintenance Features
- Soft deletes for data recovery
- Comprehensive logging and audit trails
- Modular architecture for easy updates
- Standardized validation and error handling

## API Integration Ready
- RESTful API endpoints
- JSON responses for mobile apps
- Rate limiting capability
- API authentication support

## Future Enhancements
- Payment gateway integration
- Email notifications
- SMS reminders
- Mobile app API
- Analytics dashboard
- Multi-language support
- Social media integration

## File Structure
```
app/
├── Enums/              # Type-safe enumerations
├── Http/
│   ├── Controllers/    # Business logic controllers
│   ├── Middleware/     # Custom middleware
│   └── Requests/       # Validation classes
├── Models/             # Eloquent models
└── Policies/           # Authorization policies

database/
├── migrations/         # Database schema
├── seeders/           # Sample data
└── factories/         # Test data generation

routes/
└── web.php            # Application routes
```

This system provides a robust foundation for event ticketing with proper security, scalability, and maintainability built-in from the ground up.
