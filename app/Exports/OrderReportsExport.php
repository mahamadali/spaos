<?php

namespace App\Exports;

use Currency;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Product\Models\Order;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class  OrderReportsExport implements FromCollection, WithHeadings,WithStyles
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
        $query = Order::with('orderGroup','user');

        // Filter by current vendor's orders only
        if (auth()->user()->hasRole('admin')) {
            $query = $query->whereHas('orderItems', function ($q) {
                $q->whereHas('product_variation', function ($qry) {
                    $qry->whereHas('product', function ($query) {
                        $query->where('created_by', auth()->user()->id);
                    });
                });
            });
        }

        $query->whereDate('orders.created_at', '>=', $this->dateRange[0]);

        $query->whereDate('orders.created_at', '<=', $this->dateRange[1]);

        $query = $query->get();

        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'order_code':
                        $selectedData[$column] =  setting('inv_prefix').$row->orderGroup->order_code;
                        break;

                    case 'customer_name':
                        $selectedData[$column] = $row->user->full_name;
                        break;

                    case 'placed_on':
                        $selectedData[$column] = customDate($row->created_at);
                        break;

                    case 'items':
                        $selectedData[$column] =$row->orderItems()->count();
                        break;

                    case 'payment':
                        $selectedData[$column] = ucfirst($row->payment_status);
                        break;

                    case 'status':
                        $selectedData[$column] = ucwords(str_replace('_', ' ', $row->delivery_status));
                        break;

                    case 'total_admin_earnings':
                        $selectedData[$column] = Currency::format($row->total_admin_earnings);;
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
