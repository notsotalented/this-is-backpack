<?php

namespace App\Containers\AppSection\User\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends ParentModel
{
    use CrudTrait;

    protected $table = 'transactions_of_accounts';
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
      return $this->belongsTo(Account::class, 'from_account', 'id');
    }

    public function getTo(): BelongsTo
    {
      return $this->belongsTo(Account::class, 'to_account', 'id');
    }
}
