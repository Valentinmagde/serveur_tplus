<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::group([
//     'prefix' => '{locale}', 
//     'where' => ['locale' => '[a-zA-Z]{2}'], 
//     'middleware' => 'setlocale'], function() {


//User routes
//gestion des utilisateurs
Route::group(['prefix' => 'user'], function () {
    //Création d'un compte
    Route::post('/register', 'UserController@register');
    //Connecter l'utilisateur
    Route::post('/login', 'UserController@login');
    //Reinitialiser le mot de passe d'un utilisateur
    Route::post('/recoverpwd', 'UserController@recoverePwd');
    //Changer le mot de passe de l'utilisateur
    Route::post('/changepwd', 'UserController@changePwd');
    //Activer le compte d'un utilisateur
    Route::post('/activate', 'CodeVerifyController@activate');
    //Rafraichir le token
    Route::post('/refreshToken', 'UserController@refreshToken');
    //Connection avec les reseaux sociaux
    Route::post('/loginSocial', 'UserController@loginSocial');
    //Enregistrement avec les reseaux sociaux
    Route::post('/registerSocial', 'UserController@registerSocial');

    Route::post('association/upload', 'UserController@upload');

    //resend new code
    Route::post('/activation/code/resend', 'UserController@resendCode');
});
//seconde partie de gestion d'utilisateur
Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/user/info/token', 'UserController@token');
    //récuperer les infos d'un utilisateur
    Route::get('/user/{id}', 'UserController@getUserById');

    //mark notification as read
    Route::get('/user/{id}/read/notification/{notification_id}', 'UserController@markAsRead');
    Route::post('/user/{id}/read/notifications', 'UserController@markMultipleAsRead');

    //Editer le profil
    Route::post('/user', 'UserController@updateUser');

    //Deconnecter l'utilisateur
    Route::post('/user/logout', 'UserController@logout');

    Route::get('/user/desactivate/{user}', 'UserController@desactivate');
});
//Association routes

Route::group(['middleware' => ['auth:api']], function () {

    //Extraire le details sur une association specifique
    Route::get('/association/{id}', 'UserController@getAssociationById');

    //Creation d'une association
    Route::post('/association', 'AssociationController@createAssociation');


    //changer l'etat d'une association
    Route::get('/association/{assocId}/state', 'AssociationController@changeStateAssociation');

    //Suppression d'une association
    Route::delete('/association/{assocId}/admin/{adminId}', 'AssociationController@deleteAssociation');

    //Configuration d'une association/Modification de la configuration d'une association/Passage d'une association en production
    Route::post('/association/updateParams', 'AssociationController@updateAssociation');

    //Rejoindre une association
    Route::post('/association/rejoindre', 'AssociationController@rejoindreAssociation');

    //Quitter une association
    Route::post('/association/quitter', 'AssociationController@quitterAssociation');

    //Lister toutes les associations d'un utilisateur
    Route::get('/associations/user/{id}', 'AssociationController@getAssociationMemberById');

    //récupérer un les associations d'un admin
    Route::get('/associations/admin/{admin_id}', 'AssociationController@getAssociationAdmin');

    //Liste des utilisateurs d'un compte membres d'une association
    Route::get('/association/{assocId}/membre/{membreId}/users', 'AssociationController@getUser');

    //Associer un compte à un membre d'une association
    Route::post('/association/{assocID}/membre/{id}/connect', 'AssociationController@connect');

    //Desinscrire un utilisateur/Enleve le lien entre un compte et un utilisateur
    Route::post('/association/{assocId}/membre/{id}/disconnect', 'AssociationController@disconnect');

    //add size to association
    Route::post('/association/{assocId}/add/size', 'AssociationController@addSize');
});
//Member routes
//Liste des membres d'une association
//Route::middleware('auth:api')->get('/association/{assocId}/membre', 'AssociationController@getAssociationMember');

