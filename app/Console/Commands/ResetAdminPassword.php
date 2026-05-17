<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset admin password by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            $this->info("Creating new admin user...");

            $user = new User();
            $user->name = 'Admin Master';
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->role = 'master'; // <--- BENAR
            $user->save();

            $this->info("✓ New admin user created!");
            $this->info("Email: {$email}");
            $this->info("Password: {$password}");
            return 0;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("✓ Password reset successful!");
        $this->info("Email: {$email}");
        $this->info("New Password: {$password}");

        return 0;
    }
}
