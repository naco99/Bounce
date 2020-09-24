<?php

/**
 * By NacAL
 * nacer99@gmail.com
 */

namespace NacAL\Bounce\Classes;

use Arr;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use NacAL\Bounce\Exceptions\AppException;
use NacAL\Bounce\Exceptions\VException;
use Str;

/**
 * Class BaseRepository
 * @package App\Repositories
 */
class BaseRepository
{
	// useful in need to add a hidden field to select columns
	protected array $extraFields = [];
	
	/**
	 * @var Model|BaseModel|Builder|SoftDeletes
	 */
	protected $model;
	
	protected Model $dirty;
	
	/**
	 * BaseRepository constructor.
	 * @param BaseModel $model
	 */
	public function __construct(BaseModel $model)
	{
		$this->model = $model;
	}
	
	/**
	 * @param array $data
	 * @param array $relations
	 * @return mixed | null
	 * @throws VException
	 * @throws AppException
	 */
	public function create(array $data, ...$relations)
	{
		$data = Arr::only($data, $this->model->getFillable());
		
		if (!$this->beforeCreate($data)) {
			return null;
		}
		
		$model = $this->persist($data);
		
		if (!$model) {
			return false;
		}
		
		return $this->findFirst($model->getKey(), $this->getValidRelations($relations));
	}
	
	/**
	 * @param array $data
	 * @return Builder|Model|null
	 */
	protected function persist(array $data)
	{
		try {
			return $this->model->create($data);
		} catch (QueryException $e) {
			report(new AppException($e->getMessage()));
			return null;
		}
	}
	
	/**
	 * @param array $data
	 * @param array $relations
	 * @return mixed
	 * @throws VException
	 * @throws AppException
	 */
	public function update(array $data, ...$relations)
	{
		$model = $this->model;
		
		$relations = $this->getValidRelations($relations);
		
		foreach ($relations as $relation) {
			$model = $model->with($relation);
		}
		
		$model = $model->find($data[$this->model->getKeyName()]);
		
		if (!$model) {
			throw new ModelNotFoundException();
		}
		$data = Arr::only($data, array_merge([$this->model->getKeyName()], $this->model->getFillable()));
		
		if (!$this->beforeUpdate($data)) {
			return false;
		}
		return $this->persistUpdates($model, $data);
	}
	
	/**
	 * @param Model $model
	 * @param array $data
	 * @return bool|Model
	 */
	protected function persistUpdates(Model $model, array $data)
	{
		$fillables = $this->model->getFillable();
		
		$this->dirty = clone $model;
		
		foreach ($fillables as $fillable) {
			if (!array_key_exists($fillable, $data)) {
				continue;
			}
			$model->{$fillable} = $data[$fillable];
		}
		
		try {
			$model->save();
			return $model;
		} catch (QueryException $e) {
			report(new AppException($e->getMessage()));
			return false;
		}
	}
	
	/**
	 * @param int $model_id
	 * @return bool|Model
	 * @throws ModelNotFoundException
	 */
	public function delete(int $model_id)
	{
		$model = $this->model->find($model_id);
		
		if (!$model) {
			throw new ModelNotFoundException(__('errors.not_found'));
		}
		
		try {
			$model->delete();
			return $model;
		} catch (Exception $e) {
			report(new AppException($e->getMessage()));
			return false;
		}
	}
	
	/**
	 * @param int $model_id
	 * @return bool|Model
	 */
	public function forceDelete(int $model_id)
	{
		if (!in_array(SoftDeletes::class, class_uses($this->model))) {
			return $this->delete($model_id);
		}
		
		$model = $this->model::onlyTrashed()->find($model_id);
		
		if (!$model) {
			throw new ModelNotFoundException(__('errors.not_found'));
		}
		
		try {
			$model->forceDelete();
			return $model;
		} catch (Exception $e) {
			report(new AppException($e->getMessage()));
			return false;
		}
	}
	
	/**
	 * @param int $model_id
	 * @return bool|Model
	 */
	public function restoreSoftDeleted(int $model_id)
	{
		if (!in_array(SoftDeletes::class, class_uses($this->model))) {
			return $this->model->findOrFail($model_id);
		}
		
		$model = $this->model::withTrashed()->findOrFail($model_id);
		
		if (!$model->trashed()) {
			return $model;
		}
		
		if (!$model->restore()) {
			return false;
		}
		
		return $model;
	}
	
	/**
	 * in need to add a hidden field
	 * @param array $fields
	 */
	public function addSelect(array $fields)
	{
		$this->extraFields += $fields;
	}
	
	/**
	 * @return array
	 */
	public function getPublicFillable(): array
	{
		if (method_exists($this->model, 'getPublicFillable')) {
			return $this->model->getPublicFillable();
		}
		
		return $this->model->getFillable();
	}
	