Route::group(['middleware' => ['auth:api']], function () {

    Route::get('/association/{associationId}/membre', 'memberController@getMembers');

    //Créer un compte membre/modifier un compte membre/Ajout du membre dans une association
    Route::post('/association/{assocId}/membre', 'memberController@createMember');

    //Créer plusieurs comptes membre/modifier un compte membre/Ajout du membre dans une association
    Route::post('/association/{assocId}/membres/csv', 'memberController@createMembersCsv');

    //Créer plusieurs comptes membre/modifier un compte membre/Ajout du membre dans une association
    Route::post('/association/{assocId}/membres/masse', 'memberController@createMembersMass');

    //Efface ou enleve un membre d'une association
    Route::delete('/association/{assocId}/membre/{id}', 'memberController@deleteAssociationMember');

    //Efface ou enleve plusieurs comptes membre d'une association
    Route::post('/association/{assocId}/membres/delete', 'memberController@deleteAssociationMembers');

    //Inviter l'utilisateur à joindre une association
    Route::post('/association/{assocId}/membre/{id}/inviter', 'memberController@inviteUser');

    //Inviter l'utilisateur à joindre une association
    Route::post('/association/{assocId}/membre/{id}/inviter', 'memberController@inviteUser');


    Route::post('/association/{assoc_id}/membre/{membre}/{state}', 'memberController@changeState');

    Route::get('/association/{assoc_id}/membre/{membres_id}', 'memberController@getStatistiques');

    Route::get('/member/{id}', 'memberController@getMemberById');
});


Route::group(['middleware' => ['auth:api']], function () {

    //MemberHasUser routes
    Route::get('/memberhasuser/{id}', 'membreHasUserController@getMemberHasUserById');

    Route::post('/memberhasuser', 'membreHasUserController@createMemberHasUser');
});

//Cycles routes
Route::group(['middleware' => ['auth:api']], function () {

    //Listes des cycles d'une association
    Route::get('/association/{assocId}/cycle', 'cycleController@getCycleByAssociationId');

    // Creation d'un nouveau cycle dans une association.
    Route::post('/association/{assocId}/cycle', 'cycleController@createCycle');

    //Extraire le detail sur un cycle d'une association
    Route::get('/association/{assocId}/cycle/{cycleId}', 'cycleController@getCycleByAssociation');
    //Efface ou enleve un cycle
    Route::delete('/association/{assocId}/cycle/{id}', 'cycleController@deleteCycle');

    //update du cycle
    Route::post('/association/{assocId}/cycle/{id}', 'cycleController@updateCycle');

    //Efface ou enleve plusieurs cycles d'une association
    Route::post('/association/{assocId}/cycles/delete', 'cycleController@deleteCycles');


    Route::get('/cycle/{cycle_id}/activate', 'cycleController@activate');
    Route::get('/cycle/{cycle_id}/desactivate', 'cycleController@desactivate');

    //Lire la liste des ags
    Route::get('/association/{assocId}/cycle/{cycleId}/ags', 'agsController@getCycleAssociationAgs');

    //Genere la liste des assemblees générale dans un cycle donnée
    Route::post('/association/{assocId}/cycle/{cycleId}/ags', 'agsController@createAgs');

    //Details d'une assemblee generale
    Route::get('/association/{assocId}/cycle/{cycleId}/ag/{id}', 'agsController@getAgById');

    //Mise à jours de l'assemblée generale (AGs) dans un cycle donnée
    Route::post('/association/{assocId}/cycle/{cycleId}/ag/{id}', 'agsController@updateAgs');

    //tout les ags d'un cycle
    Route::get('/cycle/{cycle_id}/ags', 'agsController@getCyclesAgs');

    //changer l'hote
    Route::get('/ag/{ag_id}/assign/{member_id}', 'agsController@changerHote');

    //permutter deux ags
    Route::post('/ags/permutation', 'agsController@permuterAg');

    //permutter deux ags
    Route::post('/ags/permutation', 'agsController@permuterAg');

    Route::get('/ag/{ag_id}/cloture', 'agsController@clotureAgs');
});

