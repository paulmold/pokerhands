<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hand extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hands';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'round_id',
        'hand1_cards',
        'hand1',
        'hand2_cards',
        'hand2',
        'result',
    ];
}
