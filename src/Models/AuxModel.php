<?php

namespace Gsferro\ResourceCrudEasy\Models;

//use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Gsferro\Select2Easy\Http\Traits\Select2Easy;
use Illuminate\Database\Eloquent\Builder;

abstract class AuxModel extends BaseModel
{
    use Select2Easy; //Cachable,

    /*
    |---------------------------------------------------
    | Configs Table
    |---------------------------------------------------
    */
    protected $primaryKey = "id";
    public    $timestamps = false;
    public $fillable      = ['name'];

    /*
    |---------------------------------------------------
    | FilterEasy
    |---------------------------------------------------
    */
    public array $likeFilterFields = ['name'];

    /*
    |---------------------------------------------------
    | Scopes
    |---------------------------------------------------
    */
    public function scopeName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /*
    |---------------------------------------------------
    | Select2Easy
    |---------------------------------------------------
    */
    public static function sl2Name($term, $page)
    {
        return self::select2easy($term, $page, ['name'], 'name');
    }
}
