<?php

namespace App\Http\Controllers;

use App\CustomModels\ActiviteMethods;
use Illuminate\Http\Request;
use App\CustomModels\RapportMethods;
use App\CustomModels\EcheancesMethods;
use App\CustomModels\NewsMethods;
use App\CustomModels\PresenceMethods;
use App\CustomModels\SanctionMethods;
use App\CustomModels\AssociationMethods;
use App\CustomModels\AgMethods;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
// use PDF;
// use Spatie\Browsershot\Browsershot;

use App\Models\Ag;
// use ChromePDF;
// use SnappyPDF;
// use MPDF;
use App\Services\Pdf;
use Markdownify\Converter;

use Validator;

class RapportController extends Controller
{
    //

    protected $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    public function index($assocId)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $rapports = RapportMethods::indexRapport($assocId);
        if ($rapports['status'] == "OK") {
            return response()->json($rapports, 200);
        } else if ($rapports['data']['errNo'] == 15) {
            return response()->json($rapports, 404);
        } else {
            return response()->json($rapports, 500);
        }
    }

    public function showRapport($assocId, $ags_id)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $rapports = RapportMethods::showRapport($ags_id);

        if ($rapports['status'] == "OK") {

            $news = NewsMethods::getAssociationNewsReturnData($assocId);

            if ($news['status'] == "OK") {
                $rapports['data']['nouvelle'] = $news['data'];
            }
            $presences = PresenceMethods::checkAssociationPresenceAll($assocId, $ags_id);
            if ($presences['status'] == "OK") {
                $rapports['data']['presences'] = $presences['data'];
            }
            $sanctions = SanctionMethods::indexSanction($ags_id);
            if ($sanctions['status'] == "OK") {
                $rapports['data']['sanctions'] = $sanctions['data'];
            }
            $sf = EcheancesMethods::getEcheancesForAllMembersAtAgs($assocId, $ags_id);
            $dec = EcheancesMethods::getEcheancesDecaissementForAllMembersAtAgs($assocId, $ags_id);

            if ($sf['status'] == "OK") {
                $rapports['data']['situation_financiere'] = $sf['data'];
            }

            if ($dec['status'] == "OK") {
                $rapports['data']['decaissement'] = $dec['data'];
            }
            $rapports['data']['association'] = $association;
            $ag = AgMethods::getById($ags_id);
            if ($ag != "not found") {
                $rapports['data']['ag'] = $ag;
            }

            $caisse = ActiviteMethods::getTresorerieByAssociationsData($assocId);
            $rapports['data']['caisse'] = $caisse;

            return response()->json($rapports, 200);
        } else if ($rapports['data']['errNo'] == 15) {
            return response()->json($rapports, 404);
        } else {
            return response()->json($rapports, 500);
        }
    }

    public function showForPdfRapport($assocId, $ags_id)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $rapports = RapportMethods::showRapport($ags_id);

        if ($rapports['status'] == "OK") {

            $news = NewsMethods::getAssociationNewsReturnData($assocId);

            if ($news['status'] == "OK") {
                $rapports['data']['nouvelle'] = $news['data'];
            }
            $presences = PresenceMethods::checkAssociationPresenceAll($assocId, $ags_id);
            if ($presences['status'] == "OK") {
                $rapports['data']['presences'] = $presences['data'];
            }
            $sanctions = SanctionMethods::indexSanction($ags_id);
            if ($sanctions['status'] == "OK") {
                $rapports['data']['sanctions'] = $sanctions['data'];
            }
            $sf = EcheancesMethods::getEcheancesForAllMembersAtAgs($assocId, $ags_id);
            $dec = EcheancesMethods::getEcheancesDecaissementForAllMembersAtAgs($assocId, $ags_id);
            if ($sf['status'] == "OK") {
                $rapports['data']['situation_financiere'] = $sf['data'];
                $encaissement_attendu = 0;
                $encaissement_Total = 0;
                foreach ($sf['data'] as $key => $ech) {
                    $encaissement_attendu += $ech['montant_attendu_init'];
                    foreach ($ech['transactions'] as $key => $value) {
                        $encaissement_Total += $value->montant;
                    }
                }

                $rapports['data']['encaissement_attendu'] = $encaissement_attendu;
                $rapports['data']['encaissement_total'] = $encaissement_Total;
            }

            if ($dec['status'] == "OK") {
                $rapports['data']['decaissement'] = $dec['data'];
                $decaissement_attendu = 0;
                $decaissement_Total = 0;
                foreach ($dec['data'] as $key => $ech) {
                    $decaissement_attendu += $ech['montant_attendu_init'];
                    foreach ($ech['transactions'] as $key => $value) {
                        $decaissement_Total += $value->montant;
                    }
                }

                $rapports['data']['decaissement_attendu'] = $decaissement_attendu;
                $rapports['data']['decaissement_total'] = $decaissement_Total;
            }



            $rapports['data']['association'] = $association;

            if ($association->langue) App::setLocale($association->langue);
            else App::setLocale('fr');

            $ag = AgMethods::getById($ags_id);
            if ($ag != "not found") {
                $rapports['data']['ag'] = $ag;
            }

            $caisse = ActiviteMethods::getTresorerieByAssociationsData($assocId);
            $rapports['data']['caisse'] = $caisse;

            return RapportMethods::generatePdf($assocId, $ags_id, $rapports);
        } else if ($rapports['data']['errNo'] == 15) {
            return $rapports;
        } else {
            return $rapports;
        }
    }

    public function storeRapport($assocId, $ags_id,  Request $request)
    {

        $validator = Validator::make($request->all(), [
            'created_by' => 'required',
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
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $rapports = RapportMethods::storeRapport($assocId, $ags_id, $request->all());
        if ($rapports['status'] == "OK") {
            return response()->json($rapports, 200);
        } else if ($rapports['data']['errNo'] == 15) {
            return response()->json($rapports, 404);
        } else {
            return response()->json($rapports, 500);
        }
    }

    public function storeSection($assocId, $rapport_id,  Request $request)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $rapports = RapportMethods::storeSection($rapport_id, $request->all());
        if ($rapports['status'] == "OK") {
            return response()->json($rapports, 200);
        } else if ($rapports['data']['errNo'] == 15) {
            return response()->json($rapports, 404);
        } else {
            return response()->json($rapports, 500);
        }
    }

    public function updateRapport($assocId, $rapport_id,  Request $request)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $rapports = RapportMethods::updateRapport($association, $rapport_id, $request->all());
        if ($rapports['status'] == "OK") {
            return response()->json($rapports, 200);
        } else if ($rapports['data']['errNo'] == 15) {
            return response()->json($rapports, 404);
        } else {
            return response()->json($rapports, 500);
        }
    }

    public function updateSection($assocId, $section_id,  Request $request)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $rapports = RapportMethods::updateSection($section_id, $request->all());
        if ($rapports['status'] == "OK") {
            return response()->json($rapports, 200);
        } else if ($rapports['data']['errNo'] == 15) {
            return response()->json($rapports, 404);
        } else {
            return response()->json($rapports, 500);
        }
    }

    public function deleteSection($assocId, $section_id)
    {
        $section = RapportMethods::deleteSection($section_id);
        if ($section['status'] == "OK") {
            return response()->json($section, 203);
        } else if ($section['data']['errNo'] == 15) {
            return response()->json($section, 404);
        } else {
            return response()->json($section, 500);
        }
    }

    public function uploadRapportPast($assocId, $ags_id, Request $request)
    {
        $rapport = RapportMethods::uploadRapportPast($assocId, $ags_id, $request->file('rapport'));
        if ($rapport['status'] == "OK") {
            return response()->json($rapport, 203);
        } else if ($rapport['data']['errNo'] == 15) {
            return response()->json($rapport, 404);
        } else {
            return response()->json($rapport, 500);
        }
    }
}
