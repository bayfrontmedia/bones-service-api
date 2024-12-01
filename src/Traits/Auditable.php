<?php

namespace Bayfront\BonesService\Api\Traits;

trait Auditable
{

    /**
     * Get array of function names to audit.
     *
     * @return array
     */
    abstract public function getAuditableFunctions(): array;

}