<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\OrderStatusService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    private const PAID_STATUSES = ['DIBAYAR'];
    private const COMPLETED_STATUSES = ['SELESAI'];

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'q'      => ['nullable', 'string', 'max:100'],
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $byPaidDate = $request->filled('dari') || $request->filled('sampai');

        $query = Order::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->input('q') . '%';
                $q->where(fn ($w) => $w->where('order_code', 'ilike', $term)
                    ->orWhere('recipient', 'ilike', $term));
            })
            ->when($request->filled('dari'), fn ($q) => $q->where('paid_at', '>=', Carbon::parse($request->input('dari'))->startOfDay()))
            ->when($request->filled('sampai'), fn ($q) => $q->where('paid_at', '<=', Carbon::parse($request->input('sampai'))->endOfDay()));

        $sumTotal = (clone $query)->sum('total');

        $orders = $query
            ->with(['user', 'items'])
            ->when($byPaidDate, fn ($q) => $q->orderByDesc('paid_at'), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $counts = DB::table('orders')
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.index', [
            'orders'   => $orders,
            'counts'   => $counts,
            'statuses' => OrderStatus::cases(),
            'sumTotal' => $sumTotal,
        ]);
    }


    public function updateStatus(Request $request, Order $order, OrderStatusService $service)
    {
        $data = $request->validate(['status' => ['required', Rule::enum(OrderStatus::class)]]);
        $status = OrderStatus::from($data['status']);

        $service->change($order, $status); 

        return back()->with('status', "Status {$order->order_code} diubah menjadi {$status->label()}.");
    }

    public function bulkStatus(Request $request, OrderStatusService $service)
    {
        $data = $request->validate([
            'order_ids'   => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
            'status'      => ['required', Rule::enum(OrderStatus::class)],
        ], [
            'order_ids.required' => 'Pilih minimal satu pesanan dulu.',
            'order_ids.min'      => 'Pilih minimal satu pesanan dulu.',
            'status.required'    => 'Pilih status tujuan.',
        ]);

        $status = OrderStatus::from($data['status']);
        $ok = 0;
        $failed = [];

        foreach (Order::whereIn('id', $data['order_ids'])->get() as $order) {
            try {
                $service->change($order, $status);
                $ok++;
            } catch (ValidationException $e) {
                $failed[] = collect($e->errors())->flatten()->first();
            }
        }

        return back()
            ->with('status', "{$ok} pesanan diubah menjadi {$status->label()}.")
            ->withErrors($failed ? ['bulk' => $failed] : []);
    }

    private function apply(Order $order, OrderStatus $status): void
    {
        $order->status = $status;

        if (in_array($status->value, self::PAID_STATUSES, true) && ! $order->paid_at) {
            $order->paid_at = now();
        }

        if (in_array($status->value, self::COMPLETED_STATUSES, true) && ! $order->completed_at) {
            $order->completed_at = now();
        }

        $order->save();
    }
}