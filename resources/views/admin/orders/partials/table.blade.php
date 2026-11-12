<div class="mt-4 overflow-x-auto rounded-2xl border border-border bg-surface">
    <table class="w-full table-fixed text-left text-sm">
        <colgroup>
            <col class="w-10">
            <col class="w-36">
            <col class="w-36">
            <col class="w-40">
            <col class="w-28">
            <col class="w-32">
            <col class="w-36">
        </colgroup>

        <thead class="border-b border-border text-[11px] uppercase tracking-wider text-muted">
            <tr>
                <th class="px-2 py-3"><input type="checkbox" id="selectAll" aria-label="Pilih semua"></th>
                <th class="px-2 py-3">Pesanan</th>
                <th class="px-2 py-3">Pembeli</th>
                <th class="px-2 py-3">Barang</th>
                <th class="px-2 py-3">Total</th>
                <th class="px-2 py-3">Status</th>
                <th class="px-2 py-3">Ubah status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($orders as $order)
                @include('admin.orders.partials.row', ['order' => $order])
            @empty
                <tr>
                    <td colspan="7" class="px-2 py-10 text-center text-muted">Belum ada pesanan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