//News Router
Route::group(['middleware' => ['auth:api']], function () {

    //Listes des nouvelles d'une association
    Route::get('/association/{assocId}/nouvelle/{page?}', 'NewsController@getAssociationNews');

    // Creation d'une nouvelle dans une association.
    Route::post('/association/{assocId}/nouvelle', 'NewsController@createNews');

    Route::post('/association/{assocId}/nouvelle/{id}', 'NewsController@updateNew');

    // Efface ou enleve une nouvelle dans une association.
    Route::delete('/association/{assocId}/nouvelle/{id}', 'NewsController@deleteNew');

    // Liker une nouvelle
    Route::get('/association/{assocId}/nouvelle/{nouvId}/membre/{membres_id}/like', 'NewsController@likeNews');

    // Disliker une nouvelle
    Route::get('/association/{assocId}/nouvelle/{nouvId}/membre/{membres_id}/dislike', 'NewsController@disLikeNews');

    // Commenter une nouvelle
    Route::post('/association/{assocId}/nouvelle/{nouvId}/comment', 'NewsCommentController@createComment');

    //publier une nouvelle par un admin
    Route::get("/association/{assocId}/nouvelle/{new}/publier", "NewsController@publier");
});

Route::group(['prefix' => 'auth'], function () {
    Route::get('signup/activate/{token}', 'UserController@signupActivate');
});


Route::middleware('auth:api')->group(function () {


    Route::get('/association/{assocId}/activite/all', 'ActiviteController@allIndex');
    Route::get('/association/{assocId}/activites/tresorerie', 'ActiviteController@getTresorerie');
    Route::get('/association/{assocId}/activite/{activite}/state/{state}', 'ActiviteController@changeState');
    Route::get('/association/{assocId}/activite/type/{type}', 'ActiviteController@typeShow');
    Route::get('/association/{assocId}/activite/type/{type}/ag/{ags_id}', 'ActiviteController@typeShowAg');
    Route::get('/association/{assocId}/activite/{activity_id}/{type}', 'ActiviteController@typeActivityShow');
    Route::get('/association/{assocId}/membre/{member}/activites', 'ActiviteController@getActivitiesByMember');
    Route::post('/association/{assocId}/activite/{activity_id}/{type}', 'ActiviteController@typeActivityStore');
    Route::post('/association/{assocId}/activite/{activity_id}/{type}/{type_id}', 'ActiviteController@typeActivityUpdate');
    Route::post('/association/{assocId}/cloturer/activite/{activites_id}', 'ActiviteController@clotureActivite');
    Route::delete('/association/{assocId}/activite/{activites_id}/membre/{membres_id}', 'ActiviteController@noGoToEvent');
    Route::resource('/association/{assocId}/activite', 'ActiviteController')->only(['index', 'store', 'show', 'update', 'destroy']);
});


Route::group(['middleware' => ['auth:api']], function () {

    Route::resource('/association/{assocId}/document', 'DocumentController')->only(['index', 'store', 'show', 'destroy']);
    Route::get('/association/{assocId}/documents/size', 'DocumentController@getUsedSize');
    Route::get('/association/{assocId}/document/{document}/download', 'DocumentController@download');
});

Route::group(['middleware' => ['auth:api']], function () {

    Route::post('/association/{assoc_id}/membre/{membe_id}/user/{user_id}/assignrole', 'RolesController@assignRole');
    Route::post('/association/{assoc_id}/membre/{membe_id}/user/{user_id}/removerole', 'RolesController@removeRole');


    Route::get('/association/{assoc_id}/user/{user_id}/roles', 'RolesController@getRoles');
});

Route::group(['prefix' => 'role'], function () {
    Route::post('/add', 'RolesController@addRole');
    Route::get('/get', 'RolesController@allRoles');
});


