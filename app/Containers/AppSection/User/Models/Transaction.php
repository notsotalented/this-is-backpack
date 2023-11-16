<?php

namespace App\Containers\AppSection\User\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends ParentModel
{
    protected $fillable = [
        'from_account',
        'to_account',
        'type',
        'money',
        'is_completed',
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];

    /**
     * A resource key to be used in the serialized responses.
     */
    protected string $resourceKey = 'Transaction';

    public function getFrom(): BelongsTo
    {
      return $this->belongsTo(User::class, 'from_account', 'id');
    }

    public function getTo(): BelongsTo
    {
      return $this->belongsTo(User::class, 'to_account', 'id');
    }
}
