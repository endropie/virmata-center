<?php

namespace App\Http\ApiControllers;

use App\Http\Filters\TenantFilter;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(TenantFilter $filter)
    {
        $collection = Tenant::filter($filter)->collective();

        return TenantResource::collection($collection);
    }

    public function store(Request $request)
    {
        $request->validate([
            "id" => "required|string|unique:tenants",
            "name" => "required|string",
            "tenant_type_id" => "required|exists:tenant_types,id",
            "address" => "nullable|string",
        ]);

        $row = $request->only([
            "id", "name", "tenant_type_id", "address"
        ]);

        // app('db')->beginTransaction();

        $record = Tenant::create($row);

        $message = "Tenant [#$record->name] has been created";

        // app('db')->commit();

        return (new TenantResource($record))
            ->additional(["message" => $message]);
    }

    public function show($id)
    {
        $record = Tenant::findOrFail($id);

        return (new TenantResource($record));
    }

    public function update($id, Request $request)
    {
        $record = Tenant::findOrFail($id);

        $request->validate([
            "name" => "required|string",
            "tenant_type_id" => "required|exists:tenant_types,id",
            "address" => "nullable|string",
        ]);

        $row = $request->only([
            "name", "tenant_type_id", "address"
        ]);

        app('db')->beginTransaction();

        $record->update($row);

        $message = "Tenant [$record->id] has been updated";

        app('db')->commit();

        return (new TenantResource($record))
            ->additional(["message" => $message]);
    }

    public function destroy($id)
    {
        $record = Tenant::findOrFail($id);

        app('db')->beginTransaction();

        $record->delete();

        $message = "Tenant [$record->id] has been deleted";

        app('db')->commit();

        return response()->json(["message" => $message]);
    }
}
