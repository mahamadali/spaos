<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class EmployeeExport implements FromCollection, WithHeadings,WithStyles
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
        $query = User::select('users.*')->role(['employee', 'manager'])->branch()->with('media', 'mainBranch')->where('created_by', auth()->id());

        $query->whereDate('users.created_at', '>=', $this->dateRange[0]);

        $query->whereDate('users.created_at', '<=', $this->dateRange[1]);

        $query->orderBy('users.updated_at', 'desc');

        $query = $query->get();

        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'varification_status':
                        $selectedData[$column] = __('customer.msg_unverified');
                        if ($row['email_verified_at']) {
                            $selectedData[$column] = __('customer.msg_verified');
                        }
                        break;

                    case 'is_banned':
                        $selectedData[$column] = __('messages.no');
                        if ($row[$column]) {
                            $selectedData[$column] = __('messages.yes');
                        }
                        break;

                    case 'status':
                        $selectedData[$column] = __('messages.no');
                        if ($row[$column]) {
                            $selectedData[$column] = __('messages.yes');
                        }
                        break;

                    case 'branches':
                        $selectedData[$column] = implode(', ', optional($row->mainBranch)->pluck('name')->toArray()) ?? '-';
                        break;

                    case 'role':
                        $selectedData[$column] = __('messages.lbl_staff');
                        if ($row->is_manager) {
                            $selectedData[$column] = __('messages.lbl_manager');
                        }
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
