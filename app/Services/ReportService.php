<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\BookingRequest;
use App\Models\Brn;
use App\Models\Group;
use App\Models\HotelBooking;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

/**
 * One reporting engine, many modules. Every module resolves to a flat row list
 * plus a column spec, so filtering, searching, and exporting are written once.
 */
class ReportService
{
    public function __construct(private ActivityLogger $logger) {}

    /** Column spec per module: key + bilingual label. */
    public function columns(string $module): array
    {
        $c = fn (string $key, string $en, string $ar) => compact('key', 'en', 'ar');

        return match ($module) {
            'groups' => [
                $c('fileNo', 'File No.', 'رقم الملف'), $c('groupCode', 'Group Code', 'رقم المجموعة'),
                $c('groupName', 'Group Name', 'اسم المجموعة'), $c('agentName', 'Agent', 'الوكيل'),
                $c('agentRef', 'Agent Ref.', 'مرجع الوكيل'), $c('leaderName', 'Leader', 'المشرف'),
                $c('totalPax', 'Pax', 'العدد'), $c('arrivalDate', 'Arrival', 'الوصول'),
                $c('departureDate', 'Departure', 'المغادرة'), $c('stage', 'Stage', 'المرحلة'),
                $c('status', 'Status', 'الحالة'),
            ],
            'trips' => [
                $c('fileNo', 'File No.', 'رقم الملف'), $c('groupName', 'Group', 'المجموعة'),
                $c('agentName', 'Agent', 'الوكيل'), $c('date', 'Date', 'التاريخ'), $c('day', 'Day', 'اليوم'),
                $c('time', 'Time', 'الوقت'), $c('tripType', 'Movement', 'نوع التحرك'),
                $c('from', 'From', 'من'), $c('to', 'To', 'إلى'), $c('vehicleType', 'Vehicle', 'المركبة'),
                $c('buses', 'Qty', 'العدد'), $c('pax', 'Pax', 'الأفراد'),
                $c('driverName', 'Driver', 'السائق'), $c('driverMobile', 'Mobile', 'الجوال'),
                $c('plateNo', 'Plate No.', 'رقم اللوحة'), $c('transportCompany', 'Company', 'شركة النقل'),
                $c('orderNumber', 'Order No.', 'رقم التشغيل'), $c('tripStatus', 'Status', 'الحالة'),
            ],
            'orders' => [
                $c('orderNumber', 'Order No.', 'رقم التشغيل'), $c('transportCompany', 'Company', 'شركة النقل'),
                $c('confirmationNo', 'Confirmation', 'رقم التأكيد'), $c('fileNo', 'File No.', 'رقم الملف'),
                $c('groupName', 'Group', 'المجموعة'), $c('agentName', 'Agent', 'الوكيل'),
                $c('brn', 'BRN', 'حجز المنصة'), $c('tripsCount', 'Trips', 'الرحلات'),
                $c('status', 'Status', 'الحالة'), $c('createdAt', 'Created', 'تاريخ الإنشاء'),
            ],
            'hotels' => [
                $c('fileNo', 'File No.', 'رقم الملف'), $c('groupName', 'Group', 'المجموعة'),
                $c('agentName', 'Agent', 'الوكيل'), $c('city', 'City', 'المدينة'),
                $c('hotelName', 'Hotel', 'الفندق'), $c('rooms', 'Rooms', 'الغرف'),
                $c('checkIn', 'Check-in', 'الدخول'), $c('checkOut', 'Check-out', 'الخروج'),
                $c('nights', 'Nights', 'الليالي'), $c('rsvNo', 'Rsv No.', 'رقم الحجز'),
                $c('status', 'Status', 'الحالة'),
            ],
            'brn' => [
                $c('fileNo', 'File No.', 'رقم الملف'), $c('groupName', 'Group', 'المجموعة'),
                $c('agentName', 'Agent', 'الوكيل'), $c('brnTypeLbl', 'Type', 'النوع'),
                $c('hotelName', 'Name', 'الاسم'),
                $c('brnNumber', 'BRN No.', 'رقم الاتفاقية'), $c('checkIn', 'Check-in', 'الدخول'),
                $c('checkOut', 'Check-out', 'الخروج'), $c('roomsCount', 'Rooms', 'الغرف'),
            ],
            'requests' => [
                $c('requestId', 'Req No.', 'رقم الطلب'), $c('type', 'Type', 'النوع'),
                $c('agentName', 'Agent', 'الوكيل'), $c('groupName', 'Group', 'المجموعة'),
                $c('totalPax', 'Pax', 'العدد'), $c('submittedAt', 'Submitted', 'تاريخ الإرسال'),
                $c('reviewedBy', 'Reviewed By', 'تمت المراجعة بواسطة'), $c('status', 'Status', 'الحالة'),
            ],
            default => [],
        };
    }

