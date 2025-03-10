<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\NewsRSS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 9;
        $page = (int) $request->query('page', 1);
        $sourceFilter = $request->query('source', '');
        $sort = $request->query('sort', 'newest');
        $dateRange = $request->query('date_range', 'all');
        $dateFrom = $request->query('date_from', '');
        $dateTo = $request->query('date_to', '');
        $searchQuery = $request->query('search', '');
    
        // ✅ Set Timezone to Asia/Manila
        $timezone = 'Asia/Manila';
        $now = now($timezone);
    
        // ✅ Cache News Sources Separately
        $sources = Cache::remember('news_sources', now()->addMinutes(30), function () {
            return NewsRSS::distinct()->pluck('source');
        });
    
        // ✅ Generate Cache Key Based on Filters
        $cacheKey = "news_page_{$page}_source_" . ($sourceFilter ?: 'all') .
            "_sort_{$sort}_date_{$dateRange}_search_" . ($searchQuery ?: 'none');
    
        // ✅ Fetch Articles with Caching
        $articles = Cache::remember($cacheKey, now()->addMinutes(10), function () use (
            $perPage, $page, $sourceFilter, $sort, $dateRange, $dateFrom, $dateTo, $searchQuery, $now, $timezone
        ) {
            $query = NewsRSS::query();
    
            // ✅ Apply Source Filter
            if (!empty($sourceFilter)) {
                $query->where('source', $sourceFilter);
            }
    
            // ✅ Apply Search Filter (Title & Description)
            if (!empty($searchQuery)) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('title', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            }
    
            // ✅ Apply Date Filters (Convert to Manila Time)
            if ($dateRange === 'today') {
                $query->whereBetween('pubDate', [$now->startOfDay(), $now->endOfDay()]);
            } elseif ($dateRange === 'yesterday') {
                $query->whereBetween('pubDate', [$now->subDay()->startOfDay(), $now->subDay()->endOfDay()]);
            } elseif ($dateRange === 'last7days') {
                $query->whereBetween('pubDate', [$now->subDays(7)->startOfDay(), $now->endOfDay()]);
            } elseif ($dateRange === 'last30days') {
                $query->whereBetween('pubDate', [$now->subDays(30)->startOfDay(), $now->endOfDay()]);
            } elseif (!empty($dateFrom) && !empty($dateTo)) {
                $query->whereBetween('pubDate', [
                    Carbon::parse($dateFrom, $timezone)->startOfDay(),
                    Carbon::parse($dateTo, $timezone)->endOfDay(),
                ]);
            }
    
            // ✅ Apply Sorting
            if ($sort === 'newest') {
                $query->orderByDesc('pubDate');
            } elseif ($sort === 'oldest') {
                $query->orderBy('pubDate');
            }
    
            return $query->paginate($perPage);
        });
    
        // ✅ Return JSON for API Calls (e.g., Axios in Vue)
        if ($request->expectsJson()) {
            return response()->json([
                'articles' => $articles->items(),
                'currentPage' => $articles->currentPage(),
                'totalPages' => $articles->lastPage(),
                'sources' => $sources,
                'selectedSource' => $sourceFilter,
                'selectedSort' => $sort,
                'selectedDateRange' => $dateRange,
                'searchQuery' => $searchQuery,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
        }
    
        // ✅ Return Inertia View for Normal Requests
        return Inertia::render('News/Index', [
            'articles' => $articles->items(),
            'currentPage' => $articles->currentPage(),
            'totalPages' => $articles->lastPage(),
            'sources' => $sources,
            'selectedSource' => $sourceFilter,
            'selectedSort' => $sort,
            'selectedDateRange' => $dateRange,
            'searchQuery' => $searchQuery,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
    
}
