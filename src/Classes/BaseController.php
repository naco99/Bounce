<?php

namespace NacAL\Bounce\Classes;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use NacAL\Bounce\Interfaces\IActivityLogger;

class BaseController extends \Illuminate\Routing\Controller
{
	use AuthorizesRequests;
	use DispatchesJobs;
	use ValidatesRequests;
	
	protected IActivityLogger $activityLogger;
	
	public function __construct()
	{
		$this->activityLogger = resolve(IActivityLogger::class);
	}
	
	protected function checkAbilityTo($ability)
	{
		try {
			$this->authorize($ability);
		} catch (AuthorizationException $e) {
			abort(403, __('auth.unauthorised'));
		}
	}
	
	protected function versionedResource(JsonResource $resource)
	{
		return (new VersionableResource($resource))->make();
	}
	
	protected function versionedCollection(JsonResource $resource)
	{
		return (new VersionableResource($resource))->collection();
	}
}
