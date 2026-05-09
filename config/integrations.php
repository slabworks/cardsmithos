<?php

return [
    'gmail' => [
        'client_id' => env('GMAIL_CLIENT_ID', env('GOOGLE_CLIENT_ID')),
        'client_secret' => env('GMAIL_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET')),
        'redirect_uri' => env('GMAIL_REDIRECT_URI', rtrim((string) env('APP_URL'), '/').'/settings/integrations/gmail/callback'),
        'scopes' => array_values(array_filter(explode(' ', (string) env('GMAIL_SCOPES', 'https://www.googleapis.com/auth/gmail.readonly')))),
        'contact_sync_query' => env('GMAIL_CONTACT_SYNC_QUERY', 'in:inbox -in:chats'),
        'contact_sync_messages' => (int) env('GMAIL_CONTACT_SYNC_MESSAGES', 1000),
        'sync_page_size' => (int) env('GMAIL_SYNC_PAGE_SIZE', 50),
    ],
];
