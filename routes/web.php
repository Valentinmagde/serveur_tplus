<?php

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

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/transactions', function () {
//     return view('transaction')->with('success', true);
// });



Auth::routes();

Route::get('association/set/default/awallet', 'AssociationController@setAssociationsDefaultAWallets');
// Route::get('association/{assocId}/ag/{ags_id}/rapport', 'RapportController@showForPdfRapport');
// Route::get('association/ag/update_date_ag', 'agsController@updateToUTCTimeAllDateAg');
// Route::get('association/ag/update_date_cloture_ag', 'agsController@updateDateClotureAg');
// Route::get('association/ag/update_date_echeancier', 'EcheancierController@updateToUTCTimeAllDateEcheancier');
// Route::get('association/ag/update_etat_echeancier_past', 'EcheancierController@updateEtatEcheancierPast');
Route::get('association/echeances/delete/acquitement/zero', 'EcheancierController@deleteAcquitementZero');
Route::get('association/credit/echeances/set/to/close', 'EcheancierController@setToCloseCreditOut');
// Route::get('association/ag/update_date_lots_tontine', 'TontineController@updateToUTCTimeAllDateLotsTontine');
