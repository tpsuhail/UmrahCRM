<?php

namespace App\Services;

use App\Models\Group;
use App\Models\HotelBooking;
use App\Models\Trip;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Session;

/**
 * Unified edit engine.
 *
 * Changing a group's travel details has to keep its trips and hotels honest:
 * the arrival trip *is* the arrival flight, mazarat trips are anchored to the
 * arrival date, and a pax change ripples into every trip's vehicle count.
 * `compute()` previews those knock-on edits; `apply()` writes them.
 */
class CascadeService
{
    public function __construct(
        private ActivityLogger $logger,
        private AdminService $admin,
    ) {}

    /**
     * Hours the departure bus must leave before the flight (Air only).
     * Null means "no rule" — the trip time is then left alone.
     */
    public function departureLeadHours(string $fromCity, string $port): ?int
    {
        $city = mb_strtoupper($fromCity);
        $p = mb_strtoupper($port);
        $isJed = str_contains($p, 'JED');
        $isMed = str_contains($p, 'MED');

        if (str_contains($city, 'MAK') && $isJed) {
            return 5;
        }
        if (str_contains($city, 'MADI') && $isJed) {
            return 12;
        }
        if (str_contains($city, 'MADI') && $isMed) {
            return 4;
        }

        return null;
    }

    /**
     * Every downstream change a group edit implies.
     *
     * @return array<int, array{kind: string, id: string, label: string, field: string, fieldLabel: string, before: string, after: string}>
     */
    public function compute(string $groupId, array $next): array
    {
        $cur = Group::find($groupId);
        if (! $cur) {
            return [];
        }

        $trips = Trip::query()->where('groupId', $groupId)->orderBy('date')->get();
        $hotels = HotelBooking::query()->where('groupId', $groupId)->orderBy('checkIn')->get();

        $val = fn (string $k) => array_key_exists($k, $next) ? Crm::s($next[$k]) : (string) $cur->{$k};

        $arrChanged = $val('arrivalDate') !== (string) $cur->arrivalDate;
        $depChanged = $val('departureDate') !== (string) $cur->departureDate;
        $arrDelta = $arrChanged ? Dates::dayDelta($cur->arrivalDate, $val('arrivalDate')) : 0;

        $newPax = array_key_exists('totalPax', $next) ? Crm::i($next['totalPax']) : Crm::i($cur->totalPax);
        $paxChanged = $newPax !== Crm::i($cur->totalPax);

        $changes = [];
        $push = function (string $kind, string $id, string $label, string $field, string $fieldLabel, $before, $after) use (&$changes) {
            $before = Crm::s($before);
            $after = Crm::s($after);
            if ($before === $after) {
                return;
            }
            $changes[] = compact('kind', 'id', 'label', 'field', 'fieldLabel', 'before', 'after');
        };

        foreach ($trips as $tr) {
            $type = (string) $tr->tripType;
            $label = ($type ?: '–').' ('.($tr->date ?: '–').')';
            $pushTrip = fn (string $field, string $fieldLabel, $before, $after) => $push('trip', $tr->tripId, $label, $field, $fieldLabel, $before, $after);

            // ── the arrival trip follows the group's arrival details ──
            if (preg_match(Crm::ARRIVAL_RE, $type)) {
                if ($arrChanged) {
                    $pushTrip('date', 'Date', $tr->date, $val('arrivalDate'));
                }
                if ($val('arrivalTime') !== (string) $cur->arrivalTime) {
                    $pushTrip('time', 'Time', $tr->time, $val('arrivalTime'));
                }
                if ($val('arrivalFlight') !== (string) $cur->arrivalFlight) {
                    $pushTrip('flightNo', 'Flight No', $tr->flightNo, $val('arrivalFlight'));
                }
                if ($val('arrivalPort') !== (string) $cur->arrivalPort) {
                    $pushTrip('from', 'From', $tr->from, $val('arrivalPort'));
                }
            }

            // ── the departure bus leaves hours before the flight ──
            if (preg_match(Crm::DEPARTURE_RE, $type)) {
                $busDate = $val('departureDate');
                $busTime = '';
                if ($val('departureMode') === 'Air') {
                    $lead = $this->departureLeadHours((string) $tr->from, $val('departurePort'));
                    $base = Dates::parseYmd($val('departureDate'));
                    if ($lead !== null && $base !== null && preg_match('/^(\d{1,2}):(\d{2})/', $val('departureTime'), $tm)) {
                        $at = $base + ((int) $tm[1] - $lead) * 3600 + (int) $tm[2] * 60;
                        $busDate = gmdate('Y-m-d', $at);
                        $busTime = gmdate('H:i', $at);
                    }
                }
                if ($depChanged || $busDate !== (string) $tr->date) {
                    $pushTrip('date', 'Date', $tr->date, $busDate);
                }
                if ($busTime !== '' && $busTime !== (string) $tr->time) {
                    $pushTrip('time', 'Time', $tr->time, $busTime);
                }
                if ($val('departureFlight') !== (string) $cur->departureFlight) {
                    $pushTrip('flightNo', 'Flight No', $tr->flightNo, $val('departureFlight'));
                }
                if ($val('departurePort') !== (string) $cur->departurePort) {
                    $pushTrip('to', 'To', $tr->to, $val('departurePort'));
                }
            }

            // ── mazarat trips are anchored to arrival, so they shift with it ──
            if (preg_match(Crm::MAZARAT_RE, $type) && $arrChanged && $arrDelta !== 0) {
                $pushTrip('date', 'Date', $tr->date, Dates::shiftDate($tr->date, $arrDelta));
            }

            // ── pax changes ripple into every trip, and into the vehicle count ──
            if ($paxChanged) {
                $pushTrip('pax', 'Pax', $tr->pax, (string) $newPax);
                $capacity = $this->admin->vehicleCapacity((string) $tr->vehicleType);
                if ($capacity > 0) {
                    $pushTrip('buses', 'Vehicles', $tr->buses, (string) (int) ceil($newPax / $capacity));
                }
            }
        }

        // ── hotels: first check-in tracks arrival, last check-out tracks departure ──
        if ($hotels->isNotEmpty()) {
            if ($arrChanged && $arrDelta !== 0) {
                $h = $hotels->first();
                $push('hotel', $h->bookingId, (string) ($h->hotelName ?: $h->city ?: '–'),
                    'checkIn', 'Check-in', $h->checkIn, Dates::shiftDate($h->checkIn, $arrDelta));
            }
            if ($depChanged) {
                $h = $hotels->last();
                $delta = Dates::dayDelta($cur->departureDate, $val('departureDate'));
                if ($delta !== 0) {
                    $push('hotel', $h->bookingId, (string) ($h->hotelName ?: $h->city ?: '–'),
                        'checkOut', 'Check-out', $h->checkOut, Dates::shiftDate($h->checkOut, $delta));
                }
            }
        }

        return $changes;
    }

