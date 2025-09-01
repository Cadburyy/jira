@component('mail::message')
# New Ticket Assigned

Hello **{{ $user->name }}**,

A new Dandory ticket has been assigned to you. Here are the details:

- **Key**: {{ $dandory->ddcnk_id }}
- **Part Name**: {{ $dandory->nama_part }}
- **Line Produksi**: {{ $dandory->line_production }}
- **Status**: {{ $dandory->status }}

You can view the full ticket details by clicking the button below:

@component('mail::button', ['url' => route('dandories.show', $dandory->id)])
View Ticket
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent