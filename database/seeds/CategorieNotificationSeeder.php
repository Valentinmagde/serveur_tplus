<?php

use Illuminate\Database\Seeder;

class CategorieNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categorie = [
            ['id' => 1, 'libelle' => 'Toutes'],
            ['id' => 2, 'libelle' => 'Toutes les notification financières'],
            ['id' => 3, 'libelle' => 'Mes finances'],
            ['id' => 4, 'libelle' => 'Nouvelles'],
            ['id' => 5, 'libelle' => 'Evenements'],
            ['id' => 6, 'libelle' => 'Messages'],
        ];
        DB::table('categories_notifications')->insert($categorie);
    }
}
