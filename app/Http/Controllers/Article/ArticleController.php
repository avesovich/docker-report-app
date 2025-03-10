<?php

namespace App\Http\Controllers\Article;

use Inertia\Inertia;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ArticleController extends Controller
{

    public function index(Request $request, $status)
    {
        $validStatuses = ['Review', 'Updated', 'Revision', 'Evaluated', 'Approved'];
    
        // ✅ Validate Status
        if (!in_array($status, $validStatuses)) {
            abort(404, 'Invalid status.');
        }
    
        $user = auth()->user();
        $page = (int) $request->query('page', 1);
        $searchQuery = $request->query('search', '');
    
        // ✅ Generate Cache Key Based on Filters
        $cacheKey = "articles_{$status}_page_{$page}_user_{$user->id}_search_" . ($searchQuery ?: 'none');
    
        // ✅ Fetch Articles with Caching
        $articles = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($status, $user, $searchQuery) {
            $query = Article::where('approval_status', $status)
                ->orderBy(($status === 'Review') ? 'created_at' : 'updated_at', 'desc');
    
            // ✅ Apply Role-Based Filter for Editors
            if ($user->hasRole('editor')) {
                $query->where('user_id', $user->id);
            }
    
            // ✅ Apply Search Filter (Title & Description)
            if (!empty($searchQuery)) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('title', 'like', "%{$searchQuery}%")
                      ->orWhere('type_of_report', 'like', "%{$searchQuery}%");
                });
            }
    
            return $query->paginate(10);
        });
    
        // ✅ Return JSON for Axios Requests
        if ($request->expectsJson()) {
            return response()->json([
                'articles' => $articles->items(),
                'currentPage' => $articles->currentPage(),
                'totalPages' => $articles->lastPage(),
                'searchQuery' => $searchQuery,
                'userRoles' => $user->roles->pluck('name')->toArray(),
            ]);
        }
    
        // ✅ Return Inertia View for Normal Requests
        return Inertia::render("Status/{$status}", [
            'articles' => $articles->items(),
            'currentPage' => $articles->currentPage(),
            'totalPages' => $articles->lastPage(),
            'searchQuery' => $searchQuery,
            'userRoles' => $user->roles->pluck('name')->toArray(),
        ]);
    }
    
    
    public function show(Request $request, $status, $id)
    {
        $validStatuses = ['Review', 'Updated', 'Revision', 'Evaluated', 'Approved'];
    
        if (!in_array($status, $validStatuses)) {
            abort(404, 'Invalid status.');
        }
    
        $article = Article::findOrFail($id);
        $user = auth()->user();
    
        if (strtolower($article->approval_status) !== strtolower($status)) {
            abort(404, 'Article not found or does not match the requested status.');
        }
    
        if (!$user->hasRole(['editor', 'executive', 'administrator']) && $article->user_id !== $user->id) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized']);
        }
    
        $userRoles = $user->roles->pluck('name')->toArray();
        $page = $request->query('page', 1);
    
        $commentsQuery = $article->comments()->with('user')->latest();
        if ($user->hasRole('executive')) {
            $commentsQuery->where('user_id', $user->id);
        }
        $comments = $commentsQuery->paginate(5);

        $imagePaths = json_decode($article->image_path, true) ?? [];

        return Inertia::render("Status/View", [
            'article' => $article,
            'comments' => [
                'data' => $comments->items(),
                'currentPage' => $comments->currentPage(),
                'totalPages' => $comments->lastPage(),
                'totalItems' => $comments->total(),
                'pageSize' => $comments->perPage(),
            ],
            'userRoles' => $userRoles,
            'imagePaths' => $imagePaths,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'publication_date' => 'required|date',
            'type_of_report' => 'required|string|in:Breach,Data Leak,Malware Information,Threat Actors Updates,Cyber Awareness,Vulnerability Exploitation,Phishing,Ransomware,Social Engineering,Illegal Access',
            'url' => 'required|url|max:2083',
            'detailed_summary' => 'required|string',
            'analysis' => 'required|string',
            'recommendation' => 'required|string',
            'image_path' => 'nullable|array',
        ]);
    
        $validatedData['title'] = Purifier::clean($validatedData['title'], 'default');
        $validatedData['detailed_summary'] = Purifier::clean($validatedData['detailed_summary'], 'default');
        $validatedData['analysis'] = Purifier::clean($validatedData['analysis'], 'default');
        $validatedData['recommendation'] = Purifier::clean($validatedData['recommendation'], 'default');
    
        $user = auth()->user();
    
        $article = new Article([
            'title' => $validatedData['title'],
            'publication_date' => $validatedData['publication_date'],
            'type_of_report' => $validatedData['type_of_report'],
            'url' => $validatedData['url'],
            'detailed_summary' => $validatedData['detailed_summary'],
            'analysis' => $validatedData['analysis'],
            'recommendation' => $validatedData['recommendation'],
            'approval_status' => Article::STATUS_REVIEW,
            'image_path' => isset($validatedData['image_path']) ? json_encode($validatedData['image_path']) : null,
        ]);
    
        $article->user_id = $user->id;
        $article->editor_name = $user->name;
        $article->posted_date = now()->toDateString();
        $article->time_posted = now()->toTimeString();
        $article->save();
    
        $status = Article::STATUS_REVIEW;
        $page = 1;
        foreach (range(1, 5) as $page) {
            Cache::forget("articles_{$status}_page_{$page}_user_{$user->id}_search_none");
        }
    
        Cache::forget('total_reports');
        Cache::forget('reports_this_week');
        Cache::forget('reports_this_month');
    
        return redirect()->route('status.index', ['status' => 'Review'])->with([
            'successMessage' => 'Article successfully submitted.',
        ]);
    }
    
     
    public function setApprovalStatus(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $user = auth()->user();
    
        // ✅ Restrict to Administrators and Executives
        if (!$user->hasAnyRole(['administrator', 'executive'])) {
            abort(403, 'Unauthorized');
        }
    
        // ✅ Validate status input
        $validatedData = $request->validate([
            'status' => 'required|string|in:approved,disapproved',
        ]);
    
        // ✅ Define allowed status transitions
        $allowedStatuses = [
            'approved' => [
                'administrator' => Article::STATUS_EVALUATED,
                'executive' => Article::STATUS_APPROVED,
            ],
            'disapproved' => [
                'administrator' => Article::STATUS_REVISION,
                'executive' => Article::STATUS_EVALUATED,
            ],
        ];
    
        // ✅ Determine the new status
        $role = $user->hasRole('executive') ? 'executive' : 'administrator';
        $newStatus = $allowedStatuses[$validatedData['status']][$role] ?? null;
    
        if (!$newStatus) {
            abort(400, 'Invalid approval status.');
        }
    
        // ✅ Get the current status before updating
        $oldStatus = $article->approval_status;
    
        // ✅ Update the article's status
        $article->update(['approval_status' => $newStatus]);
    
        // ✅ Completely clear all related caches
        $this->clearAllArticleCaches();
    
        // ✅ Redirect with a success message
        return redirect()->route('status.index', ['status' => $newStatus])->with([
            'successMessage' => match ($newStatus) {
                Article::STATUS_APPROVED => 'Article successfully approved.',
                Article::STATUS_EVALUATED => 'Article successfully evaluated.',
                Article::STATUS_REVISION => 'Article sent for revision.',
            },
        ]);
    }
    
    /**
     * ✅ Forcefully Clear All Cached Articles to Prevent Stale Data
     */
    private function clearAllArticleCaches()
    {
        Cache::flush(); // Completely clear all cache keys
    }
    
    
    public function update(Request $request, $id)
    {
        $user = auth()->user();
    
        $article = Article::where('id', $id)
            ->where('user_id', $user->id)
            ->where('approval_status', 'Revision')
            ->first();
    
        if (!$article) {
            abort(403, 'Unauthorized or invalid article status.');
        }
    
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'type_of_report' => 'required|string|in:Breach,Data Leak,Malware Information,Threat Actors Updates,Cyber Awareness,Vulnerability Exploitation,Phishing,Ransomware,Social Engineering,Illegal Access',
            'publication_date' => 'required|date',
            'url' => 'required|url|max:2083',
            'detailed_summary' => 'required|string',
            'analysis' => 'required|string',
            'recommendation' => 'required|string',
        ]);
    
        $validatedData['title'] = Purifier::clean($validatedData['title'], 'default');
        $validatedData['detailed_summary'] = Purifier::clean($validatedData['detailed_summary'], 'default');
        $validatedData['analysis'] = Purifier::clean($validatedData['analysis'], 'default');
        $validatedData['recommendation'] = Purifier::clean($validatedData['recommendation'], 'default');
    
        $article->update([
            'title' => $validatedData['title'],
            'type_of_report' => $validatedData['type_of_report'],
            'publication_date' => $validatedData['publication_date'],
            'url' => $validatedData['url'],
            'detailed_summary' => $validatedData['detailed_summary'],
            'analysis' => $validatedData['analysis'],
            'recommendation' => $validatedData['recommendation'],
            'approval_status' => 'Updated',
            'updated_at' => now(),
        ]);
    
        // ✅ Completely flush all cache
        $this->clearAllArticleCaches();
    
        return redirect()->route('status.index', ['status' => 'Updated'])->with([
            'successMessage' => 'Article successfully updated.',
        ]);
    }
    
    
    public function edit($id)
    {
        $user = auth()->user();
    
        if (!$user->hasRole('editor')) {
            abort(403, 'Unauthorized: Only editors can edit articles.');
        }
    
        $article = Article::where('id', $id)
            ->where('user_id', $user->id)
            ->where('approval_status', 'Revision')
            ->first();
    
        if (!$article) {
            abort(403, 'Unauthorized');
        }
    
        // ✅ Fetch related comments
        $comments = $article->comments()->with('user')->latest()->paginate(5);
    
        return Inertia::render('Forms/UpdateReport', [
            'article' => $article,
            'comments' => [
                'data' => $comments->items() ?? [],
                'currentPage' => $comments->currentPage(),
                'totalPages' => $comments->lastPage(),
                'totalItems' => $comments->total(),
                'pageSize' => $comments->perPage(),
            ],
            'typeOfReportOptions' => [
                'Breach',
                'Data Leak',
                'Malware Information',
                'Threat Actors Updates',
                'Cyber Awareness',
                'Vulnerability Exploitation',
                'Phishing',
                'Ransomware',
                'Social Engineering',
                'Illegal Access',
            ],
        ]);
    }

    public function uploadImages(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole('editor')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
    
        $paths = [];
        foreach ($request->file('images') as $file) {
            $hashedName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('articles', $hashedName);
            $paths[] = $filePath;
        }
    
        return response()->json(['paths' => $paths]);
    }
    

    public function getImage($filename)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['administrator', 'editor', 'executive'])) {
            abort(403, 'Unauthorized');
        }

        $filename = basename($filename);
        $path = storage_path('app/private/articles/' . $filename);

        if (!File::exists($path)) {
            abort(404, 'File not found.');
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = File::mimeType($path);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            abort(403, 'Invalid file type.');
        }

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private static function escapeCsvValue($value)
    {
        if (strpos($value, '=') === 0 || strpos($value, '+') === 0 || strpos($value, '-') === 0 || strpos($value, '@') === 0) {
            return "'" . $value;
        }
        return $value;
    }

    public function exportCsv($status)
    {
        $validStatuses = ['Review', 'Updated', 'Revision', 'Evaluated', 'Approved'];
    
        if (!in_array($status, $validStatuses)) {
            abort(404, 'Invalid status.');
        }
    
        $user = auth()->user();
    
        if (!$user->hasAnyRole(['administrator', 'executive', 'editor'])) {
            abort(403, 'Unauthorized');
        }
    
        $articlesQuery = Article::where('approval_status', $status);
    
        if ($user->hasRole('editor')) {
            $articlesQuery->where('user_id', $user->id);
        }
    
        $articles = $articlesQuery->get();
    
        $filename = 'articles_' . strtolower($status) . '.csv';
    
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
    
        $callback = function () use ($articles) {
            $file = fopen('php://output', 'w');
    
            fputcsv($file, ['ID', 'Title', 'Publication Date', 'Type of Report', 'URL', 'Editor Name', 'Detailed Summary', 
            'Analysis', 'Recommendation', 'Approval Status', 'Created At', 'Updated At']);
    
            foreach ($articles as $article) {
                fputcsv($file, [
                    $article->id,
                    self::escapeCsvValue($article->title),
                    $article->publication_date,
                    self::escapeCsvValue($article->type_of_report),
                    self::escapeCsvValue($article->url),
                    self::escapeCsvValue($article->editor_name),
                    self::escapeCsvValue($article->detailed_summary),
                    self::escapeCsvValue($article->analysis),
                    self::escapeCsvValue($article->recommendation),
                    $article->approval_status,
                    $article->created_at,
                    $article->updated_at,
                ]);
            }
    
            fclose($file);
        };
    
        return new StreamedResponse($callback, 200, $headers);
    }
    
}