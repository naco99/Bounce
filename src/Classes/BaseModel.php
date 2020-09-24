<?php

/**
 * By NacAL
 * nacer99@gmail.com
 */

namespace NacAL\Bounce\Classes;


use NacAL\Bounce\Exceptions\AppException;
use Illuminate\Database\Eloquent\Model;
use NacAL\Bounce\Interfaces\Validatable;
use NacAL\Bounce\Traits\Validate;

/**
 * Class BaseModel
 * @package App\Repositories
 */
abstract class BaseModel extends Model implements Validatable
{
    use Validate;

    protected array $availableRelations = [];

    /**
     * @return array
     * @throws AppException
     */
    public function getAvailableRelations(): array
    {
        if (empty($this->availableRelations)) {
            throw new AppException("define available relations variable on model identified by: " . $this->getTable());
        }
        return $this->availableRelations;
    }

    public function getAvailableRelationsAsString(): string
    {
        try {
            $relations = $this->getAvailableRelations();
        } catch (AppException $e) {
            return '';
        }

        return implode(',', $relations);
    }

    /**
     * @param array $ids
     * @return array
     */
    protected function filterNumericIds(array $ids)
    {
        return array_filter($ids, function ($id) {
            return is_numeric($id) && $id > 0;
        });
    }
}
