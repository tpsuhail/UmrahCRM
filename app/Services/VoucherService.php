<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Brn;
use App\Models\HotelBooking;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Session;
use Dompdf\Dompdf;
use Dompdf\Options;
use Throwable;

/** Voucher data assembly and HTML → PDF export. */
class VoucherService
{
    public function __construct(
        private ActivityLogger $logger,
        private GroupService $groups,
        private AdminService $admin,
    ) {}

    /** Everything a group voucher or transport order needs, in one call. */
    public function get(Session $session, array $p): array
    {
        $guard = $this->groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }
        $group = $guard['group'];
        $groupId = $group->groupId;
        $agent = Agent::find($group->agentCode);

        return [
            'ok' => true,
            'company' => $this->admin->company(),
            'group' => $group->toRow(),
            'agent' => [
                'agentName' => (string) ($agent->agentName ?? $group->agentCode),
                'country' => (string) ($agent->country ?? ''),
                'contactName' => (string) ($agent->contactName ?? ''),
                'mobile' => (string) ($agent->mobile ?? ''),
                'email' => (string) ($agent->email ?? ''),
            ],
            'hotels' => HotelBooking::query()->where('groupId', $groupId)
                ->where('status', '!=', 'Cancelled')->get()->map(fn ($r) => $r->toRow())->all(),
            'brn' => Brn::query()->where('groupId', $groupId)->get()->map(fn ($r) => $r->toRow())->all(),
            'orders' => TransportOrder::query()->where('groupId', $groupId)->get()->map(fn ($r) => $r->toRow())->all(),
            'trips' => Trip::query()->where('groupId', $groupId)->get()
                ->sortBy(fn ($tr) => [Dates::parseYmd($tr->date) ?? 0, (string) $tr->time])
                ->map(fn ($r) => $r->toRow())->values()->all(),
            'generatedAt' => Dates::nowStr(),
        ];
    }

    /**
     * Render client-composed HTML into a PDF, returned base64-encoded so the
     * browser can hand it straight to a download — same contract as before,
     * with Dompdf standing in for the old Drive conversion.
     */
    public function pdfFromHtml(Session $session, array $p): array
    {
        $html = (string) ($p['html'] ?? '');
        if ($html === '' || strlen($html) > 400000) {
            return ['ok' => false, 'error' => 'BAD_HTML'];
        }

        try {
            $options = new Options;
            $options->set('isRemoteEnabled', true);      // logos are served from this app
            $options->set('defaultFont', 'DejaVu Sans'); // ships with Dompdf; covers Arabic
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', str_contains($html, 'landscape') ? 'landscape' : 'portrait');
            $dompdf->render();

            $filename = $this->safeFilename(Crm::s($p['filename'] ?? 'document'));
            $this->logger->log($session, 'PDF_EXPORT', '', '', $filename);

            return ['ok' => true, 'base64' => base64_encode($dompdf->output()), 'filename' => $filename];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => 'PDF_FAIL', 'message' => $e->getMessage()];
        }
    }

    private function safeFilename(string $name): string
    {
        $clean = preg_replace('/[^\w\x{0600}-\x{06FF}\- ]/u', '', $name) ?? '';
        $clean = mb_substr(trim($clean), 0, 80);

        return ($clean ?: 'document').'.pdf';
    }
}
