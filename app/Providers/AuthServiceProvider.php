<?php

namespace App\Providers;

use App\Models\User; // <-- TAMBAHKAN INI
use Illuminate\Support\Facades\Gate; // <-- TAMBAHKAN INI
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // --- TAMBAHKAN BLOK KODE INI ---

        // Kita membuat "Gerbang" (Gate) bernama 'is-master-admin'
        // Gerbang ini hanya akan terbuka jika...
        Gate::define('is-master-admin', function (User $user) {
            // ...user yang sedang login memiliki 'role' == 'master'
            return $user->role === 'master';
        });

        // --- AKHIR BLOK KODE ---
    }
}