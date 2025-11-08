<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Maatwebsite\Excel\Facades\Excel;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'status' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'status' => false,
            'message' => $error,
        ];

        if (! empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    protected string $exportClass = '';

    public function export(Request $request)
    {
        // Handle columns - they come as array from checkboxes
        $columns = is_array($request->columns) ? $request->columns : explode(',', $request->columns);
        $type = $request->file_type;
        // Handle date range - it may come as "YYYY-MM-DD - YYYY-MM-DD" or "YYYY-MM-DD to YYYY-MM-DD"
        $dateRange = [];
        if (!empty($request->date_range)) {
            $dateRange = strpos($request->date_range, ' to ') !== false
                ? explode(' to ', $request->date_range)
                : explode(' - ', $request->date_range);
            if (count($dateRange) == 1) {
                $dateRange[1] = $dateRange[0];
            }
        }

        if (! empty($this->exportClass)) {
            switch ($type) {
                case 'csv':
                    return Excel::download(new $this->exportClass($columns, $dateRange), 'file.csv', \Maatwebsite\Excel\Excel::CSV);
                    break;
                case 'xlsx':
                    return Excel::download(new $this->exportClass($columns, $dateRange), 'file.xlsx', \Maatwebsite\Excel\Excel::XLSX);
                    break;
                case 'xls':
                    return Excel::download(new $this->exportClass($columns, $dateRange), 'file.xls', \Maatwebsite\Excel\Excel::XLS);
                    break;
                case 'ods':
                    return Excel::download(new $this->exportClass($columns, $dateRange), 'file.ods', \Maatwebsite\Excel\Excel::ODS);
                    break;
                case 'html':
                    return Excel::download(new $this->exportClass($columns, $dateRange), 'file.html', \Maatwebsite\Excel\Excel::HTML);
                    break;
                case 'pdf':
                    return Excel::download(new $this->exportClass($columns, $dateRange), 'file.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
                    break;
            }
        }

        return abort(500);
    }
}
