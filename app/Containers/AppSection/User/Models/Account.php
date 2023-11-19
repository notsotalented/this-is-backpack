<?php

namespace App\Containers\AppSection\User\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends ParentModel
{
    use CrudTrait;

    protected $table = 'accounts_of_user';
    protected $fillable = [
        'name',
        'password',
        'type',
        'money',
        'belongs_to',
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];

    /**
     * A resource key to be used in the serialized responses.
     */
    protected string $resourceKey = 'Account';

    public function getOwner(): BelongsTo
    {
      return $this->belongsTo(User::class, 'belongs_to', 'id');
    }
}