    /** Which field a module's date-range filter applies to. */
    private function dateField(string $module): string
    {
        return [
            'groups' => 'arrivalDate', 'trips' => 'date', 'orders' => 'createdAt',
            'hotels' => 'checkIn', 'brn' => 'checkIn', 'requests' => 'submittedAt',
        ][$module] ?? '';
    }

    /** The raw row set for a module, already scoped to the caller's role. */
    private function rows(Session $session, string $module): array
    {
        $groups = Group::query()->get()->keyBy('groupId');
        $agentNames = Agent::query()->pluck('agentName', 'agentCode');
        $isAgent = $session->isAgent();

        $mine = function (string $groupId) use ($groups, $isAgent, $session) {
            if (! $isAgent) {
                return true;
            }
            $g = $groups[$groupId] ?? null;

            return $g && $g->agentCode === $session->agentCode;
        };

        // decorate a child row with its group + agent context
        $ctx = function (array $row, string $groupId) use ($groups, $agentNames) {
            $g = $groups[$groupId] ?? null;

            return $row + [
                'groupId' => $groupId,
                'fileNo' => (string) ($g->fileNo ?? ''),
                'groupName' => (string) ($g->groupName ?? ''),
                'agentName' => $agentNames[$g->agentCode ?? ''] ?? (string) ($g->agentCode ?? ''),
                'stage' => (string) ($g->stage ?? ''),
            ];
        };

        return match ($module) {
            'groups' => $groups->filter(fn ($g) => $mine($g->groupId))
                ->map(fn (Group $g) => $g->toRow() + ['agentName' => $agentNames[$g->agentCode] ?? (string) $g->agentCode])
                ->values()->all(),

            'trips' => (function () use ($mine, $ctx) {
                $primaryOrder = [];
                foreach (TransportOrder::query()->get() as $o) {
                    $primaryOrder[$o->groupId] ??= $o;
                }

                return Trip::query()->get()->filter(fn ($tr) => $mine($tr->groupId))
                    ->map(function (Trip $tr) use ($ctx, $primaryOrder) {
                        $o = $primaryOrder[$tr->groupId] ?? null;

                        return $ctx($tr->toRow(), (string) $tr->groupId) + [
                            'transportCompany' => (string) ($o->transportCompany ?? ''),
                            'orderNumber' => (string) ($o->orderNumber ?? ''),
                        ];
                    })->values()->all();
            })(),

            'orders' => (function () use ($mine, $ctx) {
                $tripCounts = Trip::query()->get()->countBy('groupId');

                return TransportOrder::query()->get()->filter(fn ($o) => $mine($o->groupId))
                    ->map(fn (TransportOrder $o) => $ctx($o->toRow(), (string) $o->groupId)
                        + ['tripsCount' => $tripCounts[$o->groupId] ?? 0])
                    ->values()->all();
            })(),

            'hotels' => HotelBooking::query()->get()->filter(fn ($h) => $mine($h->groupId))
                ->map(fn (HotelBooking $h) => $ctx($h->toRow(), (string) $h->groupId))->values()->all(),

            'brn' => Brn::query()->get()->filter(fn ($b) => $mine($b->groupId))
                ->map(fn (Brn $b) => $ctx(
                    $b->toRow() + ['brnTypeLbl' => $b->brnType === 'Catering' ? 'Catering / إعاشة' : 'Hotel / فندق'],
                    (string) $b->groupId,
                ))->values()->all(),

            'requests' => BookingRequest::query()
                ->when($isAgent, fn ($q) => $q->where('agentCode', $session->agentCode))
                ->orderBy('seq')->get()
                ->map(function (BookingRequest $r) use ($agentNames, $groups) {
                    $payload = json_decode((string) $r->payloadJSON, true);
                    $group = is_array($payload) ? ($payload['group'] ?? $payload) : [];

                    return $r->toRow() + [
                        'agentName' => $agentNames[$r->agentCode] ?? (string) $r->agentCode,
                        'groupName' => Crm::s($group['groupName'] ?? '') ?: (string) ($groups[$r->groupId]->groupName ?? ''),
                        'totalPax' => Crm::s($group['totalPax'] ?? ''),
                    ];
                })->values()->all(),

            default => [],
        };
    }

