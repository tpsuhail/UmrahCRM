<?php

namespace App\Support;

/** Domain vocabulary shared by the services and the client. */
class Crm
{
    public const GROUP_STATUSES = ['New', 'Confirmed', 'InKingdom', 'Departed', 'Closed', 'Cancelled'];

    public const GROUP_STAGES = ['Visa', 'Transport', 'Operation', 'Completed'];

    public const TRIP_STATUSES = ['Pending', 'Done', 'Cancelled'];

    public const REPORT_MODULES = ['groups', 'trips', 'orders', 'hotels', 'brn', 'requests'];

    /** Fields an agent may submit in a booking request / group payload. */
    public const GROUP_FIELDS = [
        'groupCode', 'groupName', 'leaderName', 'leaderMobile',
        'totalPax', 'paxBreakdown', 'adults', 'children', 'infants', 'arrivedPax', 'departedPax',
        'arrivalDate', 'arrivalFlight', 'arrivalPort', 'arrivalMode', 'arrivalTime',
        'departureDate', 'departureFlight', 'departurePort', 'departureMode', 'departureTime',
        'ticketUrl', 'groupPairs', 'notes',
        'bookingType', 'transportBy', 'hostJSON', 'hostIdUrl', 'agentRef',
    ];

    public const ARRIVAL_RE = '/arrival|قدوم|وصول/iu';

    public const DEPARTURE_RE = '/departure|مغادرة/iu';

    public const MAZARAT_RE = '/mazarat|ziyarat|مزارات/iu';

    /** Coerce a payload value to the trimmed string the storage layer expects. */
    public static function s(mixed $v): string
    {
        if ($v === null || $v === false) {
            return '';
        }
        if (is_bool($v)) {
            return 'true';
        }
        if (is_array($v)) {
            return json_encode($v, JSON_UNESCAPED_UNICODE);
        }

        return (string) $v;
    }

    public static function i(mixed $v): int
    {
        return (int) preg_replace('/[^\d-].*$/', '', trim((string) $v));
    }
}
