<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The CRM used to live in a Google Sheet where every cell was a string.
 * The port keeps that convention: dates stay "YYYY-MM-DD", timestamps stay
 * "YYYY/MM/DD HH:mm:ss", so the client contract is byte-for-byte the same.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->string('userId')->primary();
            $t->string('username')->unique();
            $t->string('passwordHash');
            $t->string('role')->default('operator');
            $t->string('agentCode')->default('');
            $t->string('displayName')->default('');
            $t->string('status')->default('active');
            $t->string('createdAt')->default('');
            $t->string('lastLogin')->default('');
            $t->string('department')->default('');
        });

        Schema::create('agents', function (Blueprint $t) {
            $t->string('agentCode')->primary();
            $t->string('agentName');
            $t->string('country')->default('');
            $t->string('contactName')->default('');
            $t->string('mobile')->default('');
            $t->string('email')->default('');
            $t->string('status')->default('active');
            $t->text('notes')->nullable();
            $t->string('createdAt')->default('');
        });

        Schema::create('groups', function (Blueprint $t) {
            $t->string('groupId')->primary();
            $t->string('fileNo')->default('');
            $t->string('groupCode')->default('');
            $t->string('agentCode')->default('')->index();
            $t->string('groupName')->default('');
            $t->string('leaderName')->default('');
            $t->string('leaderMobile')->default('');
            $t->string('totalPax')->default('');
            $t->string('paxBreakdown')->default('');
            $t->string('arrivedPax')->default('');
            $t->string('departedPax')->default('');
            $t->string('arrivalDate')->default('')->index();
            $t->string('arrivalFlight')->default('');
            $t->string('arrivalPort')->default('');
            $t->string('departureDate')->default('')->index();
            $t->string('departureFlight')->default('');
            $t->string('departurePort')->default('');
            $t->string('status')->default('New')->index();
            $t->string('createdBy')->default('');
            $t->string('approvedBy')->default('');
            $t->string('createdAt')->default('');
            $t->text('notes')->nullable();
            $t->string('adults')->default('');
            $t->string('children')->default('');
            $t->string('infants')->default('');
            $t->string('arrivalMode')->default('');
            $t->string('departureMode')->default('');
            $t->string('arrivalTime')->default('');
            $t->string('departureTime')->default('');
            $t->text('ticketUrl')->nullable();
            $t->text('groupPairs')->nullable();
            $t->string('bookingType')->default('');
            $t->string('transportBy')->default('');
            $t->text('hostJSON')->nullable();
            $t->text('hostIdUrl')->nullable();
            $t->string('stage')->default('Visa')->index();
            $t->string('stageSince')->default('');
            $t->string('agentRef')->default('');
            $t->string('visaStatus')->default('');
            $t->string('archived')->default('');
        });

        Schema::create('hotel_bookings', function (Blueprint $t) {
            $t->string('bookingId')->primary();
            $t->string('groupId')->default('')->index();
            $t->string('city')->default('');
            $t->string('hotelName')->default('');
            $t->string('rooms')->default('');
            $t->string('checkIn')->default('');
            $t->string('checkOut')->default('');
            $t->string('nights')->default('');
            $t->string('status')->default('Confirmed');
            $t->text('notes')->nullable();
            $t->string('rsvNo')->default('');
        });

        Schema::create('brn', function (Blueprint $t) {
            $t->string('brnId')->primary();
            $t->string('bookingId')->default('');
            $t->string('groupId')->default('')->index();
            $t->string('brnType')->default('Hotel');
            $t->string('hotelName')->default('');
            $t->string('checkIn')->default('');
            $t->string('checkOut')->default('');
            $t->string('brnNumber')->default('');
            $t->string('roomsCount')->default('');
        });

        Schema::create('transport_orders', function (Blueprint $t) {
            $t->string('orderId')->primary();
            $t->string('groupId')->default('')->index();
            $t->string('fileNo')->default('');
            $t->string('transportCompany')->default('');
            $t->string('orderNumber')->default('');
            $t->string('brn')->default('');
            $t->string('status')->default('Requested');
            $t->string('createdBy')->default('');
            $t->string('approvedBy')->default('');
            $t->string('createdAt')->default('');
            $t->string('confirmationNo')->default('');
        });

        Schema::create('trips', function (Blueprint $t) {
            $t->string('tripId')->primary();
            $t->string('orderId')->default('');
            $t->string('groupId')->default('')->index();
            $t->string('date')->default('')->index();
            $t->string('day')->default('');
            $t->string('tripType')->default('');
            $t->string('from')->default('');
            $t->string('fromDetail')->default('');
            $t->string('to')->default('');
            $t->string('toDetail')->default('');
            $t->string('time')->default('');
            $t->string('flightNo')->default('');
            $t->string('buses')->default('');
            $t->string('pax')->default('');
            $t->string('driverName')->default('');
            $t->string('driverMobile')->default('');
            $t->string('plateNo')->default('');
            $t->string('bookingStatus')->default('Pending');
            $t->string('tripStatus')->default('Pending')->index();
            $t->text('notes')->nullable();
            $t->string('transportMode')->default('');
            $t->string('vehicleType')->default('');
        });

        Schema::create('requests', function (Blueprint $t) {
            // `seq` is the table's own key so the database can assign it; the
            // application still addresses a request by its human-facing id.
            $t->bigIncrements('seq');
            $t->string('requestId')->unique();
            $t->string('type')->default('');
            $t->string('groupId')->default('')->index();
            $t->string('agentCode')->default('')->index();
            $t->longText('payloadJSON')->nullable();
            $t->string('status')->default('Pending')->index();
            $t->string('submittedAt')->default('');
            $t->string('reviewedBy')->default('');
            $t->string('reviewedAt')->default('');
            $t->text('reviewNote')->nullable();
        });

        Schema::create('masters', function (Blueprint $t) {
            $t->string('masterId')->primary();
            $t->string('type')->default('')->index();
            $t->string('value_ar')->default('');
            $t->string('value_en')->default('');
            $t->string('active')->default('yes');
            $t->text('meta')->nullable();
        });

        Schema::create('logs', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('timestamp')->default('');
            $t->string('userId')->default('');
            $t->string('username')->default('');
            $t->string('action')->default('');
            $t->string('entity')->default('');
            $t->string('entityId')->default('');
            $t->text('details')->nullable();
            $t->string('groupId')->default('')->index();
        });
    }

    public function down(): void
    {
        foreach (['logs', 'masters', 'requests', 'trips', 'transport_orders', 'brn',
            'hotel_bookings', 'groups', 'agents', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
