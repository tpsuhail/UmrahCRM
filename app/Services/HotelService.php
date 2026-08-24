<?php

namespace App\Services;

use App\Models\Brn;
use App\Models\HotelBooking;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Ids;
use App\Support\Session;

/** Hotel bookings and BRN (platform reservation) agreements, per group. */
class HotelService
{
    public function __construct(
        private ActivityLogger $logger,
        private GroupService $groups,
    ) {}

    public function list(Session $session, array $p): array
    {
        $guard = $this->groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }
        $groupId = Crm::s($p['groupId']);

        return [
            'ok' => true,
            'bookings' => HotelBooking::query()->where('groupId', $groupId)->get()->map(fn ($r) => $r->toRow())->all(),
            'brn' => Brn::query()->where('groupId', $groupId)->get()->map(fn ($r) => $r->toRow())->all(),
        ];
    }

    public function save(Session $session, array $p): array
    {
        $guard = $this->groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }
        if (Crm::s($p['hotelName'] ?? '') === '' || Crm::s($p['city'] ?? '') === '') {
            return ['ok' => false, 'error' => 'MISSING'];
        }

        $status = Crm::s($p['status'] ?? '');
        $fields = [
            'groupId' => Crm::s($p['groupId']),
            'city' => Crm::s($p['city']),
            'hotelName' => Crm::s($p['hotelName']),
            'rooms' => Crm::s($p['rooms'] ?? ''),
            'checkIn' => Crm::s($p['checkIn'] ?? ''),
            'checkOut' => Crm::s($p['checkOut'] ?? ''),
            'nights' => Dates::calcNights($p['checkIn'] ?? '', $p['checkOut'] ?? ''),
            'status' => in_array($status, ['Tentative', 'Confirmed', 'Cancelled'], true) ? $status : 'Confirmed',
            'notes' => Crm::s($p['notes'] ?? ''),
        ];
        $label = $fields['hotelName'].' ('.$fields['city'].')';

        if (! empty($p['bookingId'])) {
            $booking = HotelBooking::find(Crm::s($p['bookingId']));
            if (! $booking) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            $booking->update($fields);
            $this->logger->log($session, 'HOTEL_UPDATE', 'HotelBookings', $booking->bookingId, $label, $fields['groupId']);

            return ['ok' => true, 'bookingId' => $booking->bookingId];
        }

        $id = Ids::make('H');
        HotelBooking::create($fields + ['bookingId' => $id]);
        $this->logger->log($session, 'HOTEL_CREATE', 'HotelBookings', $id, $label, $fields['groupId']);

        return ['ok' => true, 'bookingId' => $id];
    }

    public function delete(Session $session, array $p): array
    {
        $booking = HotelBooking::find(Crm::s($p['bookingId'] ?? ''));
        if (! $booking) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $booking->delete();
        $this->logger->log($session, 'HOTEL_DELETE', 'HotelBookings', $booking->bookingId,
            (string) $booking->hotelName, (string) $booking->groupId);

        return ['ok' => true];
    }

    public function brnSave(Session $session, array $p): array
    {
        $guard = $this->groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }
        if (Crm::s($p['brnNumber'] ?? '') === '') {
            return ['ok' => false, 'error' => 'MISSING'];
        }

        $fields = [
            'groupId' => Crm::s($p['groupId']),
            'bookingId' => Crm::s($p['bookingId'] ?? ''),
            'brnType' => ($p['brnType'] ?? '') === 'Catering' ? 'Catering' : 'Hotel',
            'hotelName' => Crm::s($p['hotelName'] ?? ''),
            'checkIn' => Crm::s($p['checkIn'] ?? ''),
            'checkOut' => Crm::s($p['checkOut'] ?? ''),
            'brnNumber' => Crm::s($p['brnNumber']),
            'roomsCount' => Crm::s($p['roomsCount'] ?? ''),
        ];

        if (! empty($p['brnId'])) {
            $brn = Brn::find(Crm::s($p['brnId']));
            if (! $brn) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            $brn->update($fields);
            $this->logger->log($session, 'BRN_UPDATE', 'BRN', $brn->brnId, $fields['brnNumber'], $fields['groupId']);

            return ['ok' => true, 'brnId' => $brn->brnId];
        }

        $id = Ids::make('B');
        Brn::create($fields + ['brnId' => $id]);
        $this->logger->log($session, 'BRN_CREATE', 'BRN', $id, $fields['brnNumber'], $fields['groupId']);

        return ['ok' => true, 'brnId' => $id];
    }

    public function brnDelete(Session $session, array $p): array
    {
        $brn = Brn::find(Crm::s($p['brnId'] ?? ''));
        if (! $brn) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $brn->delete();
        $this->logger->log($session, 'BRN_DELETE', 'BRN', $brn->brnId, (string) $brn->brnNumber, (string) $brn->groupId);

        return ['ok' => true];
    }
}
