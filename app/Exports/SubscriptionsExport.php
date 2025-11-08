<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Carbon\Carbon;
use PDF;

class SubscriptionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    protected $subscriptions;

    public function __construct($subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    public function collection()
    {
        return $this->subscriptions;
    }

    public function headings(): array
    {
        return [
            'Vendor Name',
            'Plan',
            'Payment Method',
            'Amount',
            'Duration',
            'Start Date',
            'End Date',
            'Status'
        ];
    }

    public function map($subscription): array
    {
        return [
            $subscription->user ? $subscription->user->first_name . ' ' . $subscription->user->last_name : 'Deleted User',
            json_decode($subscription->plan_details, true)['name'] ?? '-',
            ucfirst($subscription->gateway_type) ?? '-',
            $subscription->total_amount,
            $this->formatDuration($subscription->plan_details),
            Carbon::parse($subscription->start_date)->format('d/m/Y'),
            Carbon::parse($subscription->end_date)->format('d/m/Y'),
            ucfirst($subscription->status)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row
            'D' => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE]], // Amount column
            'F' => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_DATE_DDMMYYYY]], // Start date
            'G' => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_DATE_DDMMYYYY]], // End date
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    private function formatDuration($planDetails)
    {
        $details = json_decode($planDetails, true);
        $type = $details['type'] ?? '';
        $duration = $details['duration'] ?? 0;
        
        $suffix = match($type) {
            'Monthly' => 'Month',
            'Yearly' => 'Year',
            'Weekly' => 'Week',
            default => 'Day'
        };
        
        return $duration . ' ' . $suffix;
    }

    public function downloadPDF($fileName)
    {
        $data = $this->collection();
        
        $pdf = PDF::loadView('subscriptions::backend.subscriptions.export_pdf', [
            'subscriptions' => $data,
            'title' => 'Subscriptions Report'
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($fileName . '.pdf');
    }
}