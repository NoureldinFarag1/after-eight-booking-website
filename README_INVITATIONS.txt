Invitations feature added:

- New model: app/Models/Invitation.php
- New controller: app/Http/Controllers/InvitationController.php
- New migration: database/migrations/2025_09_24_004845_create_invitations_table.php
- New route: POST /invitations named invitations.store

Notes:
- The blade changes were applied to files detected containing 'Quick Actions' or similar.
- The invite button is visible only when Auth::user()->is_admin is truthy OR user.role === 'admin'.
- Run `php artisan migrate` to create the invitations table.
- Adjust the admin condition in Blade if your project uses a different role implementation.
