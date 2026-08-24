<?php

return [
    /*
     * How long a bearer token stays valid without activity. Every authenticated
     * call renews it, so this is an idle timeout, not a hard cap.
     */
    'session_ttl' => (int) env('CRM_SESSION_TTL', 21600),   // 6 hours

    /* Operations run on Saudi time, whatever the server's clock is set to. */
    'timezone' => env('CRM_TIMEZONE', 'Asia/Riyadh'),

    /* Ceiling for a single base64 upload (tickets, host IDs, logos). */
    'max_upload_bytes' => (int) env('CRM_MAX_UPLOAD_BYTES', 8 * 1024 * 1024),
];