    /** Preview endpoint — what a group edit would cascade into. */
    public function preview(Session $session, GroupService $groups, array $p): array
    {
        $guard = $groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }

        $next = $groups->pickGroupFields($p);
        $next['totalPax'] = (string) $groups->paxTotal($p);

        return ['ok' => true, 'changes' => $this->compute(Crm::s($p['groupId']), $next)];
    }

    /** Write the changes `compute()` returned. Returns how many rows moved. */
    public function apply(Session $session, string $groupId, array $changes): int
    {
        $byTrip = [];
        $byHotel = [];
        foreach ($changes as $c) {
            if ($c['kind'] === 'trip') {
                $byTrip[$c['id']][$c['field']] = $c['after'];
            }
            if ($c['kind'] === 'hotel') {
                $byHotel[$c['id']][$c['field']] = $c['after'];
            }
        }

        $n = 0;
        foreach ($byTrip as $id => $patch) {
            if (! empty($patch['date'])) {
                $patch['day'] = Dates::dayNameOf($patch['date']);
            }
            if ($trip = Trip::find($id)) {
                $trip->update($patch);
                $n++;
            }
        }
        foreach ($byHotel as $id => $patch) {
            $hotel = HotelBooking::find($id);
            if (! $hotel) {
                continue;
            }
            $patch['nights'] = Dates::calcNights(
                $patch['checkIn'] ?? $hotel->checkIn,
                $patch['checkOut'] ?? $hotel->checkOut,
            );
            $hotel->update($patch);
            $n++;
        }

        if ($n > 0) {
            $this->logger->log($session, 'CASCADE_APPLY', 'Groups', $groupId,
                $n.' dependent record(s) updated automatically', $groupId);
        }

        return $n;
    }
}