Route::group(['middleware' => ['auth:api']], function () {

    //operations et transactions
    Route::resource('membre/{member_id}/operation', 'OperationController')->only(['index', 'show', 'destroy']);
    Route::get('operation/{operation}', 'OperationController@showUniqueId');
    Route::post('membre/{member_id}/operation/{operation}', 'OperationController@update');

    Route::post('{role}/{member_id}/operation', 'OperationController@store');
    Route::post('{role}/{member_id}/operation/en/seance', 'OperationController@storeEnSeance');

    Route::get('membre/{member_id}/operation/{operation}/transaction/validate', 'OperationController@validateTransactions');
    Route::get('membre/{member_id}/operation/{operation}/transaction/rejeter', 'OperationController@rejeterTransactions');
    Route::get('association/{assoc_id}/operations', 'OperationController@getAssociationOperation');

    Route::resource('operation/{operation}/transaction', 'TransactionController')->only(['store', 'show']);

    //comptes
    Route::resource('activite/{activite}/membre/{membre}/register', 'CompteController')->only(['store']);
    Route::post('activite/{activite}/membre/{membre}/register/one', 'CompteController@storeOneElse');
    Route::post('activite/{activite}/membre/{membre}/update/{id}', 'CompteController@update');
    Route::post('activite/membres/register', 'CompteController@storeMultiple');
    Route::post('activite/membres/update', 'CompteController@updateMultiple');
    Route::post('activite/membres/comptes/delete', 'CompteController@multipleDestroy');
    Route::post('compte/{comptes_id}/assigner/avoir', 'CompteController@assignerAvoir');
    Route::post('compte/{comptes_id}/ajouter/avoir', 'CompteController@ajouterAvoir');
    Route::get('activite/{activite}/comptes/all', 'CompteController@getAll');
    Route::get('/member/{member_id}/comptes', 'CompteController@getCompteByMember');
    Route::get('/association/{assocId}/comptes', 'CompteController@getCompteByAssociation');
    Route::get('activite/{activites_id}/member/{member_id}/compte', 'CompteController@getCompteByActivityAndMember');

    //echeanciers
    Route::resource('activite/{activite}/echeancier', 'EcheancierController')->only(['store', 'destroy']);
    Route::post('activite/{activite}/echeancier/some', 'EcheancierController@storeSome');
    Route::post('activite/{activite}/echeanciers/somes/all/members', 'EcheancierController@storeEcheancesForSomeCompte');
    Route::post('activite/{activite}/echeanciers/somes/some/members', 'EcheancierController@storeSomeEcheancesForSomeCompte');
    Route::get('activite/{activite}/echeancier/active', 'EcheancierController@getActive');
    Route::get('/member/{member_id}/echeances', 'EcheancierController@getEcheancesByMember');
    Route::get('/member/all/association/{assocId}/echeances', 'EcheancierController@getEcheancesForAllMembers');
    Route::get('/member/all/association/{assocId}/echeances/ag/{ags_id}', 'EcheancierController@getEcheancesForAllMembersAtAgs');
    Route::get('/member/all/association/{assocId}/echeances/ag/{ags_id}/decaissement', 'EcheancierController@getEcheancesDecaissementForAllMembersAtAgs');
});


Route::group(['middleware' => ['auth:api']], function () {


    //virements
    Route::post('activite/{activite_id}/compte/{compte_id}/attribution', 'VirementController@attribution');
    Route::post('activite/{activite_id}/compte/{compte_id}/acquitement', 'VirementController@acquitement');

    //transferts
    Route::post('comptes/solde/transfert', 'TransfertController@transfert');
});


