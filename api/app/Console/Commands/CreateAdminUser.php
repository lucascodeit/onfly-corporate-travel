<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin';

    protected $description = 'Create a default admin user to bootstrap the system';

    public function handle(): int
    {
        $firstName = $this->ask('First name');
        $lastName = $this->ask('Last name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        $validator = Validator::make(
            compact('firstName', 'lastName', 'email', 'password'),
            [
                'firstName' => ['required', 'string', 'max:50'],
                'lastName' => ['required', 'string', 'max:50'],
                'email' => ['required', 'email', 'max:120', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return Command::FAILURE;
        }

        User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($password),
            'type' => 'admin',
            'is_active' => true,
        ]);

        $this->info("Admin user [{$email}] created successfully.");

        return Command::SUCCESS;
    }
}
