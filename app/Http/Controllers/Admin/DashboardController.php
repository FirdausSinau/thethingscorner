<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductVariant;
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

        $count = fn (string ...$values) => (int) collect($values)->sum(fn ($v) => $counts[$v] ?? 0);

        $cards = [
            ['label' => 'Perlu diproses', 'value' => $count('DIBAYAR'),   'note' => 'Sudah dibayar, menunggu diproses'],
            ['label' => 'Perlu dikirim',  'value' => $count('DIKEMAS'),   'note' => 'Sudah dikemas, menunggu dikirim'],
            ['label' => 'Pending',        'value' => $count('PENDING'),   'note' => 'Sudah diproses, ada kendala'],
            ['label' => 'Dikirim',        'value' => $count('DIKIRIM'),   'note' => 'Sedang dalam pengiriman'],
            ['label' => 'Selesai',        'value' => $count('SELESAI'),   'note' => 'Sudah diterima pembeli'],
            ['label' => 'Dibatalkan',     'value' => $count('DIBATALKAN', 'KEDALUWARSA'), 'note' => 'Dibatalkan atau kedaluwarsa'],
        ];

        return view('admin.dashboard', compact('cards'));
    }
}