<?php

/**
 * By NacAL
 * nacer99@gmail.com
 */

namespace NacAL\Bounce\Classes;


use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class VersionableResource
 * @package App\Repositories
 * The purpose of this class is to resolve and return the correct versioned Resource
 * So to make versionable resource, we create the versioned class inside directory named vx where x is the version number
 * the versioned class must be named exactly the same as the base class
 * | - Transformers
 * | --- ResourceClass.php
 * | --- v2
 * | ----- ResourceClass.php
 * | --- v3
 * | ----- ResourceClass.php
 */
class VersionableResource
{
    /**
     * @var JsonResource
     */
    private JsonResource $class;

    /**
     * VersionableResource constructor.
     * @param $class
     */
    public function __construct($class)
    {
        $path_segments = explode('\\', get_class($class));

        $class_name = array_pop($path_segments);

        $class_path = implode('\\', $path_segments);

        $versioned_resource = $class_path . '\\' . config('api.version') . '\\' . $class_name;

        if (class_exists($versioned_resource)) {
            $this->class = new $versioned_resource($class->resource);
        } else {
            $this->class = $class;
        }
    }

    /**
     * @return JsonResource
     */
    public function make()
    {
        if (collect($this->class->resource)->isEmpty()) {
            return null;
        }
        return $this->class::make($this->class->resource);
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function collection()
    {
        return $this->class::collection($this->class->resource);
    }
}
