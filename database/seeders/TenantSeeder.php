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
    const ICONS = [
        'corporate_fare', 'fitness_center', 'lunch_dining', 'beach_access',
        'liquor', 'airplane_ticket', 'dinner_dining', 'car_repair', 'storefront', 'domain',
        'engineering', 'flight'
    ];
    const COLORS = [
        'red', 'orange', 'purple', 'indigo', 'blue', 'cyan', 'tear', 'green',
        'orange', 'deep-orange', 'brown', 'blue-grey'
    ];

    protected \Faker\Generator $faker;

    public function run(): void
    {
        $this->faker = Factory::create();
        $this->fakeData();
    }

    protected function fakeData()
    {
        $this->dropTenantDatabase();
        $types = \App\Models\TenantType::all();
        $inviteTokens = collect();

        $sample = $this->createUser('sample', [
            'id' => '99999999-f2d4-4975-b96a-82063f366975',
            'phone' => $this->faker->unique()->phoneNumber()
        ]);


        auth()->loginUsingId($sample->id);

        $request = new Request([
            'id' => 'sample',
            'name' => 'Sample, Corp.',
            'address' => $this->faker->address(),
            'tenant_type_id' => $types->shuffle()->first()?->id,
            'cluster' => $this->faker->randomElement(config('tenancy.app.cluster.options')),
            'avatar' => [
                'mode' => 'icon',
                'value' => $this->faker->randomElement(static::ICONS),
                'iconColor' => $this->faker->randomElement(static::COLORS),
            ]
        ]);

        app(\App\Http\Api\TenantController::class)->store($request)->resource;

        $rows = ['aaa', 'bbb', 'ccc', 'ddd', 'eee', 'fff', 'ggg', 'hhh', 'iii', 'jjj'];

        foreach ($rows as $name) {
            $user = $this->createUser($name, ['phone' => $this->faker->unique()->phoneNumber()]);

            auth()->loginUsingId($user->id);

            $request = new Request([
                'id' => $name,
                'name' => $this->faker->company(),
                'address' => $this->faker->address(),
                'tenant_type_id' => $types->shuffle()->first()?->id,
                'cluster' => $this->faker->randomElement(array_keys(config('tenancy.app.cluster.options'))),
                'avatar' => [
                    'mode' => 'icon',
                    'value' => $this->faker->randomElement(static::ICONS),
                    'iconColor' => $this->faker->randomElement(static::COLORS),
                ]
            ]);

            $tenant = app(\App\Http\Api\TenantController::class)->store($request)->resource;

            if(rand(0,5) > 1) {
                $tenant->setUserAccess($sample, [
                    'level' => $this->faker->randomElement(['operator', 'administrator'])
                ]);
            }

            for ($i=0; $i < rand(0,3); $i++)
            {
                $nameInvite = $name."-". str_pad("$i", 3, "0", STR_PAD_LEFT);
                $userInvite = $this->createUser($nameInvite, ['phone' => $this->faker->unique()->phoneNumber()]);


                $request = new Request([
                    'context' => $userInvite->email,
                    'level' => $this->faker->randomElement(['operator', 'administrator']),
                ]);
                $response = app(\App\Http\Api\TenantInviteController::class)->store($tenant->id, $request);


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

    protected function createUser($name = 'user', $attrs = [])
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
                app(\App\Http\Api\TenantInviteController::class)->confirmByToken($request);
            } catch (\Throwable $th) {
                $this->command->error("Invite confirmation [$token] Failed [". $th->getMessage() ."]");
            }

        }
    }
}