	/**
	 * get model fillable with model keyName
	 * @param string $alias
	 * @return array
	 */
	public function getAllFields($alias = ''): array
	{
		$fields = array_merge(
			[$this->model->getKeyName()],
			$this->model->getDates(),
			$this->getPublicFillable(),
			$this->extraFields,
		);
		
		if ($this->model->timestamps) {
			$fields = array_merge($fields, ['created_at', 'updated_at']);
		}
		
		if ($alias !== '') {
			$fields = array_map(function ($field) use ($alias) {
				$field = $alias . '.' . $field;
				return $field;
			}, $fields);
		}
		return $fields;
	}
	
	/**
	 * @param $primary_key
	 * @param array $relations
	 * @return mixed|BaseModel|null
	 * @throws AppException
	 */
	public function findFirst($primary_key, ...$relations)
	{
		$model = $this->model;
		
		$relations = $this->getValidRelations($relations);
		
		foreach ($relations as $relation) {
			$model = $model->with($relation);
		}
		
		return $model->whereKey($primary_key)
			->select($this->getAllFields())
			->first();
	}
	
	/**
	 * @param mixed ...$relations
	 * @return Builder[]|Collection
	 * @throws AppException
	 */
	public function all(...$relations)
	{
		$all = $this->model;
		
		$relations = $this->getValidRelations($relations);
		
		foreach ($relations as $relation) {
			$all = $all->with($relation);
		}
		
		return $all
			->select($this->getAllFields())
			->get();
	}
	
	/**
	 * @param $per_page
	 * @param mixed ...$relations
	 * @return LengthAwarePaginator
	 * @throws AppException
	 */
	public function paginate($per_page, ...$relations)
	{
		$chunk = $this->model;
		
		$relations = $this->getValidRelations($relations);
		
		foreach ($relations as $relation) {
			$chunk = $chunk->with($relation);
		}
		
		return $chunk
			->select($this->getAllFields())
			->paginate($per_page);
	}
	
	/**
	 * @param mixed ...$relations
	 * @return Builder[]|Collection|SoftDeletes[]|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
	 * @throws AppException
	 */
	public function getAllWithTrashed(...$relations)
	{
		$relations = $this->getValidRelations($relations);
		
		if (!in_array(SoftDeletes::class, class_uses($this->model))) {
			return $this->getAllWithoutTrashed($relations);
		}
		
		$all = $this->model::withTrashed();
		
		foreach ($relations as $relation) {
			$all = $all->with($relation);
		}
		
		return $all
			->select($this->getAllFields())
			->get();
	}
	
	/**
	 * @param mixed ...$relations
	 * @return Builder[]|Collection
	 * @throws AppException
	 */
	public function getAllWithoutTrashed(...$relations)
	{
		return $this->all($relations);
	}
	
	/**
	 * @return Validatable|Builder|BaseModel
	 */
	public function getModel()
	{
		return $this->model;
	}
	
	/**
	 * @return Model|null
	 */
	public function getDirty()
	{
		return $this->dirty ?? null;
	}
	
	/**
	 * cast $array to current Model
	 * @param array $array
	 * @return mixed
	 */
	protected function hydrate(array $array)
	{
		return $this->model->hydrate($array);
	}
	
	/**
	 * @param array $data
	 * @return bool
	 * @throws VException
	 */
	protected function beforeCreate(array $data)
	{
		$data = array_filter($data);
		
		return $this->validateOnCreate($data);
	}
	
	/**
	 * @param array $data
	 * @return bool
	 * @throws VException
	 */
	protected function beforeUpdate(array $data)
	{
		$data = array_filter($data);
		
		$validation = $this->model->validateOnUpdate($data);
		
		if ($validation->fails()) {
			throw new VException($validation->errors());
		}
		
		return true;
	}
	
	/**
	 * @param array $data
	 * @return bool
	 * @throws VException
	 */
	protected function validateOnCreate(array $data): bool
	{
		$validation = $this->model->validateOnCreate($data);
		
		if ($validation->fails()) {
			throw new VException($validation->errors());
		}
		
		return true;
	}
	
	/**
	 * @return array
	 */
	public function getCreateRules()
	{
		return $this->model->getCreateRules();
	}
	
	/**
	 * @param mixed ...$relations
	 * @return array
	 * @throws AppException
	 */
	private function getValidRelations(...$relations): array
	{
		$relations = Arr::first($relations);
		
		$relations = array_filter($relations);
		if (empty($relations)) {
			return [];
		}
		
		$valid_relations = [];
		
		foreach ($relations as $relation) {
			$fields = [];
			
			if (Str::contains($relation, ':')) {
				$relation_name = Str::before($relation, ':');
				$fields = explode(',', Str::after($relation, ':'));
			} else {
				$relation_name = $relation;
			}
			
			if (!in_array($relation_name, $this->model->getAvailableRelations())) {
				continue;
			}
			
			if (empty($fields)) {
				$valid_relations[] = $relation_name;
				continue;
			}
			
			if (empty($fields)) {
				$valid_relations[] = $relation_name;
				continue;
			}
			
			$fields = array_map(function ($filed) {
				return htmlspecialchars($filed);
			}, $fields);
			
			$valid_relations[] = $relation_name . ':' . implode(',', $fields);
		}
		
		return $valid_relations;
	}
}
