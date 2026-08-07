<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function export(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $type = $request->input('type'); // attendance, leaves, overtime
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        if ($type === 'attendance') {
            return $this->exportAttendance($startDate, $endDate);
        } elseif ($type === 'leaves') {
            return $this->exportLeaves($startDate, $endDate);
        } elseif ($type === 'overtime') {
            return $this->exportOvertime($startDate, $endDate);
        }

        return response()->json(['message' => 'Invalid report type'], 400);
    }

    private function exportAttendance($startDate, $endDate)
    {
        $data = Attendance::with('user')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $csvHeader = ['Date', 'NIK', 'Name', 'Time In', 'Time Out', 'Status', 'Work Duration'];
        $csvData = [];
        $csvData[] = implode(',', $csvHeader);

        foreach ($data as $row) {
            $csvData[] = implode(',', [
                $row->date,
                $row->user->nik ?? '-',
                $row->user->name ?? '-',
                $row->time_in,
                $row->time_out ?? '-',
                $row->status,
                $row->work_duration ?? '-'
            ]);
        }

        return Response::make(implode("\n", $csvData), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_report_' . $startDate . '_to_' . $endDate . '.csv"',
        ]);
    }

    private function exportLeaves($startDate, $endDate)
    {
        $data = LeaveRequest::with('user')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();

        $csvHeader = ['Type', 'NIK', 'Name', 'Start Date', 'End Date', 'Status', 'Reason'];
        $csvData = [];
        $csvData[] = implode(',', $csvHeader);

        foreach ($data as $row) {
            $csvData[] = implode(',', [
                $row->type,
                $row->user->nik ?? '-',
                $row->user->name ?? '-',
                $row->start_date,
                $row->end_date,
                $row->status,
                '"' . str_replace('"', '""', $row->reason) . '"'
            ]);
        }

        return Response::make(implode("\n", $csvData), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leaves_report_' . $startDate . '_to_' . $endDate . '.csv"',
        ]);
    }

    private function exportOvertime($startDate, $endDate)
    {
        $data = OvertimeRequest::with('user')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $csvHeader = ['Date', 'NIK', 'Name', 'Start Time', 'End Time', 'Duration', 'Status', 'Reason'];
        $csvData = [];
        $csvData[] = implode(',', $csvHeader);

        foreach ($data as $row) {
            $csvData[] = implode(',', [
                $row->date,
                $row->user->nik ?? '-',
                $row->user->name ?? '-',
                $row->start_time,
                $row->end_time,
                $row->total_duration ?? '-',
                $row->status,
                '"' . str_replace('"', '""', $row->reason) . '"'
            ]);
        }

        return Response::make(implode("\n", $csvData), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="overtime_report_' . $startDate . '_to_' . $endDate . '.csv"',
        ]);
    }
}
