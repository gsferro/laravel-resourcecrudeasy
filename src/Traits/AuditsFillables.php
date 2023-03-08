<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use \OwenIt\Auditing\Auditable;

trait AuditsFillables
{
    use Auditable;
    public function getAuditInclude(): array
    {
        return $this->getFillable();
    }
}