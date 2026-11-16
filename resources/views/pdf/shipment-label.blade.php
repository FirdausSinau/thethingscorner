<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Resi {{ $shipment->tracking_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 100mm 150mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 7mm;
            color: #111;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9pt;
            line-height: 1.35;
        }

        .header {
            border-bottom: 1.5px solid #111;
            padding-bottom: 3mm;
        }

        .store {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.3pt;
        }

        .courier {
            margin-top: 1mm;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .barcode {
            margin-top: 3mm;
            text-align: center;
        }

        .barcode img {
            width: 78mm;
            height: 18mm;
        }

        .tracking {
            margin-top: 1mm;
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            word-break: break-all;
        }

        .section {
            margin-top: 3mm;
            border-top: 1px solid #bbb;
            padding-top: 2mm;
        }

        .section-title {
            color: #555;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 0.4pt;
            text-transform: uppercase;
        }

        .recipient {
            margin-top: 1mm;
            font-size: 12pt;
            font-weight: bold;
        }

        .phone {
            margin-top: 0.5mm;
            font-size: 9pt;
        }

        .address {
            margin-top: 1mm;
            font-size: 8.5pt;
        }

        .meta {
            margin-top: 3mm;
            border-top: 1px solid #bbb;
            padding-top: 2mm;
            font-size: 7.5pt;
        }

        .meta-row {
            display: table;
            width: 100%;
        }

        .meta-row span {
            display: table-cell;
        }

        .meta-row span:last-child {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="store">{{ $storeName }}</div>
        <div class="courier">{{ $shipment->courier }} {{ $shipment->service }}</div>
    </div>

    @if ($barcode)
        <div class="barcode">
            <img src="data:image/png;base64,{{ $barcode }}" alt="Barcode {{ $shipment->tracking_number }}">
        </div>
    @endif

    <div class="tracking">{{ $shipment->tracking_number }}</div>

    <div class="section">
        <div class="section-title">Penerima</div>
        <div class="recipient">{{ $order->recipient }}</div>
        <div class="phone">{{ $order->phone }}</div>
        <div class="address">
            {{ $order->address }}<br>
            {{ $order->city }} {{ $order->postal_code }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Pengirim</div>
        <div>{{ $storeName }}</div>
        <div class="address">
            {{ data_get($origin, 'city', 'Bandung') }} {{ data_get($origin, 'postal_code', '') }}
        </div>
    </div>

    <div class="meta">
        <div class="meta-row">
            <span>Order {{ $order->order_code }}</span>
            <span>{{ $itemCount }} item</span>
        </div>
        <div class="meta-row">
            <span>Berat {{ number_format($weightGrams, 0, ',', '.') }} gram</span>
            <span>{{ $shipment->provider_reference ?: $shipment->provider }}</span>
        </div>
    </div>
</body>
</html>
