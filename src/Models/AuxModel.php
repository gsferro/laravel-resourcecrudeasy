<?php

namespace Gsferro\ResourceCrudEasy\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;

abstract class AuxModel extends BaseModel
{
    use Cachable;

    protected $primaryKey = "id";
    public    $timestamps = false;

    public $fillable = [
        'name'
    ];

    /*
    |---------------------------------------------------
    | FilterEasy
    |---------------------------------------------------
    */
    public array $likeFilterFields = [
        'name'
    ];

    /*
    |---------------------------------------------------
    | Scopes
    |---------------------------------------------------
    */
    public function scopeName($q, string $name)
    {
        return $q->where('name', 'like', "%{$name}%");
    }
}
