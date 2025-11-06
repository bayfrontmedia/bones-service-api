<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\Validator\Validator;

trait CreatesOrUpdatesUser
{

    /**
     * Validate user meta.
     *
     * @param array $body
     * @param string $action (create/update)
     * @return void
     * @throws BadRequestException
     */
    protected function validateUserMeta(array $body, string $action): void
    {

        $meta_rules = $this->apiService->getConfig('meta.user', []);

        /** @noinspection DuplicatedCode */
        if (!empty($meta_rules)) {

            $meta = (array)Arr::get($body, 'meta', []);

            if ($action == 'update') {

                foreach ($meta_rules as $k => $v) {

                    if (array_key_exists($k, $meta)) {

                        if (str_contains($v, 'required') && $meta[$k] === null) {
                            throw new BadRequestException('Unable to ' . $action . ' resource: Missing required meta field(s)');
                        }

                    }

                    $meta_rules[$k] = str_replace([
                        'required|',
                        '|required',
                        'required'
                    ], '', $v);

                }

            }

            $validator = new Validator();

            $validator->validate($meta, $meta_rules, false, true);

            if (!empty(Arr::except($meta, array_keys($meta_rules))) || !$validator->isValid()) {
                throw new BadRequestException('Unable to ' . $action . ' resource: Invalid and/or missing meta field(s)');
            }

        }

    }

}