<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $counts = DB::table('orders')
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (string ...$statuses): int => (int) collect($statuses)
            ->sum(fn (string $status): int => (int) ($counts[$status] ?? 0));

        $cards = [
            ['label' => 'Menunggu pembayaran', 'value' => $count('MENUNGGU_PEMBAYARAN'), 'note' => 'Belum dibayar'],
            ['label' => 'Dibayar', 'value' => $count('DIBAYAR'), 'note' => 'Pembayaran diterima, perlu dikonfirmasi'],
            ['label' => 'Menunggu konfirmasi', 'value' => $count('MENUNGGU_KONFIRMASI'), 'note' => 'Menunggu konfirmasi admin'],
            ['label' => 'Dikemas', 'value' => $count('DIKEMAS'), 'note' => 'Siap dikirim'],
            ['label' => 'Dikirim', 'value' => $count('DIKIRIM'), 'note' => 'Dalam pengiriman'],
            ['label' => 'Selesai', 'value' => $count('SELESAI'), 'note' => 'Sudah diterima pembeli'],
            ['label' => 'Dibatalkan', 'value' => $count('DIBATALKAN', 'KEDALUWARSA'), 'note' => 'Dibatalkan atau kedaluwarsa'],
        ];

        return view('admin.dashboard', compact('cards'));
    }
}
