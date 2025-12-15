<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Card extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'column_id',
        'name',
        'description',
        'previous_id',
        'start_date',
        'end_date',
        'is_done',
        'archive',
        'archived_at',
        'next_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'previous_id',
        'next_id',
        'created_at',
        'updated_at',
    ];

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

    public function board()
    {
        return $this->belongsTo(Column::class);
    }

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function previousCard()
    {
        return $this->belongsTo(Card::class, 'previous_id');
    }

    public function nextCard()
    {
        return $this->belongsTo(Card::class, 'next_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, "card_user", "card_id", "user_id");
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'card_user', 'card_id', 'user_id');
    }
}
