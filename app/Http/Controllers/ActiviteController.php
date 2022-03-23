<?php

namespace App\Http\Controllers;


use App\CustomModels\ActiviteMethods;
use App\CustomModels\AssociationMethods;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class ActiviteController extends Controller
{

    public function allIndex($assocId)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activities = ActiviteMethods::getActivitiesByAssocAndDatas($assocId);

        return response()->json($activities, 200);
    }

    /**
     * retourner l'ensemble des activités par associations
     * @param $assocId qui est l'id de l'association
     */

    /**
     * @OA\Get(
     *     path="/association/{assocId/activites",
     *     description="récupération de toutes activites d'une association",
     *     @OA\Response(response="default", description="Welcome page")
     * )
     * 
     */

    public function index($assocId)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activities = ActiviteMethods::getActivitiesByAssociations($assocId);

        return response()->json($activities, 200);
    }


    /**
     * creer une activite pour une association donné
     * 
     * @param $assocId l'id de l'association
     * @param $request l'ensemble des données à envoyer
     */
    public function store(Request $request, $assocId)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in(['Tontine', 'Mutuelle', 'Evenement', 'Solidarite', 'Projet', 'Main_levee', 'Pam', 'Generique'])],
            'nom' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::createActivity($request->all(), $assocId);

        if ($activite['status'] == "OK") {
            return response()->json($activite, 201);
        }
        if ($activite['data']['errNo'] == 15) {
            return response()->json($activite, 404);
        } else {
            return response()->json($activite, 500);
        }
    }

    /**
     * retrouver une activité et ses proprietés
     *  @param $assocId l'id de l'association
     *  @param $activite l'id de l'activite
     */
    public function show($assocId, $activite)
    {

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::getActivityByIdAndData($activite);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Activity don\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $success['status'] = 'OK';
        $success['data'] =  $activite;

        return response()->json($success, 200);
    }

    public function destroy($assocId, $activite)
    {

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::getActivityById($activite);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Activity don\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }
        try {

            $del = $activite->delete();

            $success['status'] = 'OK';
            $success['data'] =  "suppression réussie";

            return response()->json($success, 200);
        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = "problems with activity creation";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return response()->json($error, 500);
        }
    }

    /**
     * update d'une activite générale
     */
    public function update(Request $request, $assocId, $activite)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::updateActivity($request->all(), $activite);

        if ($activite['status'] == 'OK') {
            return response()->json($activite, 202);
        } else if ($activite['data']['errNo'] == 15) {
            return response()->json($activite, 404);
        } else {
            return response()->json($activite, 500);
        }
    }

    /**
     * récuperer les données des activités selon un type
     */
    public function typeActivityShow($assocId, $activity_id, $type)
    {

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::getSomeActivityData($activity_id, $type);

        if ($activite['status'] == 'OK') {
            return response()->json($activite, 200);
        } else if ($activite['data']['errNo'] == 15) {
            return response()->json($activite, 404);
        }
    }


    /**
     * créer une activite particulière avec ses données basé sur une activité précise
     * 
     * @param $assocId l'id de l'association
     * @param $activity_id l'id de l'activité
     * @param $type le type de l'activite à entrer
     */
    public function typeActivityStore($assocId, $activity_id, $type, Request $request)
    {

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activity = ActiviteMethods::getActivityById($activity_id);

        if ($activity == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'activity doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'date_debut' => 'integer'
        ]);

        $validator->sometimes('type', ['required', Rule::in(['FIXE', 'VARIABLE'])], function ($type) {
            return $type == "Tontine";
        });

        $validator->sometimes('montant_part', 'required', function ($input) {
            return $input->type == "FIXE";
        });

        $validator->sometimes('montant_cagnote', 'required', function ($input) {
            return $input->type == "FIXE";
        });

        $validator->sometimes('attribution_cagnote', ['required', Rule::in(['TIRAGE', 'ENCHERE', 'VENTE'])], function ($type) {
            return $type == "Tontine";
        });

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }


        $activite = ActiviteMethods::createSomeActivity($request->all(), $activity_id, $type);

        if ($activite['status'] == 'OK') {
            return response()->json($activite, 201);
        } else if ($activite['data']['errNo'] == 15) {
            return response()->json($activite, 404);
        } else {
            return response()->json($activite, 500);
        }
    }

    /**
     * update d'un type d'activite
     */
    public function typeActivityUpdate($assocId, $activity_id, $type, $type_id, Request $request)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::updateSomeActivityType($request->all(), $type, $type_id);

        if ($activite['status'] == 'OK') {
            return response()->json($activite, 202);
        } else if ($activite['data']['errNo'] == 15) {
            return response()->json($activite, 404);
        } else {
            return response()->json($activite, 500);
        }
    }

    /**
     * recupération d'activités par type
     */
    public function typeShow($assocId, $type)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::getActivitiesByType($assocId, $type);
        return $activite;

        return response()->json($activite, 200);
    }

    public function typeShowAg($assocId, $type, $ags_id)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::getActivitiesByTypeAtAgs($assocId, $type, $ags_id);
        return $activite;

        return response()->json($activite, 200);
    }

    /**
     * ne participe pas à une activité en générale
     */
    public function noGoToEvent($assocId, $activites_id, $membres_id)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $nogo = ActiviteMethods::noGoToEvent($membres_id, $activites_id);

        if ($nogo['status'] == "OK") {
            return response()->json($nogo, 203);
        } else if ($nogo['data']['errNo'] == 15) {
            return response()->json($nogo, 500);
        } else {
            return response()->json($nogo, 404);
        }
    }

    /**
     * changer d'etat 
     */
    public function changeState($assocId, $activite, $state)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::getActivityById($activite);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Activite doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }
        $states = array("init", "actif", "inactif");
        if ($state == "" || !in_array($state, $states)) {
            $err['errNo'] = 10;
            $err['errMsg'] = 'state not valid';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }

        $nogo = ActiviteMethods::changeState($activite, $state);

        if ($nogo['status'] == "OK") {
            return response()->json($nogo, 201);
        } else if ($nogo['data']['errNo'] == 10) {
            return response()->json($nogo, 500);
        } else {
            return response()->json($nogo, 404);
        }
    }

    public function getActivitiesByMember($assocId, $member)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::getActivitiesByMember($assocId, $member);
        return response()->json($activite, 200);
    }

    public function getTresorerie($assocId)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::getTresorerieByAssociations($assocId);
        return response()->json($activite, 200);
    }

    public function clotureActivite($assocId, $activites_id, Request $request)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $activite = ActiviteMethods::clotureGeneralActivite($activites_id, $request->all());

        if ($activite['status'] == "OK") {
            return response()->json($activite, 201);
        } else if ($activite['data']['errNo'] == 10) {
            return response()->json($activite, 500);
        } else {
            return response()->json($activite, 400);
        }
    }
}
