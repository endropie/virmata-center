<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantSeeder extends Seeder
{
    protected \Faker\Generator $faker;
    
    public function run(): void
    {
        $this->faker = Factory::create();
        $this->fakeData();
    }

    protected function fakeData()
    {
        $this->dropTenantDatabase();
        $sample = $this->createUser('sample', ['phone' => $this->faker->unique()->phoneNumber()]);
        $types = \App\Models\TenantType::all();
        $inviteTokens = collect();

        $rows = ['aaa', 'bbb', 'ccc', 'ddd', 'eee', 'fff', 'ggg', 'hhh', 'iii', 'jjj'];

        foreach ($rows as $name) {
            $user = $this->createUser($name, ['phone' => $this->faker->unique()->phoneNumber()]);

            auth()->loginUsingId($user->id);

            $request = new Request([
                'id' => $name, 
                'name' => $this->faker->company(),
                'address' => $this->faker->address(),
                'tenant_type_id' => $types->shuffle()->first()?->id,
            ]);

            $tenant = app(\App\Http\ApiControllers\TenantController::class)->store($request)->resource;

            $tenant->setUserAccess($sample, ['level' => 'operator']);

            for ($i=0; $i < rand(0,3); $i++) 
            {
                $nameInvite = $name."-". str_pad("$i", 3, "0", STR_PAD_LEFT);
                $userInvite = $this->createUser($nameInvite, ['phone' => $this->faker->unique()->phoneNumber()]);

                
                $request = new Request([
                    'context' => $userInvite->email,
                    'level' => 'operator',
                ]);
                $response = app(\App\Http\ApiControllers\TenantInviteController::class)->store($tenant->id, $request);
                

                if ($plainToken = $response->additional['plain-token'] ?? false) {
                    $inviteTokens->push($plainToken);
                }

            }
        }

        $this->confirmOfInvites($inviteTokens);
    }

    protected function dropTenantDatabase()
    {
        foreach (DB::select('SHOW DATABASES') as $e) {
            $prefix = config('tenancy.database.prefix');
            $suffix = config('tenancy.database.suffix');
            if ($prefix && !stringable($e->Database)->startsWith($prefix)) continue;
            if ($suffix && !stringable($e->Database)->endsWith($suffix)) continue;
            
            Schema::dropDatabaseIfExists($e->Database);
        }
    }

    public static function createUser($name = 'user', $attrs = [])
    {
        $row = array_merge([
            'name' => "$name",
            'email' => "$name@example.com",
            'password' => bcrypt('password'),
        ], $attrs);

        return  \App\Models\User::firstOrCreate(['email' => $row['email']], $row);
    }

    protected function confirmOfInvites($tokens)
    {
        foreach ($tokens as $token) 
        {
            $invite = \App\Models\TenantInvite::inviting()->whereToken($token)->first();
            $name = stringable($invite->context)->explode("@")->first();
            $user = $this->createUser($name);
            
            auth()->loginUsingId($user->id);

            $request = new Request([
                'token' => $token,
                'confirm' => 'accepted',
            ]);

            try {
                app(\App\Http\ApiControllers\TenantInviteController::class)->confirmByToken($request);
            } catch (\Throwable $th) {
                $this->command->error("Invite confirmation [$token] Failed [". $th->getMessage() ."]");
            }

        }
    }
}
