<?php

namespace App\Http\ApiControllers;

use App\Http\Filters\TenantInviteFilter;
use App\Http\Resources\TenantInviteResource;
use App\Models\TenantInvite;
use Illuminate\Http\Request;

class TenantInviteController extends Controller
{
    public function index(TenantInviteFilter $filter)
    {
        $collection = TenantInvite::filter($filter)->collective();

        return TenantInviteResource::collection($collection);
    }

    public function store($id, Request $request)
    {
        $tenant = \App\Models\Tenant::findOrFail($id);

        $request->validate([
            "context" => "required|string",
            "level" => "required",
        ]);

        $row = $request->only(["context", "level"]);

        app('db')->beginTransaction();

        $record = $tenant->invites()->create($row);

        $message = "Invite [$record->context] has been created";

        app('db')->commit();

        return (new TenantInviteResource($record))
            ->additional(array_merge(
                ["message" => $message],
                env('APP_ENV') === 'local' ? ['plain-token' => $record->getCreatedPlainToken()] : []
            ));
    }

    public function getInvitingByToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $record = TenantInvite::authorized()->inviting()->whereToken($request->get('token'))->firstOrFail();

        return (new TenantInviteResource($record));
    }

    public function confirmByToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string', 
            'confirm' => 'required|in:rejected,accepted',
        ]);
        
        $record = TenantInvite::authorized()->inviting()->whereToken($request->get('token'))->firstOrFail();

        app('db')->beginTransaction();

        $record->setConfirmed($request->get('confirm'), [
            'level' => $record->level,
        ]);

        app('db')->commit();

        return (new TenantInviteResource($record))
            ->additional(['message' => "The inviting has been ". $request->get('confirm') ."."]);
    }

    public function destroy($id)
    {
        $record = TenantInvite::findOrFail($id);

        app('db')->beginTransaction();

        $record->delete();

        $message = "Invite [$record->context] has been canceled";

        app('db')->commit();

        return response()->json(["message" => $message]);
    }
}
