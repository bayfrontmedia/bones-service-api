<?php

namespace Bayfront\BonesService\Api\Interfaces;

interface CrudControllerInterface
{

    /**
     * Create new resource.
     *
     * @param array $params
     * @return void
     */
    public function create(array $params): void;

    /**
     * List resources.
     *
     * @param array $params
     * @return void
     */
    public function list(array $params): void;

    /**
     * Read single resource.
     *
     * @param array $params
     * @return void
     */
    public function read(array $params): void;

    /**
     * Update existing resource.
     *
     * @param array $params
     * @return void
     */
    public function update(array $params): void;

    /**
     * Delete single resource.
     *
     * @param array $params
     * @return void
     */
    public function delete(array $params): void;

}