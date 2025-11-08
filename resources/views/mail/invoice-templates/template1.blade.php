<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ __('messages.invoice') }}</title>
  <style>
    body {
        font-family: Arial, sans-serif;
    }

    .currency-font {
      font-family: 'DejaVu Sans', sans-serif;
    }
    h1, h2, h3, h4, h5, h6 {
      color: #000000;
    }

    p {
      margin: 0 0 8px;
    }

    .invoice {
      width: 190mm;
      height: auto;
      box-sizing: border-box;
    }

    .invoice-header {
      text-align: center;
    }

    .invoice-header h1 {
      margin: 0 0 10px;
    }

    .invoice-logo-section {
      text-align: center;
      margin: 0 0 20px;
      padding: 0 0 20px;
      border-bottom: 1px solid #f1f1f1;
    }

    .invoice-detail-part {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      margin: 16px 0;
    }

    .invoice-customer, .invoice-billing {
      width: 45%;
    }

    .invoice-branch {
      width: 100%;
      text-align: right;
    }

    .invoice-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    .invoice-table th, .invoice-table td {
      border: 1px solid #f1f1f1;
      padding: 16px;
      font-size: 14px;
    }

    .invoice-table th {
      background-color: #f2f2f2;
    }

    .text-end {
      text-align: right;
    }

    strong {
      color: #000000;
    }

    table th {
      color: #000000;
    }

    .thank-you {
      margin-top: 20px;
      background: #f1f1f1;
      padding: 16px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="invoice">
    <div class="invoice-logo-section">
      @php
        $logo = null;
        $logoSetting = Vendorsetting('logo');

        if ($logoSetting) {
            // If logo is set in settings, use it
            $logoPath = parse_url($logoSetting, PHP_URL_PATH) ?? $logoSetting;
            // ...existing code...
            $logoPath = ltrim($logoPath, '/');
            // Remove the first subfolder dynamically (not static like 'frezka')
            $logoPath = preg_replace('/^[^\/]+\//', '', $logoPath);
            // Build absolute public path
            $logoPath = public_path($logoPath);

            if (file_exists($logoPath) && is_readable($logoPath)) {
                try {
                    // Get MIME type of the image
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $logoPath);
                    finfo_close($finfo);

                    $extensions = [
                        'image/jpeg' => 'jpeg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                        'image/svg+xml' => 'svg+xml',
                        'image/webp' => 'webp'
                    ];

                    $extension = $extensions[$mimeType] ?? 'jpeg';

                    $logoData = file_get_contents($logoPath);
                    $logo = 'data:image/' . $extension . ';base64,' . base64_encode($logoData);
                } catch (\Exception $e) {
                    \Log::error('Logo processing error: ' . $e->getMessage());
                }
            }
        }

        // If no logo from settings or failed to load, try default logo
        if (!$logo) {
            $defaultLogoPath = public_path('img/logo/logo.png');
            if (file_exists($defaultLogoPath)) {
                try {
                    $logoData = file_get_contents($defaultLogoPath);
                    $logo = 'data:image/png;base64,' . base64_encode($logoData);
                } catch (\Exception $e) {
                    \Log::error('Default logo processing error: ' . $e->getMessage());
                }
            }
        }
      @endphp
      
      @if($logo)
          <img src="{{ $logo }}" alt="Company Logo" style="max-width: 150px; height: auto;">
      @else
          <h3 style="margin: 0; color: #666;">{{ config('app.name', 'Logo Not Found') }}</h3>
      @endif
  </div>
    <div class="text-end">
      <p><strong>{{ __('messages.invoice_ID') }}:</strong> {{ __('messages.booking') }}{{$data['id']}}</p>
    </div>

    <div class="invoice-detail-part">
      <div class="invoice-customer">
        <h3>{{ __('messages.customer_info') }}</h3>
        <p>{{$data['user_name']}}</p>
        <p>{{$data['email']}}</p>
        <p>{{$data['mobile']}}</p>
      </div>
      <div class="invoice-billing">
        <h3>{{ __('messages.billing_address') }}</h3>
        <p>{{$data['venue_address']}}</p>
      </div>
      <div class="invoice-branch">
        <h3>{{ __('messages.branch_details') }}</h3>
        <p>{{ __('messages.branch_name') }}: {{ $data['branch_name'] }}</p>
        <p>{{ __('messages.contact_number') }}: {{ $data['branch_number'] }}</p>
        <p>{{ __('messages.email') }}</p>
      </div>
    </div>

    <div class="invoice-info">
      <div>
        <p><strong>{{ __('messages.booking_date') }}:</strong></p>
        <p>{{$data['booking_date']}}</p>
      </div>
      <div>
        <p><strong>{{ __('messages.payment_method') }}:</strong></p>
        <p>{{ $data['transaction_type'] === 'upi' ? 'UPI' : ucwords($data['transaction_type']) }}</p>
      </div>
    </div>

    <table class="invoice-table">
      <thead>
        <tr>
          <th>{{ __('messages.item_name') }}</th>
          <th>{{ __('messages.quantity') }}</th>
          <th>{{ __('messages.unit_price') }}</th>
          <th class="text-end">{{ __('messages.total') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data['extra']['services'] as $key => $value)
          <tr>
            <td>{{$value['service_name']}}</td>
            <td class="text-end">1</td>
            <td class="text-end currency-font">{{ \Currency::format($value['service_price']) }}</td>
            <td class="text-end currency-font">{{ \Currency::format($value['service_price']) }}</td>
          </tr>
        @endforeach
        @foreach($data['extra']['products'] as $key => $value)
          <tr>
            <td>{{$value['product_name']}}</td>
            <td class="text-end">{{$value['product_qty']}}</td>
            @php
              $price = $value['discounted_price'] != $value['product_price'] ? $value['discounted_price'] : $value['product_price'];
              // $productPrice += $price * $value['product_qty'];
            @endphp
            <td class="text-end currency-font">{{ \Currency::format($price) }}</td>
            <td class="text-end currency-font">{{ \Currency::format($price * $value['product_qty']) }}</td>
          </tr>
        @endforeach
       @if(isset($data['extra']) && is_array($data['extra']) && isset($data['extra']['packages']) && is_array($data['extra']['packages']))
          @foreach($data['extra']['packages'] as $key => $value)
            <tr>
              <td>{{ $value['name'] ?? '-' }}</td>
              <td>1</td>
              <td class="text-end currency-font">{{ \Currency::format(isset($value['package_price']) ? $value['package_price'] : 0) }}</td>
              <td class="text-end currency-font">{{ \Currency::format(isset($value['package_price']) ? $value['package_price'] : 0) }}</td>
            </tr>
          @endforeach
      @endif
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" class="text-end"><strong>{{ __('messages.sub_total') }}:</strong></td>
          <td class="text-end currency-font">{{ \Currency::format($data['serviceAmount'] + $data['product_price'] + ($data['package_price'] ?? 0)) }}</td>
        </tr>
        <tr>
          <td colspan="3" class="text-end"><strong>{{ __('messages.tips') }}:</strong></td>
          <td class="text-end currency-font">{{ \Currency::format($data['tip_amount']) }}</td>
        </tr>
        <tr>
          <td colspan="3" class="text-end"><strong>{{ __('messages.tax') }}:</strong></td>
          <td class="text-end currency-font">{{ \Currency::format($data['tax_amount']) }}</td>
        </tr>
        @if(isset($data['coupon_discount']) && $data['coupon_discount'] > 0)
        <tr>
          <td colspan="3" class="text-end"><strong>{{ __('messages.coupon_discount') }}:</strong></td>
          <td class="text-end currency-font">{{ \Currency::format($data['coupon_discount']) }}</td>
        </tr>
        @endif
        <tr>
          <td colspan="3" class="text-end"><strong>{{ __('messages.grand_total') }}:</strong></td>
          <td class="text-end currency-font">{{ \Currency::format($data['grand_total']) }}</td>
        </tr>
      </tfoot>
    </table>

    <div class="thank-you">
      <p>{{ setting('spacial_note') }}</p>
    </div>
  </div>
</body>
</html>
