<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateGregGarrisonUser extends Command
{
    protected $signature = 'app:create-greg-garrison-user';

    protected $description = 'Create the Greg Garrison admin user';

    public function handle(): int
    {
        if (User::where('email', 'greg@thegarrisonshow.com')->exists()) {
            $this->info('User greg@thegarrisonshow.com already exists.');

            return self::SUCCESS;
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::create([
            'name' => 'Greg Garrison',
            'email' => 'greg@thegarrisonshow.com',
            'password' => 'test',
            'email_verified_at' => now(),
        ]);

        $user->assignRole('admin');

        $this->info('Greg Garrison admin user created successfully.');

        return self::SUCCESS;
    }
}
