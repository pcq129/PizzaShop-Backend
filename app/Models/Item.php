<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\ItemCategory;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    public function Category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id', 'id');
    }

    public function ModifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'item_modifier_groups_mapping', 'item_id', 'modifier_group_id');
    }

    /**
     * The order that belong to the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function order(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'item_order_mapper', 'item_id', 'order_id');
    }


    protected $hidden = [
        'deleted_at',
        'updated_at',
        'created_at',
    ];

}
