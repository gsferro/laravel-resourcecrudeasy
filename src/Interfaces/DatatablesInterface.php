<?php

namespace Gsferro\ResourceCrudEasy\Interfaces;

interface DatatablesInterface
{
    public function getDatatablesGrid(?string $index = null): array;
}