Route::group(['middleware' => ['auth:api']], function () {

    Route::get('association/{assocId}/tontine/{tontine_id}/echeanciers', 'TontineController@echeanciers');
    Route::get('association/{assocId}/tontine/{tontine_id}/calendrier', 'TontineController@calendrier');
    Route::get('association/{assocId}/tontine/{tontine_id}/tirage', 'TontineController@tirage');
    Route::get('association/{assocId}/tontine/{tontine_id}/lots', 'TontineController@lots');
    Route::delete('tontine/{tontine_id}/lot/{lot_id}/delete', 'TontineController@deleteLotSecondaire');
    Route::delete('tontine/activite/{activites_id}/compte/{comptes_id}/delete', 'TontineController@deleteCompte');
    Route::post('association/{assocId}/tontine/{tontine_id}/assignation', 'TontineController@assignation');
    Route::post('association/{assocId}/tontine/{tontine_id}/assignation/principal', 'TontineController@assignationLotPrincipal');
    Route::post('association/{assocId}/tontine/{tontine_id}/desassignation/principal', 'TontineController@desassignationLotPrincipal');
    Route::post('association/{assocId}/tontine/{tontine_id}/assignation/single', 'TontineController@assignationSingle');
    Route::post('association/{assocId}/tontine/{tontine_id}/permutation', 'TontineController@permutation');
    Route::post('association/{assocId}/tontine/{tontine_id}/change/date/lot', 'TontineController@changeDateLotTontine');
    Route::post('association/{assocId}/tontine/{tontine_id}/echeanciersTontineVariable', 'TontineController@echeanciersTontineVariable');
});

Route::group(['middleware' => ['auth:api']], function () {

    //presence à une ag
    Route::post('association/{assocId}/presence/create', 'PresenceController@store');
    Route::post('association/{assocId}/presence/update', 'PresenceController@updatePresence');
    Route::get('association/{assocId}/ag/{ag_id}/presence', 'PresenceController@index');
    Route::get('association/{assocId}/membre/{membre_id}/ag/{ag_id}/presence', 'PresenceController@show');
    Route::get('association/{assocId}/membre/{membre_id}/presence', 'PresenceController@showAssoc');


    //presence à un evenement
    Route::post('association/{assocId}/presence/evenement/create', 'PresenceEvenementController@store');
    Route::post('association/{assocId}/presence/evenement/update', 'PresenceEvenementController@updatePresence');
    Route::get('association/{assocId}/evenement/{evt_id}/presence/evenement', 'PresenceEvenementController@index');
    Route::get('association/{assocId}/membre/{membre_id}/evenement/{evt_id}/presence/evenement', 'PresenceEvenementController@show');
    Route::get('association/{assocId}/membre/{membre_id}/presence/evenement', 'PresenceEvenementController@showAssoc');
});


