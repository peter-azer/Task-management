<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Board extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'team_id',
        'pattern',
        'image_path',
        'archive',
        'archived_at',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function columns()
    {
        return $this->hasMany(Column::class);
    }

    public function cards()
    {
        return $this->hasManyThrough(Card::class, Column::class);
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at')->where('archive', false);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at')->orWhere('archive', true);
    }
}
