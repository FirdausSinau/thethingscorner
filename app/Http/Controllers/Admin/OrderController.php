<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'q' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $byPaidDate = $request->filled('dari') || $request->filled('sampai');

        $query = Order::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.trim((string) $request->input('q')).'%';

                $query->where(fn ($query) => $query
                    ->whereLike('order_code', $term)
                    ->orWhereLike('recipient', $term));
            })
            ->when($request->filled('dari'), fn ($query) => $query->where('paid_at', '>=', Carbon::parse($request->input('dari'))->startOfDay()))
            ->when($request->filled('sampai'), fn ($query) => $query->where('paid_at', '<=', Carbon::parse($request->input('sampai'))->endOfDay()));

        $sumTotal = (clone $query)->sum('total');

        $orders = $query
            ->with(['user', 'items'])
            ->when($byPaidDate, fn ($query) => $query->orderByDesc('paid_at'), fn ($query) => $query->latest())
            ->paginate(15)
            ->withQueryString();

        $counts = DB::table('orders')
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.index', [
            'orders' => $orders,
            'counts' => $counts,
            'statuses' => OrderStatus::cases(),
            'sumTotal' => $sumTotal,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'user',
            'items',
            'reservations.variant.color.product',
            'statusLogs.actor',
        ]);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function updateStatus(Request $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $status = OrderStatus::from($data['status']);
        $service->change($order, $status, $request->user());

        return back()->with('status', "Status {$order->order_code} diubah menjadi {$status->label()}.");
    }

    public function bulkStatus(Request $request, OrderStatusService $service): RedirectResponse
    {
        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ], [
            'order_ids.required' => 'Pilih minimal satu pesanan dulu.',
            'order_ids.min' => 'Pilih minimal satu pesanan dulu.',
            'status.required' => 'Pilih status tujuan.',
        ]);

        $status = OrderStatus::from($data['status']);
        $changed = 0;
        $failed = [];

        foreach (Order::query()->whereIn('id', $data['order_ids'])->get() as $order) {
            try {
                $service->change($order, $status, $request->user());
                $changed++;
            } catch (ValidationException $exception) {
                $failed[] = collect($exception->errors())->flatten()->first();
            }
        }

        return back()
            ->with('status', "{$changed} pesanan diubah menjadi {$status->label()}.")
            ->withErrors($failed ? ['bulk' => $failed] : []);
    }
}