Route::group(['middleware' => ['auth:api']], function () {

    //factures
    Route::get('association/{assocId}/facture/all', 'FactureController@getAllFacture');
    Route::get('association/{assocId}/facture/{id}', 'FactureController@getFactureById');
    Route::get('association/{assocId}/cycle/{cycle}/facture/waiting', 'FactureController@getFacture');
    Route::get('association/{assocId}/cycle/{cycle_id}/facture/{facture_id}/buy', 'FactureController@buyFacture');
    Route::get('association/{assocId}/cycle/{cycle_id}/facture/{facture_id}/apply/coupon/{coupon}', 'FactureController@applyCoupon');


    //categories notifications

    Route::get('user/{user_id}/categories/notifications', 'CategoriesNotificationController@show');
    Route::get('users/categories/notifications', 'CategoriesNotificationController@index');
    Route::post('user/{user_id}/categories/notifications', 'CategoriesNotificationController@setCategoriesToUser');

    //token user
    Route::post('user/{user_id}/add/token/{token}', 'TokenController@addToken');




    //mutuelle
    //récupérer toutes les mutuelles d'une association
    Route::get('association/{assoc_id}/mutuelles', 'MutuelleController@getAllMutuellesAssociation');
    //récupérer tous les credits d'une association
    Route::get('association/{assoc_id}/credits', 'MutuelleController@getAllCreditsAssociation');
    //récupérer toutes les mutuelles d'une activité
    Route::get('activite/{activites_id}/mutuelles', 'MutuelleController@getAllMutuellesActivite');
    //récupérer toutes les mises de fonds d'une mutuelle
    Route::get('mutuelle/{mutuelle_id}/misedefonds', 'MutuelleController@getAllMiseMutuelle');
    //récupérer tout les credits d'une mutuelle
    Route::get('mutuelle/{mutuelle_id}/credits', 'MutuelleController@getAllCreditMutuelle');
    //approuver le credit
    Route::get('mutuelle/credit/{credit_id}/approuve', 'MutuelleController@approuveCredit');
    //rejeter le credit
    Route::get('activite/{activites_id}/mutuelle/credit/{credit_id}/reject', 'MutuelleController@rejectCredit');
    //rejeter le credit
    Route::get('activite/{activites_id}/mutuelle/credit/{credit_id}/annuler', 'MutuelleController@annulerCredit');
    //preview des echeances de credit
    Route::post('mutuelle/{mutuelle_id}/credit/preview/echeance', 'MutuelleController@previewEcheanceCredit');
    //ajouter un credit à une mutuelle
    Route::post('activite/{activites_id}/mutuelle/{mutuelle_id}/credit', 'MutuelleController@addCredit');
    //update un credit à une mutuelle
    Route::post('activite/{activites_id}/mutuelle/{mutuelle_id}/credit/{credit_id}/update', 'MutuelleController@updateCredit');
    //ajouter une mise de fonds à une mutuelle
    Route::post('membre/{membres_id}/mutuelle/{mutuelle_id}/misedefond', 'MutuelleController@addMise');
    //ajouter plusieurs credits à une mutuelle
    Route::post('activite/{activites_id}/mutuelle/{mutuelle_id}/credits', 'MutuelleController@addMultipleCredit');
    //ajouter plusieurs mises de fonds à une mutuelle
    Route::post('mutuelle/{mutuelle_id}/misesdefonds', 'MutuelleController@addMultipleMise');
    //ajouter plusieurs mises de fonds à une mutuelle
    Route::post('mutuelle/{mutuelle_id}/misesdefonds/csv', 'MutuelleController@addMiseCsvFile');
    //ajouter un credit en cours à une mutuelle
    Route::post('activite/{activites_id}/mutuelle/{mutuelle_id}/credit/pending', 'MutuelleController@addCreditPending');
    //ajouter plusieurs credits en cours à une mutuelle
    Route::post('activite/{activites_id}/mutuelle/{mutuelle_id}/credits', 'MutuelleController@addMultipleCreditPending');
    //suppression d'une mutuelle
    Route::delete('mutuelle/{mutuelle_id}/delete', 'MutuelleController@deleteMutuelle');
    //suppression d'une mise de fonds
    Route::delete('mutuelle/mise/{mise_id}/delete', 'MutuelleController@deleteMise');
    //suppression d'un credit
    Route::delete('mutuelle/credit/{credit_id}/delete', 'MutuelleController@deleteCredit');
    //suppression credit en cours
    Route::delete('activite/{activites_id}/credit/{credit_id}/delete/pending', 'MutuelleController@deleteCreditPending');




    //rapport et sections
    //rapports d'une association
    Route::get('association/{assocId}/ag/{ags_id}/rapport/pdf', 'RapportController@showForPdfRapport');

    Route::get('association/{assocId}/rapports', 'RapportController@index');
    //rapports d'une assemblée générale
    Route::get('association/{assocId}/ag/{ags_id}/rapport', 'RapportController@showRapport');
    //enregistrer un rapport
    Route::post('association/{assocId}/ag/{ags_id}/rapport', 'RapportController@storeRapport');
    //update un rapport
    Route::post('association/{assocId}/rapport/{rapport_id}/update', 'RapportController@updateRapport');
    //upload file past rapport
    Route::post('association/{assocId}/{ags_id}/rapport/upload', 'RapportController@uploadRapportPast');
    //sections
    //enregistrer une section
    Route::post('association/{assocId}/rapport/{rapport_id}/section', 'RapportController@storeSection');
    //update une section
    Route::post('association/{assocId}/section/{section_id}/update', 'RapportController@updateSection');
    //delete section
    Route::delete('association/{assocId}/section/{section_id}/delete', 'RapportController@deleteSection');


    //sanction
    //enregistrer une sanction
    Route::post('association/{assocId}/sanction', 'SanctionController@storeSanction');
    //enregistrer un type de sanction
    Route::post('association/{assocId}/type/sanction', 'SanctionController@storeTypeSanction');
    //update un type de sanction
    Route::post('association/{assocId}/type/sanction/{type_id}/update', 'SanctionController@updateTypeSanction');
    //récupérer les sanctions
    Route::get('ags/{ags_id}/sanctions', 'SanctionController@indexSanction');
    //récupérer les types de sanction
    Route::get('association/{assocId}/type/sanctions', 'SanctionController@indexTypeSanction');
    //delete sanction
    Route::delete('association/{assocId}/sanctions/{sanctionId}/delete', 'SanctionController@deleteSanction');
    //delete type sanction
    Route::delete('association/{assocId}/type/sanctions/{typesanctionId}/delete', 'SanctionController@deleteTypeSanction');

    //solidarites
    //tout les types d'assistance d'une association
    Route::get('association/{assocId}/types/assistances', 'SolidariteController@indexTypeAssistance');
    //tout les assistances d'une acticite
    Route::get('activite/{activite_id}/assistances', 'SolidariteController@indexAssistance');
    //enregistrer un un type d'assistance
    Route::post('association/{assocId}/types/assistances', 'SolidariteController@storeTypeAssistance');
    //enregistrer une assistance
    Route::post('activite/{activite_id}/membre/{membre_id}/assistances', 'SolidariteController@storeAssistance');
    //enregistrer une assistance Past
    Route::post('activite/{activite_id}/membre/{membre_id}/assistances/past', 'SolidariteController@storeAssistancePast');
    //supprimer un type d'assistance
    Route::delete('assistance/type/{type_assistance}/delete', 'SolidariteController@deleteTypeAssistance');
    //supprimer un assistance
    Route::delete('assistance/{assistance_id}/delete', 'SolidariteController@deleteAssistance');

    
    Route::get("wallet/association/{assocId}/awalletget/{id}", "WalletController@getAwalletByIdAndAssocId");
    Route::get("wallet/user/{user_id}/uwalletget", "WalletController@getUWallet");
    Route::get("wallet/user/{user_id}/uwalletget/{id}", "WalletController@getUwalletByWalletId");
    Route::get("wallet/user/{user_id}/setdefault/{u_wallet_id}/association/{assocId}", "WalletController@setDefaultUWallet");
    Route::post("wallet/{type}/store", "WalletController@storeWallet");
    Route::delete("wallet/{wallet_id}/delete", "WalletController@deleteWallet");
    Route::post("wallet/{wallet_id}/update", "WalletController@updateWallet");
    Route::get("wallet/{wallet_id}/cashinout", "WalletController@getCashInOut");
    Route::get("wallet/{wallet_id}/wtransaction", "WalletController@getWTransactions");
    Route::get("wallet/{wallet_id}/association/{assocId}/wtransaction", "WalletController@getWTransactionsWalletAssociation");
    Route::post("wallet/cashinout/type_paiement/{type}/send", "WalletController@cashInOut");
    Route::post("wallet/source/{wallets_id_source}/destination/{wallets_id_destination}/{type}", "WalletController@WTransaction");
    Route::post("wallet/source/{wallets_id_source}/destination/{wallets_id_destination}/{type}/preview", "WalletController@PreviewWTransaction");



    Route::get("coupon/getAll", "CouponController@getAllCoupons");
    Route::get("coupon/getAll/active", "CouponController@getAllActiveCoupons");
    Route::get("coupon/miseaniveau", "CouponController@miseANiveauCoupons");
    Route::post("coupon/store", "CouponController@storeCoupon");

   
});

Route::get("wallet/cashinout/{cash_id}/type_paiement/{type}/success", "WalletController@cashInOutSuccess");
Route::get("wallet/cashinout/{cash_id}/type_paiement/{type}/error", "WalletController@cashInOutError");

// });
