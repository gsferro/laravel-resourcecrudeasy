<?php

namespace Gsferro\ResourceCrudEasy\Interfaces;

interface DatatablesInterface
{
    public function getDatatablesGrid(): array;
    public function getDatatablesColumns(): array;
}