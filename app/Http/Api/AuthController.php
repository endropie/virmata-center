<?php

namespace App\Http\Api;

use App\Http\Filters\TenantFilter;
use App\Http\Resources\TenantResource;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        return new UserResource($request->user());
    }
}
