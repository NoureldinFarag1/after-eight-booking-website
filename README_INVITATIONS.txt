Invitation System with QR Code Status Tracking (Final Version):

## Overview
A streamlined invitation system where admins send event-specific invitations with QR codes. Events are REQUIRED for all invitations - no generic invitations allowed.

## Key Features:
- **Event Required**: All invitations must be tied to a specific event
- **QR Code Generation**: Automatic QR code creation for event entry
- **Status Tracking**: Both invitation status and QR code status
- **Email Notifications**: Immediate email sending with QR code attachment
- **Entry Verification**: QR codes can be scanned for event entry

## How it works:
1. Admin selects an existing event (required)
2. Enters recipient details and optional personal message
3. System generates unique QR code and sends email immediately
4. Recipient receives email with event details and QR code
5. QR code used for event entry verification

## Updated Validation:
- `event_id`: **REQUIRED** - must select an existing event
- `name`: required recipient name
- `email`: required recipient email
- `message`: optional personal message

## QR Code Status Flow:
- **valid**: QR code ready for event entry
- **used**: QR code scanned and used for entry
- **cancelled**: QR code cancelled by admin

## Email Content:
- Event title, date, time, location
- Personal message (if provided)
- QR code attachment for entry
- Clear instructions for use

## UI Improvements:
- Better create form with event requirement
- Warning if no events exist with link to create event
- Enhanced event display in invitation lists
- Clear QR status indicators
- Helpful instructions and guidance

## Technical Details:
- Events are loaded and required in create form
- QR codes point to verification endpoints
- Immediate email sending (sync queue driver)
- Comprehensive error handling and validation
- Mobile-friendly responsive design

## Admin Workflow:
1. Ensure events exist (create if needed)
2. Navigate to invitation creation
3. Select event from dropdown
4. Enter recipient details
5. Add personal message (optional)
6. Send invitation
7. Monitor QR status for entry tracking

This creates a professional, event-focused invitation system perfect for managing event attendance with QR code verification.
