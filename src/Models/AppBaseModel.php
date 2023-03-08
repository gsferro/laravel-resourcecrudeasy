<?php

namespace Gsferro\ResourceCrudEasy\Models;

use Gsferro\ResourceCrudEasy\Traits\AuditsFillables;
use Gsferro\FilterEasy\Traits\FilterEasy;
use Gsferro\PowerModel\Traits\PowerModel;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/*
|---------------------------------------------------
| TODO entrara no pacote template-generate-easy
|---------------------------------------------------
*/
abstract class AppBaseModel extends Model implements Auditable
{
    use FilterEasy, AuditsFillables, PowerModel;
}
