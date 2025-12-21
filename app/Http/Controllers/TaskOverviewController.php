<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskOverviewController extends Controller
{
    public function delayedIndex()
    {
        $user = Auth::user();
        $now = now();
        $inSixDays = now()->addDays(6)->endOfDay();

        $cardsQuery = Card::active()
            ->with(['column.board.team', 'members'])
            ->where(function ($q) use ($now) {
                // consider all cards; classification happens in PHP, but we still avoid archived
                $q->whereNull('end_date')->orWhereNotNull('end_date');
            });

        if (!$user->hasRole('admin') && !$user->hasRole('super-admin')) {
            $cardsQuery->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $cards = $cardsQuery->get();

        // Group by Team and classify
        $grouped = [];
        foreach ($cards as $card) {
            $team = optional(optional($card->column)->board)->team;
            if (!$team) {
                continue;
            }
            $teamId = $team->id;
            if (!isset($grouped[$teamId])) {
                $grouped[$teamId] = [
                    'team' => $team,
                    'delayed' => [],
                    'near_deadline' => [],
                    'normal' => [],
                ];
            }

            $statusBucket = 'normal';
            if (!$card->is_done && $card->end_date) {
                $end = Carbon::parse($card->end_date);
                if ($end->lt($now)) {
                    $statusBucket = 'delayed';
                } elseif ($end->between($now->copy()->startOfDay(), $inSixDays)) {
                    $statusBucket = 'near_deadline';
                }
            }

            $grouped[$teamId][$statusBucket][] = $card;
        }

        // Calculate total delayed for badge
        $delayedCount = collect($grouped)->sum(function ($g) {
            return count($g['delayed'] ?? []);
        });

        return view('tasks.delayed', [
            'groups' => $grouped,
            'delayedCount' => $delayedCount,
        ]);
    }

    public function delayedCount()
    {
        $user = Auth::user();
        $now = now();

        $cardsQuery = Card::active();
        if (!$user->hasRole('admin') && !$user->hasRole('super-admin')) {
            $cardsQuery->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $count = $cardsQuery
            ->whereNotNull('end_date')
            ->where('is_done', false)
            ->where('end_date', '<', $now)
            ->count();

        return response()->json(['delayed' => $count]);
    }
}
