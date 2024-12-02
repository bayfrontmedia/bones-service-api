<?php

namespace Bayfront\BonesService\Api\Interfaces;

interface ApiControllerInterface
{

    /**
     * Create new resource.
     *
     * @param array $params
     * @return void
     * @throws ApiExceptionInterface
     */
    public function create(array $params): void;

    /**
     * List resources.
     *
     * @param array $params
     * @return void
     * @throws ApiExceptionInterface
     */
    public function list(array $params): void;

    /**
     * Read single resource.
     *
     * @param array $params
     * @return void
     * @throws ApiExceptionInterface
     */
    public function read(array $params): void;

    /**
     * Update existing resource.
     *
     * @param array $params
     * @return void
     * @throws ApiExceptionInterface
     */
    public function update(array $params): void;

    /**
     * Delete single resource.
     *
     * @param array $params
     * @return void
     * @throws ApiExceptionInterface
     */
    public function delete(array $params): void;

}