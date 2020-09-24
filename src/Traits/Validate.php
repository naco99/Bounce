<?php

/**
 * By NacAL
 * nacer99@gmail.com
 */

namespace NacAL\Bounce\Traits;

use Illuminate\Contracts\Validation\Validator;

/**
 * Trait Validate
 * @package App\Repositories
 */
trait Validate
{
    /**
     * @param array $data
     * @return Validator
     */
    public function validateOnCreate(array $data): Validator
    {
        $messages = $this->validation_messages ?? [];

        $custom_attributes = (method_exists($this, 'getValidationAttributes')) ? $this->getValidationAttributes() : [];

        return \Validator::make($data, $this->getCreateRules(), $messages, $custom_attributes);
    }

    /**
     * @param array $data
     * @return Validator
     */
    public function validateOnUpdate(array $data): Validator
    {
        $messages = $this->validation_messages ?? [];

        $custom_attributes = (method_exists($this, 'getValidationAttributes')) ? $this->getValidationAttributes() : [];

        return \Validator::make($data, $this->getUpdateRules($data), $messages, $custom_attributes);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getUpdateRules(array $data = [])
    {
        return [
            $this->getKeyName() => 'required|integer'
        ];
    }
}
