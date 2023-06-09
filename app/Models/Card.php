<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function board()
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
}