    /**
     * Run a report.
     *
     * `search` may hold several values separated by comma, semicolon, or
     * newline — a row matches if ANY term appears in ANY of its columns, which
     * is what you want when pasting a list of file numbers or group codes.
     */
    public function run(Session $session, array $p): array
    {
        $module = Crm::s($p['module'] ?? '');
        if (! in_array($module, Crm::REPORT_MODULES, true)) {
            return ['ok' => false, 'error' => 'BAD_MODULE'];
        }

        $columns = $this->columns($module);
        $rows = collect($this->rows($session, $module));

        // ── filters ──
        if (! empty($p['agentCode'])) {
            $code = Crm::s($p['agentCode']);
            $codeByName = Agent::query()->pluck('agentCode', 'agentName');
            $rows = $rows->filter(fn ($r) => ($r['agentCode'] ?? '') === $code
                || ($codeByName[$r['agentName'] ?? ''] ?? null) === $code);
        }
        if (! empty($p['status'])) {
            $status = Crm::s($p['status']);
            $rows = $rows->filter(fn ($r) => (($r['status'] ?? '') ?: ($r['tripStatus'] ?? '')) === $status);
        }
        if (! empty($p['stage'])) {
            $stage = Crm::s($p['stage']);
            $rows = $rows->filter(fn ($r) => ($r['stage'] ?? '') === $stage);
        }

        $df = $this->dateField($module);
        $from = Crm::s($p['dateFrom'] ?? '');
        $to = Crm::s($p['dateTo'] ?? '');
        if ($df !== '' && ($from !== '' || $to !== '')) {
            $rows = $rows->filter(function ($r) use ($df, $from, $to) {
                $v = substr((string) ($r[$df] ?? ''), 0, 10);
                if ($v === '') {
                    return false;
                }

                return ! (($from !== '' && $v < $from) || ($to !== '' && $v > $to));
            });
        }

        // ── multi-value search (OR across terms, across the displayed columns) ──
        $terms = array_values(array_filter(array_map(
            fn ($t) => mb_strtolower(trim($t)),
            preg_split('/[,;\n]+/', (string) ($p['search'] ?? '')) ?: [],
        ), 'strlen'));
        if ($terms) {
            $rows = $rows->filter(function ($r) use ($columns, $terms) {
                $hay = mb_strtolower(implode(' ', array_map(fn ($c) => (string) ($r[$c['key']] ?? ''), $columns)));
                foreach ($terms as $term) {
                    if (str_contains($hay, $term)) {
                        return true;
                    }
                }

                return false;
            });
        }

        // newest / soonest first wherever a date exists
        if ($df !== '') {
            $rows = $rows->sortByDesc(fn ($r) => (string) ($r[$df] ?? ''));
        }

        // trim the payload to the report columns (+ id for drill-through)
        $out = $rows->map(function ($r) use ($columns) {
            $o = ['groupId' => (string) ($r['groupId'] ?? '')];
            foreach ($columns as $c) {
                $o[$c['key']] = Crm::s($r[$c['key']] ?? '');
            }

            return $o;
        })->values()->all();

        return ['ok' => true, 'module' => $module, 'columns' => $columns, 'rows' => $out, 'total' => count($out)];
    }

    /** Filter option lists for the report UI. */
    public function meta(Session $session): array
    {
        return [
            'ok' => true,
            'agents' => $session->isAgent() ? [] : Agent::query()->where('status', 'active')
                ->get(['agentCode', 'agentName'])
                ->map(fn ($a) => ['agentCode' => $a->agentCode, 'agentName' => (string) $a->agentName])->all(),
            'statuses' => Crm::GROUP_STATUSES,
            'stages' => Crm::GROUP_STAGES,
        ];
    }

    /** Export the same rows the screen shows as a real .xlsx file. */
    public function excel(Session $session, array $p): array
    {
        $res = $this->run($session, $p);
        if (! ($res['ok'] ?? false)) {
            return $res;
        }

        $lang = ($p['lang'] ?? '') === 'ar' ? 'ar' : 'en';

        try {
            $book = new Spreadsheet;
            $sheet = $book->getActiveSheet();
            $sheet->setTitle($res['module']);

            $data = [array_map(fn ($c) => $c[$lang] ?: $c['en'], $res['columns'])];
            foreach ($res['rows'] as $row) {
                $data[] = array_map(fn ($c) => $row[$c['key']] ?? '', $res['columns']);
            }
            $sheet->fromArray($data, null, 'A1');

            $lastColumn = $sheet->getHighestColumn();
            $header = $sheet->getStyle('A1:'.$lastColumn.'1');
            $header->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $header->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF17263F');
            $sheet->freezePane('A2');
            foreach (range('A', $lastColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            if ($lang === 'ar') {
                $sheet->setRightToLeft(true);
            }

            $path = tempnam(sys_get_temp_dir(), 'crm-report-');
            (new Xlsx($book))->save($path);
            $bytes = (string) file_get_contents($path);
            @unlink($path);
            $book->disconnectWorksheets();

            $filename = 'report-'.$res['module'].'-'.Dates::now()->format('Ymd-Hi').'.xlsx';
            $this->logger->log($session, 'REPORT_EXPORT', '', '',
                $res['module'].' → xlsx ('.count($res['rows']).' rows)');

            return ['ok' => true, 'base64' => base64_encode($bytes), 'filename' => $filename];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => 'SERVER', 'message' => $e->getMessage()];
        }
    }
}
