<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hóa đơn')</title>
    <style>
        /* Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* Khổ giấy in hóa đơn 80mm */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        html, body {
            width: 80mm;
            margin: 0 auto;
            padding: 0;
            font-family: 'Courier New', 'Consolas', monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            line-height: 1.35;
        }

        .receipt {
            width: 72mm;                /* chừa biên 4mm mỗi bên */
            margin: 0 auto;
            padding: 6mm 0 10mm 0;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .left   { text-align: left; }

        .bold   { font-weight: 700; }
        .small  { font-size: 11px; }
        .xsmall { font-size: 10px; }

        .store-name {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            gap: 4px;
        }

        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
        .divider-solid {
            border: none;
            border-top: 1px solid #000;
            margin: 4px 0;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
        }
        table.items th,
        table.items td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 11px;
        }
        table.items th {
            text-align: left;
            border-bottom: 1px dashed #000;
            font-weight: 700;
        }
        table.items td.qty   { width: 14mm; text-align: center; }
        table.items td.price { width: 22mm; text-align: right; }
        table.items td.total { width: 24mm; text-align: right; }

        .item-name {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .totals {
            width: 100%;
            margin-top: 4px;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 1px 0;
        }
        .totals .grand {
            font-size: 14px;
            font-weight: 700;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            margin-top: 3px;
        }

        .footer-note {
            margin-top: 8px;
            text-align: center;
            font-size: 11px;
        }

        /* In tự động khi load xong */
        @media print {
            body { width: 80mm; }
            .no-print { display: none !important; }
        }

        /* Nút bấm khi xem trên màn hình (ẩn khi in) */
        .toolbar {
            position: fixed;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 6px;
            z-index: 99;
        }
        .toolbar button {
            padding: 6px 10px;
            border: 1px solid #333;
            background: #fff;
            color: #000;
            font-size: 12px;
            cursor: pointer;
            border-radius: 4px;
        }
        .toolbar button:hover { background: #f0f0f0; }

        @media screen {
            body { background: #e9ecef; padding: 16px 0; }
            .receipt {
                background: #fff;
                box-shadow: 0 0 6px rgba(0,0,0,0.15);
                padding: 10mm 8mm;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button onclick="window.print()">🖨️ In</button>
        <button onclick="window.close()">Đóng</button>
    </div>

    <div class="receipt">
        @yield('content')
    </div>

    <script>
        // Khi trang vừa load xong -> tự mở hộp thoại in (giúp máy in bill ra luôn)
        window.addEventListener('load', function () {
            // Dùng setTimeout nhỏ để trình duyệt kịp render font/ảnh
            setTimeout(function () {
                try { window.print(); } catch (e) { console.error(e); }
            }, 250);
        });
    </script>
</body>
</html>
