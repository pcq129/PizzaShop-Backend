<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes;


    public function section():BelongsTo{
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function customer():BelongsTo{
        return $this->belongsTo(Customer::class, 'assigned_to', 'id');
    }

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'created_at',
    ];

    protected $fillable = [
        'name',
        'status',
        'capacity',
        'section_id'
    ];
}
