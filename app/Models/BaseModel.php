<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class BaseModel extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getTable());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
    return auth()->user()->name . " {$eventName} " . class_basename($this);
    }

    protected function formatEventDescription(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            default => ucfirst($eventName),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Helpers
    |--------------------------------------------------------------------------
    */

    public function getLatestActivity()
    {
        return Activity::where('subject_type', static::class)
            ->where('subject_id', $this->id)
            ->latest()
            ->first();
    }

    public function getCauserName(): ?string
    {
        $activity = $this->getLatestActivity();

        return $activity?->causer?->name;
    }

    public function getActionName(): ?string
    {
        $activity = $this->getLatestActivity();

        return $activity?->description;
    }

    public function getFormattedActivity(): ?string
    {
        $activity = $this->getLatestActivity();

        if (!$activity) {
            return null;
        }

        $causer = $activity->causer?->name ?? 'System';
        $action = $this->formatEventDescription($activity->description);

        return "{$causer} {$action} this record";
    }

    public function getActivityDisplayName(): string
    {
        // Handle specific model types with custom logic
        $className = class_basename($this);

        if ($className === 'User') {
            return $this->name ?? $this->email ?? 'User #' . $this->id;
        }

        if ($className === 'Card') {
            return $this->name ?? 'Card #' . $this->id;
        }

        if ($className === 'UserTeam') {
            $user = $this->user;
            $team = $this->team;
            $userName = $user?->name ?? 'Unknown User';
            $teamName = $team?->name ?? 'Unknown Team';
            $status = $this->status ?? 'Unknown';
            return "{$userName} - {$teamName} ({$status})";
        }

        if ($className === 'CardUser') {
            $user = $this->user;
            $card = $this->card;
            $userName = $user?->name ?? 'Unknown User';
            $cardName = $card?->name ?? 'Card #' . ($this->card_id ?? 'Unknown');
            return "{$userName} Assigned to Task: {$cardName}";
        }

        if ($className === 'CardHistory') {
            $user = $this->user;
            $card = $this->card;
            $userName = $user?->name ?? 'Unknown User';
            $cardName = $card?->name ?? 'Card #' . ($this->card_id ?? 'Unknown');
            $type = $this->type ?? 'Unknown';
            return "{$userName} - {$cardName} ({$type})";
        }

        // Try common name attributes first
        $nameAttributes = ['name', 'title', 'subject', 'email', 'username'];

        foreach ($nameAttributes as $attribute) {
            if (isset($this->attributes[$attribute]) && !empty($this->attributes[$attribute])) {
                return $this->attributes[$attribute];
            }
        }

        // Fallback to model type with ID
        return class_basename($this) . ' #' . $this->id;
    }
}
