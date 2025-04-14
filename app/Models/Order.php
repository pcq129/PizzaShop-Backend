<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory,SoftDeletes;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

  /**
   * The item that belong to the Order
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function item(): BelongsToMany
  {
      return $this->belongsToMany(Item::class, 'item_order_mapper', 'order_id', 'item_id');
  }

}
