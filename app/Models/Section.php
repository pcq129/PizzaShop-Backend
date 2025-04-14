<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * Get all of the tables for the Section
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, 'section_id', 'id');
    }

    /**
     * Get all of the customers for the Section
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'section_id', 'id');
    }

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'created_at',
    ];

    protected $fillable = [
        'name',
        'description',
    ];


}
