<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class KOT extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the order that owns the KOT
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        if (!auth()->user()->can('view_kot')) {
            abort(403, 'Unauthorized action.');
        }
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get the ItemCategory that owns the KOT
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ItemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category', 'id');
    }


}
