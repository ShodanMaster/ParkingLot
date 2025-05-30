<!-- resources/views/getPrint.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Allocation</title>
    <style>
        /* Add any styles to make the print look good */
        body {
            font-family: Arial, sans-serif;
        }
        .allocation-info {
            margin: 20px;
        }
        .allocation-info h2, .allocation-info p {
            margin: 0;
        }
        .allocation-info .barcode {
            margin-top: 10px;
        }
        .qr-code {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="allocation-info">
        <div class="qr-code">
            <img src="{{asset($allocate->QRCode->path)}}" alt="NO_QR">
        </div>
        <p><strong>Vehicle Number:</strong> {{ $allocate->vehicle_number }}</p>
        <p><strong>Qr Code:</strong> {{ $allocate->qrcode }}</p>
        <p><strong>Location:</strong> {{ $allocate->location->name }}</p>


    </div>

    <script>
        window.onload = function () {
            setTimeout(function () {
                window.print();
            }, 500);
        };

        window.onafterprint = function () {
            
            setTimeout(function () {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>
