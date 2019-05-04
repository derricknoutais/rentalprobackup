<?php

use Illuminate\Http\Request;
use App\Contrat;
use App\Voiture;
use App\Client;
use GuzzleHttp\Client as Gzclient;
use Illuminate\Support\Facades\Auth;
use App\Document;
use App\Accessoire;
use App\Panne;
use App\Maintenance;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Auth::loginUsingID(1);

Route::get('/', function () {
    $voitures = Voiture::with('contrats')->get();
    $contrats_en_cours = Contrat::where('check_in', '>' ,now())->get()->sortBy('check_in');
    $contrats_en_retard = Contrat::where('check_in', '<', now())->whereNull('real_check_in')->get()->sortBy('check_in');
    return view('welcome', compact('voitures', 'contrats_en_cours', 'contrats_en_retard'));
});

Route::get('/test-upload/{contrat}', function(Contrat $contrat){
    return view('test', compact('contrat'));
});

Route::post('/upload', function(Request $request){
    $array = ['cote_droit', 'cote_gauche', 'arriere', 'avant'];
    $liste_nom = [];
    foreach ($array as $cote) {
        if ($request->hasFile( $cote )) {
            $image = $request->file( $cote );
            $name = time(). uniqid() . '.'.$image->getClientOriginalExtension();
            array_push($liste_nom, $name);
            $destinationPath = public_path('/uploads');
            $image->move($destinationPath, $name);
            // $this->save();
        }
    }
    Contrat::find($request->contrat_id)->update([
        'lien_photo_droit' => $liste_nom[0],
        'lien_photo_gauche' => $liste_nom[1],
        'lien_photo_avant' => $liste_nom[2],
        'lien_photo_arriere' => $liste_nom[3],
    ]);

    return redirect('/contrat/' . $request->contrat_id);
     
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


// VOITURES
Route::get('/voitures', 'VoitureController@index');
Route::get('/voiture/{voiture}', 'VoitureController@show');
Route::post( '/voiture/reception', 'VoitureController@reception');
// Route::get('/voiture/{voiture}/reception', 'VoitureController@reception');
Route::get('/voiture/{voiture}/maintenance', 'VoitureController@maintenance');
Route::post('/voitures/ajout-voiture', function(Request $request){

    $voiture = Voiture::create([
        'immatriculation' => $request->immatriculation,
        'chassis' => $request->numero_chassis,
        'annee' => $request->annee,
        'marque' => $request->marque,
        'type' => $request->type,
        'etat' => 'disponible',
        'prix' => $request->prix
    ]);

    return redirect('/voiture/' . $voiture->id);
});
Route::post('/voitures/{voiture}/ajoute-pannes', function ( Request $request, Voiture $voiture) {
    for ($i=0; $i < $request->nombrePannes; $i++) { 
        $data[] = [
            'voiture_id' => $voiture->id,
            'description' => $request['panne' . $i ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    Panne::insert($data);
    return redirect()->back();
    // return $request->all();
});

// CLIENTS
Route::get('/clients', 'ClientController@index');
Route::get('/clients/{client}', 'ClientController@show');
Route::post( '/clients/ajout-client', function(Request $request){
    $client = Client::create([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'adresse' => $request->addresse,
        'numero_permis' => $request->numero_permis,
        'phone1' => $request->numero_telephone,
        'phone2' => $request->numero_telephone2,
        'phone3' => $request->numero_telephone3,
        'mail' => $request->mail,
        'ville' => $request->ville,
        'cashier_id' => $request->cashier_id
    ]);

    if($request->hasFile('permis')){
        $image = $request->file('permis');
        $nom = time(). uniqid() . '.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/uploads');
        $image->move($destinationPath, $nom);
        $client->update([
            'permis' => $nom
        ]);
    }
    return redirect('/clients/' . $client->id);

});
Route::get('/clients/{client}/edit', 'ClientController@edit');
Route::post('/clients/{client}/update', 'ClientController@update');

// CONTRATS
Route::get('/contrats/menu', 'ContratController@menu');
Route::get('/contrats', 'ContratController@index');
Route::get('/contrats/create', 'ContratController@create');
Route::get('/contrat/{contrat}', 'ContratController@show');
Route::get('/contrat/{contrat}/voir-uploads', 'ContratController@voirUploads');
Route::post('/contrats/{contrat}/ajoute-photos', 'ContratController@ajoutePhotos')->where('contrat', '[0-9]+' );
Route::post('/contrats/{contrat}/update-cashier', 'ContratController@updateCashier')->where( 'contrat', '[0-9]+');
Route::post('/contrats/store', 'ContratController@store');
Route::post( '/contrat/{contrat}/update-cashier-id', function(Request $request, Contrat $contrat){
    $contrat->update([
        'cashier_facture_id' => $request->cashier_id
    ]);
});

// Paramètres
Route::get('/mes-paramètres', function(){
    $documents = Document::all();
    $accessoires = Accessoire::all();
    $voitures = Voiture::with('documents', 'accessoires')->get();
    return view( 'paramètres.index', compact('documents', 'accessoires', 'voitures'));
});
Route::post('/documents', function(Request $request){
    $document = Document::create([
        'type' => $request->type
    ]);
    if($document)
        return redirect('/mes-paramètres');
});
Route::post('/accessoires', function ( Request $request) {
    $accessoire = Accessoire::create([
        'type' => $request->type
    ]);
    if ( $accessoire)
        return redirect('/mes-paramètres');
});
Route::post( '/documents/{document}/destroy', function(Document $document){
    $deleted = $document->delete();
    if ( $deleted )
        return redirect('/mes-paramètres');
});
Route::post('/accessoires/{accessoire}/destroy', function (Accessoire $accessoire) {
    $deleted = $accessoire->delete();
    if ($deleted)
        return redirect('/mes-paramètres');
});
Route::post('/documents/{document}/update', function(Document $document, Request $request){
    $updated = $document->update([
        'type' => $request->type
    ]);
    if ($updated)
        return redirect('/mes-paramètres');
});

Route::post('/accessoires/{accessoire}/update', function ( Accessoire $accessoire, Request $request) {
    $updated = $accessoire->update([
        'type' => $request->type
    ]);
    if ($updated)
        return redirect('/mes-paramètres');
});
Route::post( '/{voiture}/voiture-documents-accessoires', function(Request $request, Voiture $voiture){
    $documents = Document::all();
    $docKeys = [];
    $accessoires = Accessoire::all();
    $accKeys = [];
    foreach ($documents as  $document) {
        array_push($docKeys, str_replace(' ', '', $document->type));
    }
    foreach ( $accessoires as  $accessoire) {
        array_push( $accKeys, str_replace(' ', '', $accessoire->type));
    }
    // return $request;
    for($i = 0; $i < sizeof($documents); $i++){
        if(isset( $request[ $docKeys[$i] ]) && isset( $request['date' . $docKeys[$i]])){
            DB::table('voiture_documents')->updateOrInsert(
                ['voiture_id' => $voiture->id, 'document_id' => $request[$docKeys[$i]]],
                [ 'voiture_id' => $voiture->id, 'document_id' => $request[$docKeys[$i]], 'date_expiration' => $request['date' . $docKeys[$i]]]
            );
        } else {
            DB::table('voiture_documents')->where(['voiture_id' => $voiture->id, 'document_id' => $documents[$i]->id ])->delete();
        }
    }

    for($i = 0; $i < sizeof($accessoires); $i++){
        if(isset( $request[ $accKeys[$i]]) && isset( $request['quantité' . $accKeys [$i]] )) {
            DB::table('voiture_accessoires')->updateOrInsert(
                [ 'voiture_id' => $voiture->id, 'accessoire_id' => $request[ $accKeys[$i]] ],
                [ 'voiture_id' => $voiture->id, 'accessoire_id' => $request[ $accKeys[$i]], 'quantité' => $request[ 'quantité' . $accKeys[$i]]]
            );
        } else {
            DB::table( 'voiture_accessoires')->where(['voiture_id' => $voiture->id, 'accessoire_id' => $accessoires[$i]->id ])->delete();
        }
    }
    return redirect()->back();
    
    
    
});

Route::post('/maintenances/store', function(Request $request){

    return $request->all();

    $maintenance = Maintenance::create([
        'voiture_id' => $request->voiture,
        'technicien_id' => $request->technicien,
    ]);

    for($i = 1; $i <= $request->nombrePannes; $i++){
        Panne::find( $request['panne' . $i])->update([
            'voiture_id' => $request->voiture,
            'maintenance_id' => $maintenance->id
        ]);
    }
    return redirect()->back();
    
});
