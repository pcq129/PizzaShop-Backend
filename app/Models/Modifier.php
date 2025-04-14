<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ModifierGroup;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modifier extends Model
{
    use HasFactory, SoftDeletes;

    public function ModifierGroups():BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'modifier_modifier_group','modifier_id', 'modifier_group_id');
    }

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'created_at',
    ];

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'unit',
        'rate'
    ];

}
