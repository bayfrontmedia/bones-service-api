<?php

namespace Bayfront\BonesService\Api\Interfaces;

use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;

interface ApiControllerInterface
{

    /**
     * Create new resource.
     *
     * @param array $params
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function create(array $params): void;

    /**
     * List resources.
     *
     * @param array $params
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function list(array $params): void;

    /**
     * Read single resource.
     *
     * @param array $params
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function read(array $params): void;

    /**
     * Update existing resource.
     *
     * @param array $params
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function update(array $params): void;

    /**
     * Delete single resource.
     *
     * @param array $params
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function delete(array $params): void;

}