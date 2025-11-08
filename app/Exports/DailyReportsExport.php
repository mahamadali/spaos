<?php

namespace App\Exports;

use Currency;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Booking\Models\Booking;
use Maatwebsite\Excel\Concerns\WithStyles;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class DailyReportsExport implements FromCollection, WithHeadings,WithStyles
{
    public array $columns;

    public array $dateRange;

    public function __construct($columns, $dateRange)
    {
        $this->columns = $columns;
        $this->dateRange = $dateRange;
    }

    public function headings(): array
    {
        $modifiedHeadings = [];

        foreach ($this->columns as $column) {
            // Capitalize each word and replace underscores with spaces
            $modifiedHeadings[] = ucwords(str_replace('_', ' ', $column));
        }

        return $modifiedHeadings;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
public function collection()
{
    $query = Booking::with(['services', 'packages', 'payment'])
        ->where('status', 'completed')
        ->whereHas('payment', function($q) {
            $q->where('payment_status', 1);
        });

    // Admin branch filter
    if (auth()->user()->hasRole('admin')) {
        $query->whereHas('branch', fn($q) => $q->where('created_by', auth()->id()));
    }

    // Date range filter
    $query->whereDate('bookings.start_date_time', '>=', $this->dateRange[0])
          ->whereDate('bookings.start_date_time', '<=', $this->dateRange[1]);

    // Get bookings and group by start_date_time
    $bookings = $query->get()->groupBy(fn($b) => Carbon::parse($b->start_date_time)->format('Y-m-d'));

    $newQuery = collect();
    foreach ($bookings as $date => $dailyBookings) {
        $newQuery->push((object)[
            'start_date_time'      => $date,
            'total_booking'        => $dailyBookings->count(),
            'total_service'        => $dailyBookings->sum(fn($b) => $b->services->count() + $b->packages->count()),
            'total_service_amount' => $dailyBookings->sum(fn($b) => $b->total_service_amount),
            'total_tax_amount'     => $dailyBookings->sum(fn($b) => $b->total_tax_amount),
            'total_tip_amount'     => $dailyBookings->sum(fn($b) => $b->total_tip_amount),
            'grand_total_amount'   => $dailyBookings->sum(fn($b) => $b->grand_total_amount),
        ]);
    }

    // Map columns for export
    $exportData = $newQuery->map(function ($row) {
    $selectedData = [];

    foreach ($this->columns as $column) {
        switch ($column) {
            case 'date':
                // Use formatDateOrTime to match the table display format
                $selectedData[$column] = formatDateOrTime($row->start_date_time, 'date');
                break;

            case 'total_service_amount':
                $selectedData[$column] = Currency::format($row->total_service_amount);
                break;

            case 'total_tax_amount':
                $selectedData[$column] = Currency::format($row->total_tax_amount);
                break;

            case 'total_tip_amount':
                $selectedData[$column] = Currency::format($row->total_tip_amount);
                break;

            case 'total_amount':
                $selectedData[$column] = Currency::format($row->grand_total_amount);
                break;

            default:
                // Use object property access
                $selectedData[$column] = $row->{$column} ?? null;
                break;
        }
    }

    return $selectedData;
});


    return $exportData;
}


    public function styles(Worksheet $sheet)
    {
        applyExcelStyles($sheet);
    }
}
