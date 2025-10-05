@component('mail::message')
# Hello {{ $invitation->name }}!

{{ $invitation->sender->name }} has sent you an invitation.

@if($invitation->event)
**Event:** {{ $invitation->event->title }}
**Date:** {{ $invitation->event->event_date->format('F j, Y') }}
**Time:** {{ $invitation->event->event_time->format('g:i A') }}
**Location:** {{ $invitation->event->location }}
@endif

@if($invitation->message)
**Message:**
{{ $invitation->message }}
@endif

@component('mail::button', ['url' => $invitation->getAcceptUrl()])
Accept Invitation
@endcomponent

You can also decline this invitation if you cannot attend.

@component('mail::button', ['url' => $invitation->getDeclineUrl(), 'color' => 'red'])
Decline Invitation
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
