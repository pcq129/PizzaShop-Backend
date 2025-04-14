<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemModifierGroup extends Model
{
    use HasFactory;

    public $incrementing = true;
    public $table = "item_modifier_group_mapping";
}
