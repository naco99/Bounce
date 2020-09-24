<?php
/**
 * Created by iCompta team.
 * nacer99@gmail.com
 */

/**
 * Date: 4/12/20
 * Time: 14:35
 */

namespace NacAL\Bounce\Interfaces;


use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Interface IActivityLogger
 * @package App\ActivityLogger
 */
interface IActivityLogger
{
    /**
     * @param $context :in:admin|user
     * @return mixed
     */
    public function setContext($context): self;

    public function by(Authenticatable $person): self;

    public function setResourceId(int $id): self;

    public function setOldData(array $data): self;

    public function setNewData(array $data): self;

    /**
     * Log the activity
     * @param $activity
     */
    public function log($activity): void;
}
