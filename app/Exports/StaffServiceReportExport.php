<?php

namespace App\Exports;

use App\Models\User;
use Currency;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class StaffServiceReportExport implements FromCollection, WithHeadings,WithStyles
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
        $query = User::staffReportWithCommissionStatus();

        if(auth()->user()->hasRole('admin')){
            $query = $query->whereHas('mainBranch', function($q){
                $q->where('created_by',auth()->id());
            });
        }

        $query = $query->get();

        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'employee':
                        $selectedData[$column] = $row->full_name ?? '-';
                        break;

                    case 'total_services':
                        $totalServices = ($row->employee_booking_count ?? 0) + ($row->booking_packages_count ?? 0);
                        $selectedData[$column] = $totalServices > 0 ? $totalServices : '0';
                        break;

                    case 'total_service_amount':
                        $serviceSum = $row->employee_booking_sum_service_price ?? 0;
                        $packageSum = $row->booking_packages_sum_package_price ?? 0;
                        $totalAmount = $serviceSum + $packageSum;
                        $selectedData[$column] = Currency::format($totalAmount);
                        break;

                    case 'total_commission_earn':
                        $selectedData[$column] = Currency::format($row->commission_earning_sum_commission_amount ?? 0);
                        break;

                    case 'total_tip_earn':
                        $selectedData[$column] = Currency::format($row->tip_earning_sum_tip_amount ?? 0);
                        break;

                    case 'total_earning':
                         $selectedData[$column] = Currency::format( $row->commission_earning_sum_commission_amount + $row->tip_earning_sum_tip_amount);
                        break;

                    default:
                        $selectedData[$column] = $row[$column];
                        break;
                }
            }

            return $selectedData;
        });

        return $newQuery;
    }
    public function styles(Worksheet $sheet)
    {
        applyExcelStyles($sheet);
    }
}
