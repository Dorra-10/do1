<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\User;
use Carbon\Carbon;

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
                    COALESCE(last_viewed_at, 0),
                    COALESCE(last_modified_at, 0)
                ) as latest_date
            ')
            ->orderByDesc('latest_date');

        if (!$user->hasRole(['admin', 'superviseur'])) {
            $query->whereHas('document.accesses', fn($q) => $q->where('user_id', $user->id));
        }

        $histories = $query->get()
            ->each(function ($history) {
                $history->formatted_viewed = $history->last_viewed_at 
                    ? Carbon::parse($history->last_viewed_at)->format('Y-m-d H:i:s')
                    : 'Jamais consulté';
                    
                $history->formatted_modified = $history->last_modified_at
                    ? Carbon::parse($history->last_modified_at)->format('Y-m-d H:i:s')
                    : 'Jamais modifié';
            });

        return view('history.index', [
            'histories' => $histories,
            'users' => $this->getUsersForHistories($histories)
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