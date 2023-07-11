<?php

namespace App\Http\Resources;


class TenantResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            $this->mergeAttributes(),
            $this->mergeInclude('owner', function () {
                return new UserResource($this->resource->owner);
            }),
            $this->mergeField('is_owner', function () {
                return $this->resource->isOwner();
            }),
            $this->mergeField('as_level', function () {
                return $this->resource->asLevel();
            }),
        ];
    }
}
