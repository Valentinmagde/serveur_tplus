<?php

use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
