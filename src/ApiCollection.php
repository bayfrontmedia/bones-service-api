<?php

namespace Bayfront\BonesService\Api;

use Bayfront\BonesService\Orm\OrmCollection;

class ApiCollection
{

    protected OrmCollection $ormCollection;

    /**
     * @param OrmCollection $ormCollection
     */
    public function __construct(OrmCollection $ormCollection)
    {
        $this->ormCollection = $ormCollection;
    }

}