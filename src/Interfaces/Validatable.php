<?php

/**
 * By NacAL
 * nacer99@gmail.com
 */


namespace NacAL\Bounce\Interfaces;


use Illuminate\Contracts\Validation\Validator;

interface Validatable
{
    /**
     * @param array $data
     * @return Validator
     */
    public function validateOnCreate(array $data): Validator;

    /**
     * @param array $data
     * @return Validator
     */
    public function validateOnUpdate(array $data): Validator;

    /**
     * @return array
     */
    public function getCreateRules(): array;
}
