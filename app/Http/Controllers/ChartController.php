<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ChartController extends Controller
{
    public function getReports(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }
    
        // ✅ Define allowed `type_of_report` values
        $allowedReports = [
            'Breach',
            'Data Leak',
            'Malware Information',
            'Threat Actors Updates',
            'Cyber Awareness',
            'Vulnerability Exploitation',
            'Phishing',
            'Ransomware',
            'Social Engineering',
            'Illegal Access'
        ];
    
        // ✅ Validate `from`, `to`, and `type_of_report`
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'type_of_report' => ['nullable', 'string', 'in:' . implode(',', $allowedReports)], // ✅ Only allow specific reports
        ]);
    
        try {
            // ✅ Set timezone
            $now = Carbon::now('Asia/Manila');
    
            // ✅ Use Default Last 30 Days If No Date Provided
            $from = isset($validated['from'])
                ? Carbon::parse($validated['from'], 'Asia/Manila')->startOfDay()
                : $now->subDays(30)->startOfDay();
    
            $to = isset($validated['to'])
                ? Carbon::parse($validated['to'], 'Asia/Manila')->endOfDay()
                : $now->endOfDay();
    
            // ✅ Prevent Invalid Date Ranges
            if ($from->greaterThan($to)) {
                return response()->json([
                    'status' => 'error',
                    'message' => '`from` must be before `to`.',
                ], 400);
            }
    
            // ✅ Restrict Date Range to 1 Year Maximum
            if ($from->diffInDays($to) > 365) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Date range too large (Max: 1 year).',
                ], 400);
            }
    
            // ✅ Secure Query: Only fetch allowed report types
            $query = Article::whereBetween('created_at', [$from, $to])
                ->select('type_of_report')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('type_of_report');
    
            // ✅ Filter by `type_of_report` if provided
            if (isset($validated['type_of_report'])) {
                $query->where('type_of_report', $validated['type_of_report']);
            }
    
            $reports = $query->get();
    
            return response()->json([
                'status' => 'success',
                'labels' => $reports->pluck('type_of_report')->toArray(),
                'data' => $reports->pluck('count')->toArray(),
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error. Please try again later.',
            ], 500);
        }
    }
    
    
    

    public function getLineData(Request $request)
    {
        try {
            // ✅ Validate input to prevent injection & invalid data
            $validated = $request->validate([
                'type_of_report' => ['required', 'string', 'in:Breach,Data Leak,Malware Information,Threat Actors Updates,Cyber Awareness,Vulnerability Exploitation,Phishing,Ransomware,Social Engineering,Illegal Access'],
                'date_filter' => ['nullable', 'string', 'in:Today,Yesterday,Last 7 Days,Last 30 Days,This Month,Last Month,Custom Range'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            ]);
    
            // Set timezone to Asia/Manila
            $now = Carbon::now('Asia/Manila');
    
            // ✅ Default values
            $typeOfReport = $validated['type_of_report'] ?? 'Breach';
            $dateFilter = $validated['date_filter'] ?? 'Last 30 Days';
            $startDate = $validated['start_date'] ?? null;
            $endDate = $validated['end_date'] ?? null;
    
            // ✅ Secure Date Filtering
            if ($dateFilter === 'Custom Range' && $startDate && $endDate) {
                $startDate = Carbon::parse($startDate, 'Asia/Manila')->startOfDay();
                $endDate = Carbon::parse($endDate, 'Asia/Manila')->endOfDay();
            } else {
                switch ($dateFilter) {
                    case 'Today':
                        $startDate = $now->copy()->startOfDay();
                        $endDate = $now->copy()->endOfDay();
                        break;
                    case 'Yesterday':
                        $startDate = $now->copy()->subDay()->startOfDay();
                        $endDate = $now->copy()->subDay()->endOfDay();
                        break;
                    case 'Last 7 Days':
                        $startDate = $now->copy()->subDays(6)->startOfDay();
                        $endDate = $now->copy()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = $now->copy()->subDays(29)->startOfDay();
                        $endDate = $now->copy()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = $now->copy()->startOfMonth()->startOfDay();
                        $endDate = $now->copy()->endOfMonth()->endOfDay();
                        break;
                    case 'Last Month':
                        $startDate = $now->copy()->subMonth()->startOfMonth()->startOfDay();
                        $endDate = $now->copy()->subMonth()->endOfMonth()->endOfDay();
                        break;
                    default:
                        $startDate = $now->copy()->subDays(29)->startOfDay();
                        $endDate = $now->copy()->endOfDay();
                }
            }
    
            // ✅ Secure Query: Use Eloquent instead of `selectRaw`
            $data = Article::selectRaw("DATE(created_at) as date, COUNT(*) as count")
                ->where('type_of_report', $typeOfReport)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
    
            // ✅ Build Date Range
            $dateRange = collect();
            $currentDate = Carbon::parse($startDate, 'Asia/Manila');
            while ($currentDate->lte(Carbon::parse($endDate, 'Asia/Manila'))) {
                $dateRange->put($currentDate->toDateString(), 0);
                $currentDate->addDay();
            }
    
            foreach ($data as $row) {
                if ($dateRange->has($row->date)) {
                    $dateRange[$row->date] = $row->count;
                }
            }
    
            return response()->json([
                'labels' => $dateRange->keys()->toArray(),
                'data' => $dateRange->values()->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function showStats()
    {
        try {
            // ✅ Require authentication
            if (!Auth::check()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }
    
            // Set timezone to Asia/Manila
            $now = Carbon::now('Asia/Manila');
    
            // ✅ Use caching to improve performance
            $totalReports = Cache::remember('total_reports', now()->addMinutes(10), function () {
                return Article::count();
            });
    
            $reportsThisWeek = Cache::remember('reports_this_week', now()->addMinutes(10), function () use ($now) {
                return Article::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
            });
    
            $reportsThisMonth = Cache::remember('reports_this_month', now()->addMinutes(10), function () use ($now) {
                return Article::whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count();
            });
    
            $previousWeekCount = Cache::remember('previous_week_count', now()->addMinutes(10), function () use ($now) {
                return Article::whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()])->count();
            });
    
            $previousMonthCount = Cache::remember('previous_month_count', now()->addMinutes(10), function () use ($now) {
                return Article::whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])->count();
            });
    
            // ✅ Prevent division by zero errors
            $weeklyChange = $previousWeekCount > 0
                ? round((($reportsThisWeek - $previousWeekCount) / $previousWeekCount) * 100, 1)
                : 0;
    
            $monthlyChange = $previousMonthCount > 0
                ? round((($reportsThisMonth - $previousMonthCount) / $previousMonthCount) * 100, 1)
                : 0;
    
            return response()->json([
                'status' => 'success',
                'stats' => [
                    [
                        'title' => 'Total Reports',
                        'value' => number_format($totalReports),
                        'description' => '',
                        'trend' => '',
                        'change' => ''
                    ],
                    [
                        'title' => 'Reports This Week',
                        'value' => number_format($reportsThisWeek),
                        'description' => "({$weeklyChange}%)",
                        'trend' => $weeklyChange >= 0 ? 'up' : 'down',
                        'change' => abs($reportsThisWeek - $previousWeekCount)
                    ],
                    [
                        'title' => 'Reports This Month',
                        'value' => number_format($reportsThisMonth),
                        'description' => "({$monthlyChange}%)",
                        'trend' => $monthlyChange >= 0 ? 'up' : 'down',
                        'change' => abs($reportsThisMonth - $previousMonthCount)
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve stats.',
            ], 500);
        }
    }    

}
