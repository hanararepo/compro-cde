New message for {{ $companyName }}

Subject: {{ $contactMessage->subject }}
From: {{ $contactMessage->name }}
Email: {{ $contactMessage->email }}
Received: {{ \App\Support\LocalTime::format($contactMessage->created_at) }}

{{ $contactMessage->message }}

Reply directly to this email to contact the sender.
This message is also saved in Contact Messages. Reference #{{ $contactMessage->id }}.
