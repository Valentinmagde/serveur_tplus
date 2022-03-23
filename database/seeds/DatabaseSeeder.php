<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(RoleTableSeeder::class);
        $this->seed_roles();
    }

    public function seed_roles()
    {
        $roles = [
            ['id' => 1, 'libelle' => 'Administrateur'],
            ['id' => 2, 'libelle' => 'Financier'],
            ['id' => 3, 'libelle' => 'Contrôleur Financier'],
            ['id' => 4, 'libelle' => 'Moderateur contenu'],
        ];
        DB::table('roles')->insert($roles);
    }
}
