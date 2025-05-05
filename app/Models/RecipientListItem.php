<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipientListItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recipient_list_id',
        'name',
        'email',
        'phone',
        'metadata',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'json',
        'is_active' => 'boolean',
    ];

    /**
     * Get the recipient list that this item belongs to.
     */
    public function recipientList()
    {
        return $this->belongsTo(RecipientList::class);
    }
}
