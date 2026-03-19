<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; padding: 40px; }
        .header { margin-bottom: 30px; }
        .company-name { font-size: 20px; font-weight: bold; }
        .date { color: #666; margin-top: 4px; }
        .customer { margin-bottom: 24px; }
        .customer-label { font-size: 10px; text-transform: uppercase; color: #666; letter-spacing: 0.5px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { text-align: left; padding: 8px; border-bottom: 2px solid #333; font-size: 11px; text-transform: uppercase; color: #666; letter-spacing: 0.5px; }
        th:last-child, td:last-child { text-align: right; }
        td { padding: 8px; border-bottom: 1px solid #e5e5e5; }
        .summary { width: 280px; margin-left: auto; }
        .summary-row { display: flex; padding: 4px 0; }
        .summary table { margin-bottom: 0; }
        .summary td { border-bottom: 1px solid #e5e5e5; padding: 6px 8px; }
        .summary .total td { border-top: 2px solid #333; border-bottom: none; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        @if($companyName)
            <div class="company-name">{{ $companyName }}</div>
        @endif
        <div class="date">Invoice &mdash; {{ $date }}</div>
    </div>

    <div class="customer">
        <div class="customer-label">Bill to</div>
        <div><strong>{{ $customer->name }}</strong></div>
        @if($customer->email)<div>{{ $customer->email }}</div>@endif
        @if($customer->address)<div>{{ $customer->address }}</div>@endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Card</th>
                <th>Rate type</th>
                <th>Hours</th>
                <th>Unit rate</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineItems as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['rate_type'] === 'hourly' ? 'Hourly' : 'Fixed' }}</td>
                    <td>{{ $item['hours'] !== null ? number_format($item['hours'], 2) : '—' }}</td>
                    <td>${{ number_format($item['unit_rate'], 2) }}</td>
                    <td>${{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tbody>
                <tr>
                    <td>Subtotal</td>
                    <td>${{ number_format($subtotal, 2) }}</td>
                </tr>
                @if($shipping > 0)
                    <tr>
                        <td>Shipping</td>
                        <td>${{ number_format($shipping, 2) }}</td>
                    </tr>
                @endif
                @if($packaging > 0)
                    <tr>
                        <td>Packaging</td>
                        <td>${{ number_format($packaging, 2) }}</td>
                    </tr>
                @endif
                @if($handling > 0)
                    <tr>
                        <td>Handling</td>
                        <td>${{ number_format($handling, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Tax ({{ number_format($taxRate, 2) }}%)</td>
                    <td>${{ number_format($tax, 2) }}</td>
                </tr>
                <tr class="total">
                    <td>Total</td>
                    <td>${{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
