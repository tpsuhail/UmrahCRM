<?php

namespace Database\Seeders;

use App\Models\Master;
use App\Models\User;
use App\Services\AuthService;
use App\Support\Ids;
use Illuminate\Database\Seeder;

/**
 * Bootstraps a usable install: the master lists the booking wizard depends on,
 * plus the first operator account.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedMasters();
        $this->seedAdmin();
    }

    private function seedMasters(): void
    {
        if (Master::query()->exists()) {
            return;   // already seeded
        }

        $rows = [
            ['city', 'مكة المكرمة', 'Makkah', ''],
            ['city', 'المدينة المنورة', 'Madinah', ''],
            ['city', 'جدة', 'Jeddah', ''],
            ['city', 'الطائف', 'Taif', ''],
            ['port', 'مطار جدة', 'JED Airport', 'Air'],
            ['port', 'مطار المدينة', 'MED Airport', 'Air'],
            ['port', 'مطار الدمام', 'DMM Airport', 'Air'],
            ['port', 'مطار الرياض', 'RUH Airport', 'Air'],
            ['port', 'ميناء جدة الإسلامي', 'Jeddah Islamic Port', 'Sea'],
            ['port', 'منفذ البطحاء', 'Al Batha Border', 'Land'],
            ['tripType', 'وصول', 'Arrival', ''],
            ['tripType', 'مغادرة', 'Departure', ''],
            ['tripType', 'بين المدن', 'Between Cities', ''],
            ['tripType', 'مزارات مكة', 'Makkah Mazarat', ''],
            ['tripType', 'مزارات المدينة', 'Madinah Mazarat', ''],
            ['tripType', 'مزارات الطائف', 'Taif Mazarat', ''],
            ['transportCompany', 'شركة سمايا', 'Samaya Company', ''],
            ['transportCompany', 'المتصدر', 'Almutasader', ''],
            ['hotel', 'امتياز الهجرة', 'Imtiyaz Al Hijrah', ''],
            ['hotel', 'سدرة المدينة', 'Sidrat Al Madinah', ''],
            ['hotel', 'وردة الريان', 'Wardat Al Rayan', ''],
            // meta holds the seat count, which drives the vehicle-per-pax maths
            ['vehicleType', 'حافلة 49', 'Bus 49', '49'],
            ['vehicleType', 'كوستر 23', 'Coaster 23', '23'],
            ['vehicleType', 'هايس 11', 'Hiace 11', '11'],
            ['vehicleType', 'جي إم سي 7', 'GMC 7', '7'],
        ];

        foreach ($rows as [$type, $ar, $en, $meta]) {
            Master::create([
                'masterId' => Ids::make('M'),
                'type' => $type, 'value_ar' => $ar, 'value_en' => $en,
                'active' => 'yes', 'meta' => $meta,
            ]);
        }
    }

    private function seedAdmin(): void
    {
        if (User::query()->where('username', 'admin')->exists()) {
            return;
        }

        // ⚠️ Change this password immediately after the first sign-in.
        AuthService::makeUser([
            'username' => 'admin',
            'role' => 'operator',
            'agentCode' => '',
            'displayName' => 'System Administrator',
            'status' => 'active',
            'department' => '',
        ], env('CRM_ADMIN_PASSWORD', 'Admin@1447'));

        $this->command?->warn('Admin user created — username: admin / password: '
            .env('CRM_ADMIN_PASSWORD', 'Admin@1447').' — change it after first login.');
    }
}
