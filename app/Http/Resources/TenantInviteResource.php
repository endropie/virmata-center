<?php

namespace App\Http\Resources;


class TenantInviteResource extends Resource
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
            $this->mergeField('owner', function () {
                return new UserResource($this->resource->owner);
            })
        ];
    }
}
