<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $documents = ['Carte Grise', 'Visite Technique', 'Assurance', 'Carte Extincteur'];
        $accessoires = ['Crick', 'Triangle', 'Manivelle', 'Calle Métallique', 'Pneu Secours', 'Extincteur', 'Gilet', 'Trousse Secours'];

        factory(App\Compagnie::class, 10)->create();
        
        foreach ($documents as $document) {
            App\Document::create([
                'type' => $document,
                'compagnie_id' => 1
            ]);
        }
        foreach ($accessoires as $accessoire) {
            App\Accessoire::create([
                'type' => $accessoire,
                'compagnie_id' => 1
            ]);
        }
        factory(App\Client::class, 10)->create();
        
        factory(App\User::class, 10)->create();

        factory(App\Voiture::class, 10)->create()->each(function($voiture) use ($accessoires, $documents){
            if($voiture->etat == 'Loué'){
                factory(App\Contrat::class, 5)->create([
                    'voiture_id' => $voiture->id,
                    'client_id' => rand(0,9)
                ]);
            }
            for ($i=0; $i < sizeof($accessoires); $i++) { 
                $voiture->accessoires()->save(App\Accessoire::find($i+1), ['quantité' => rand(0,2)]);
            }
            for ($i=0; $i < sizeof($documents); $i++) { 
                $voiture->documents()->save(App\Document::find($i+1));
            }
            
        });

        factory(App\Technicien::class, 3)->create();

        factory(App\Maintenance::class, 100)->create()->each(function($maintenance) {
            factory(App\Panne::class, 10)->create([
                'maintenance_id' => $maintenance->id
            ]);
        });

        

    }
}
