<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Mews\Purifier\Facades\Purifier;

class CommentController extends Controller
{
    public function index($articleId)
    {
        $article = Article::with('comments.user')->findOrFail($articleId);

        return Inertia::render('Status/View', [
            'article' => $article,
            'comments' => $article->comments()->with('user')->latest()->get() ?? [],
            'userRoles' => auth()->user()->getRoleNames(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
    
        // ✅ Log unauthorized attempts
        if (!$user->hasAnyRole(['administrator', 'executive'])) {
            Log::warning("Unauthorized comment attempt by user: {$user->id}");
            return redirect()->back()->withErrors(['error' => 'Unauthorized']);
        }
    
        // ✅ Validate input
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'comment' => 'required|string|max:1000',
        ]);
    
        // ✅ Get article to determine status for dynamic redirect
        $article = Article::findOrFail($request->article_id);
    
        // ✅ XSS Protection
        $sanitizedComment = Purifier::clean(strip_tags($request->comment));
    
        // ✅ Save comment
        Comment::create([
            'article_id' => $article->id,
            'user_id' => $user->id,
            'comment' => $sanitizedComment,
        ]);
    
        // ✅ Redirect dynamically based on the article's status
        return redirect()->route('status.view', [
            'status' => $article->approval_status, // Convert to lowercase for consistency
            'id' => $article->id,
        ]);
    }
    
}
