<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    public function index()
{
    $user = auth()->user();
    
    $query = History::with([
            'document:id,name',
            'modifier:id,name',
            'viewer:id,name'
        ])
        ->select('histories.*')
        ->selectRaw('
            GREATEST(
                COALESCE(UNIX_TIMESTAMP(last_viewed_at), 0),
                COALESCE(UNIX_TIMESTAMP(last_modified_at), 0)
            ) as latest_date
        ');
    
    if (!$user->hasRole(['admin', 'supervisor'])) {
        $query->whereHas('document.accesses', fn($q) => $q->where('user_id', $user->id));
    }
    
    // Pagination avec 8 éléments par page
    $histories = $query->orderByDesc('latest_date')->paginate(8);
    
    // Transformation des données
    $histories->getCollection()->transform(function ($history) {
        $history->formatted_viewed = $history->last_viewed_at 
            ? Carbon::parse($history->last_viewed_at)->format('Y-m-d H:i:s')
            : 'Never Consulted';
        
        $history->formatted_modified = $history->last_modified_at
            ? Carbon::parse($history->last_modified_at)->format('Y-m-d H:i:s')
            : 'Never modified';
        
        $latestTimestamp = max(
            optional($history->last_viewed_at)?->getTimestamp() ?? 0,
            optional($history->last_modified_at)?->getTimestamp() ?? 0
        );
        
        $history->formatted_latest = $latestTimestamp
            ? Carbon::createFromTimestamp($latestTimestamp)->toIso8601String()
            : '';
        
        return $history;
    });
    
    return view('history.index', [
        'histories' => $histories,
        'users' => $this->getUsersForHistories($histories->getCollection())
    ]);
}
    
    protected function getUsersForHistories($histories)
    {
        $userIds = $histories->pluck('last_viewed_by')
            ->merge($histories->pluck('last_modified_by'))
            ->filter()
            ->unique();
    
        return User::whereIn('id', $userIds)->get()->keyBy('id');
    }

}