<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Branch;
use Modules\Constant\Models\Constant;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BranchExport implements FromCollection, WithHeadings, WithStyles
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
        $query = Branch::query()->with(['employee', 'address.city_data', 'address.state_data', 'address.country_data', 'services']);
        
        // Apply date range filter only if date range is provided and valid
        if (!empty($this->dateRange) && count($this->dateRange) == 2 && 
            !empty($this->dateRange[0]) && !empty($this->dateRange[1])) {
            $query->whereDate('created_at', '>=', $this->dateRange[0]);
            $query->whereDate('created_at', '<=', $this->dateRange[1]);
        }

        $query = $query->get();

        // Debug: Log the query results
        \Log::info('Branch Export Query Results:', [
            'count' => $query->count(),
            'dateRange' => $this->dateRange,
            'columns' => $this->columns
        ]);

        // If no data found, return empty collection
        if ($query->isEmpty()) {
            return collect([]);
        }

        $branch_status = Constant::getAllConstant()->where('type', 'BRANCH_STATUS');

        $newQuery = $query->map(function ($row) use ($branch_status) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'id':
                        $selectedData[$column] = $row->id;
                        break;

                    case 'name':
                        $selectedData[$column] = $row->name ?? '-';
                        break;

                    case 'contact_number':
                        $selectedData[$column] = $row->contact_number ?? '-';
                        break;

                    case 'contact_email':
                        $selectedData[$column] = $row->contact_email ?? '-';
                        break;

                    case 'manager_name':
                        $selectedData[$column] = $row->employee?->full_name ?? '-';
                        break;

                    case 'manager_email':
                        $selectedData[$column] = $row->employee?->email ?? '-';
                        break;

                    case 'city':
                        $selectedData[$column] = $row->address?->city_data?->name ?? '-';
                        break;

                    case 'state':
                        $selectedData[$column] = $row->address?->state_data?->name ?? '-';
                        break;

                    case 'country':
                        $selectedData[$column] = $row->address?->country_data?->name ?? '-';
                        break;

                    case 'postal_code':
                        $selectedData[$column] = $row->address?->postal_code ?? '-';
                        break;

                    case 'address_line_1':
                        $selectedData[$column] = $row->address_line_1 ?? '-';
                        break;

                    case 'address_line_2':
                        $selectedData[$column] = $row->address_line_2 ?? '-';
                        break;

                    case 'latitude':
                        $selectedData[$column] = $row->latitude ?? '-';
                        break;

                    case 'longitude':
                        $selectedData[$column] = $row->longitude ?? '-';
                        break;

                    case 'branch_for':
                        $selectedData[$column] = ucfirst($row->branch_for ?? '-');
                        break;

                    case 'payment_method':
                        $selectedData[$column] = is_array($row->payment_method) ? implode(', ', $row->payment_method) : ($row->payment_method ?? '-');
                        break;

                    case 'services':
                        $selectedData[$column] = $row->services ? implode(', ', $row->services->pluck('name')->toArray()) : '-';
                        break;

                    case 'status':
                        $selectedData[$column] = $row->status ? 'Active' : 'Inactive';
                        break;

                    case 'description':
                        $selectedData[$column] = $row->description ?? '-';
                        break;

                    case 'created_at':
                        $selectedData[$column] = $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-';
                        break;

                    case 'updated_at':
                        $selectedData[$column] = $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : '-';
                        break;

                    default:
                        $selectedData[$column] = '-';
                        break;
                }
            }

            return $selectedData;
        });

        return $newQuery;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}

