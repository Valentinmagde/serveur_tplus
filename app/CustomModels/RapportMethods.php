<?php

namespace App\CustomModels;

use App\Custom\Config;
use App\Models\Rapport;
use App\Models\Ag;
use App\Models\Section;
use App\Models\Document;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;

class RapportMethods
{

    public static function getJitsiRoomByMemberId($membre_id){
        $ag = AgMethods::getCurrentAg($membre_id);
        if($ag == "not found") return null;

        $rapport = Rapport::where('ags_id', $ag->id)->first();
        if($rapport) return $rapport->jitsi_room;

        return null;
    }

    public static function getRapportById($id)
    {
        $rapport = Rapport::find($id);
        if ($rapport) return $rapport;

        return "not found";
    }

    public static function getSectionById($id)
    {
        $section = Section::find($id);
        if ($section) return $section;

        return "not found";
    }


    public static function showRapport($ags_id)
    {
        $rapport = Rapport::where('ags_id', $ags_id)->first();

        if ($rapport) {
            $sections = Section::where('rapports_id', $rapport->id)->get();
            $rapport['sections'] = $sections;
            $sec = MembreMethods::getById($rapport->secretaire);
            $presidence = MembreMethods::getById($rapport->presidence);
            if ($presidence != "not found") {
                $rapport['presidence_name'] = $presidence->firstName . ' ' . $presidence->lastName;
            }
            if ($sec != "not found") {
                $rapport['secretaire_name'] = "$sec->firstName $sec->lastName";
            }
            return array(
                "status" => "OK",
                "data" => $rapport
            );
        }

        $err['errNo'] = 15;
        $err['errMsg'] = "rapport of general assembly {$ags_id} doesn't exist";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
    }

    /**
     * get all rapports of 
     */
    public static function indexRapport($assocId)
    {


        $cycle = CycleMethods::checkActifCycle($assocId);
        if ($cycle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "association {$assocId} doesn't have active cycle";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $ags = AgMethods::getByIdCycle($cycle->id);

        $rapports = array();
        foreach ($ags as $key => $ag) {
            $rapport = Rapport::where('ags_id', $ag->id)->first();

            if ($rapport) {
                $sections = Section::where('rapports_id', $rapport->id)->get();
                $rapport['sections'] = $sections;

                $rapports[] = $rapport;
            }
        }

        return array(
            "status" => "OK",
            "data" => $rapports
        );
    }

    /**
     * store rapport record
     */
    public static function storeRapport($assocId, $ags_id, $rapport)
    {

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $ag = AgMethods::getById($ags_id);
        if ($ag == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "ag {$ags_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $rap = Rapport::where('ags_id', $ags_id)->first();
        if ($rap) {
            $success['status'] = "OK";
            $success['data'] = $rap;

            return $success;
        }

        if(array_key_exists('jitsi_room', $rapport)){
            if($rapport['jitsi_room'] == "OK"){
                $uuid = Config::uuidv4();
                $rapport['jitsi_room'] = "https://meet.jit.si/TontinePlus-{$association->nom}-$uuid";
            }
        }

        $rapport['ags_id'] = $ags_id;
        $rapport['created_at'] = DateMethods::getCurrentDateInt();

        try {

            $rapport = Rapport::create($rapport);

            $membres = MembreMethods::getMemberAssociation($assocId);
            $presences = PresenceMethods::checkAssociationPresenceAll($assocId, $ags_id);
            if ($presences['status'] ==  'OK') {
                foreach ($membres as $key => $membre) {
                    $i = 0;
                    foreach ($presences['data'] as $key => $presence) {
                        if ($presence->membres_id == $membre->id) {
                            $i++;
                        }
                    }

                    if ($i == 0) {
                        PresenceMethods::addPresence($membre->id, $ags_id, 'present', '');
                    }
                }
            }
            
            $sec = MembreMethods::getById($rapport->secretaire);
            $presidence = MembreMethods::getById($rapport->presidence);
            if ($presidence != "not found") {
                $rapport['presidence_name'] = $presidence->firstName . ' ' . $presidence->lastName;
            }
            if ($sec != "not found") {
                $rapport['secretaire_name'] = "$sec->firstName $sec->lastName";
            }

            $success['status'] = "OK";
            $success['data'] = $rapport;

            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * store section of rapport
     */
    public static function storeSection($rapport_id, $section)
    {
        $rapport = RapportMethods::getRapportById($rapport_id);
        if ($rapport == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "rapport {$rapport_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $section['rapports_id'] = $rapport_id;
        $section['created_at'] = DateMethods::getCurrentDateInt();

        try {

            $section = Section::create($section);
            $success['status'] = "OK";
            $success['data'] = $section;

            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * update one section
     */
    public static function updateSection($section_id, $data)
    {
        $section = RapportMethods::getSectionById($section_id);
        if ($section == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "section {$section_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {
            $data['updated_at'] = DateMethods::getCurrentDateInt();
            $section->fill($data);
            $section->save();

            return array(
                "status" => "OK",
                "data" => $section
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * update rapport
     */
    public static function updateRapport($association, $rapport_id, $data)
    {
        $rapport = RapportMethods::getRapportById($rapport_id);
        if ($rapport == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "rapport {$rapport_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {


            if(array_key_exists('jitsi_room', $data)){
                if($data['jitsi_room'] == "OK"){
                    $uuid = Config::uuidv4();
                    $data['jitsi_room'] = "https://meet.jit.si/TontinePlus-{$association->nom}-$uuid";
                }
            }

            $data['updated_at'] = DateMethods::getCurrentDateInt();
            $rapport->fill($data);
            $rapport->save();

            return array(
                "status" => "OK",
                "data" => $rapport
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    public static function deleteSection($id)
    {

        $section = Section::find($id);
        if (!$section) {
            $err['errNo'] = 15;
            $err['errMsg'] = "section $id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            $section->delete();

            return array(
                "status" => "OK",
                "data" => "successfull deleted"
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    public static function uploadRapportPast($assocId, $ags_id, $file)
    {
        $ag = AgMethods::getById($ags_id);
        if ($ag == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "ag $ags_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $date = gmdate('Y-m-d H:m', $ag->date_ag);
        $file_uploaded = FileUpload::associationFileUpload($file, $assocId, "document");
        if ($file_uploaded != "error") {
            if ($file_uploaded != "no space") {
                $ag->fill([
                    "file" => url($file_uploaded)
                ]);
                $ag->save();
                return array(
                    "status" => "OK",
                    "data" => $ag
                );
            } else {
                $err['errNo'] = 18;
                $err['errMsg'] = "plus d'espace de données";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } else {
            $err['errNo'] = 11;
            $err['errMsg'] = "erreur de sauvegarde du fichier";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    public static function generatePdf($assocId, $ags_id,  $rapports)
    {
        $system = new Filesystem();


        //traduction

        $trans_rapport = __('content.Rapport_de_séance');
        $trans_footer_left = __('content.generate');
        $trans_header_right = __('content.rapport_du');
        $trans_hote = __('content.Hôte');
        $trans_date = __('content.Date');
        $trans_lieu = __('content.Lieu');
        $trans_presidence = __('content.Présidence');
        $trans_secretaire = __('content.Sécrétaire');
        $trans_presences_membre = __('content.Présences_des_membres');
        $trans_present = __('content.present');
        $trans_absent = __('content.absent');
        $trans_excuse = __('content.excuse');
        $trans_retard = __('content.retard');
        $trans_membre = __('content.Membre');
        $trans_status = __('content.status');
        $trans_ordre = __('content.Ordre_du_jour');
        $trans_statistique = __('content.statistique');
        $trans_finance = __('content.Finances');
        $trans_encaisse = __('content.encaissé_attendu');
        $trans_decaisse = __('content.décaissé');
        $trans_total = __('content.total_en_caisse');
        $trans_situation = __('content.Situation_financière');
        $trans_decaissement = __('content.Décaissement');
        $trans_attendu = __('content.Montant_Attendu');
        $trans_realise = __('content.Montant_Réalisé');


        $path = "upload/association/{$assocId}/document/";
        $isDir = $system->isDirectory(storage_path("app/public/{$path}"));
        if (!$isDir) {
            $dir = $system->makeDirectory(storage_path("app/public/{$path}/"), 0777, true, true);
        }
        $date = gmdate('Y-m-d H:m', $rapports['data']['ag']['date_ag']);
        $name = "{$rapports['data']['ag']['date_ag']}-$ags_id.pdf";

        $full = storage_path("app/public/{$path}") . "$name";
        $system->delete($full);

        touch($full);

        $file = public_path("{$rapports['data']['ag']['date_ag']}-$ags_id.md");
        touch($file);
        $current = file_get_contents($file);
        $data = $rapports['data'];
        $background = public_path('Background3.pdf');

        $current .= "---\n";
        $current .= 'title: "' . $data['association']['nom'] . '"';
        $current .= "\n";
        $current .= "author: [\"{$data['secretaire_name']}\"]\n";
        $current .= "date: \"$trans_rapport {$date}\"\n";

        $current .= "subject: \"Markdown\"\n";
        $current .= "keywords: [Markdown, Example]\n";
        $current .= "subtitle: \"{$data['association']['description']}\"\n";
        $current .= "lang: \"en\"\n";
        $current .= "titlepage: true,\n";
        $current .= "titlepage-rule-color: \"360049\"\n";
        $current .= "titlepage-background: \"Background3.pdf\"\n";
        // $current .= "footer-left: \"\\\\hspace{1cm}\"\n";
        $current .= "footer-left: \"$trans_footer_left\"\n";
        $current .= "footer-right: \"Page \\\\thepage\"\n";
        $current .= "header-left: \"\\\\thetitle\"\n";
        // $current .= "footer-center: \"Tontine.Plus\"\n";
        $current .= "header-right: \"$trans_header_right {$date}\"\n";
        $current .= "logo-width: 300\n";

        $logo = $data['association']['logo'];
        if ($logo) {
            $logo = parse_url($logo);
            $logo = $logo['path'];
            $logo = public_path($logo);
            if (file_exists($logo))
                $current .= "logo: \"$logo\"\n";
        }

        $current .= "...\n";
        $current .= "\n\n";



        $current .= "\n";
        $current .= "|  |  |\n";
        $current .= "| ----------- | ----------- |\n";
        $current .= "| **{$trans_hote}** | {$data['hote']} |\n";
        $date_eff = gmdate('Y-m-d H:m', $data['date_effective']);
        $current .= "| **{$trans_date}** | $date_eff |\n";
        $current .= "| **{$trans_lieu}** | {$data['lieu']} |\n";
        $current .= "| **{$trans_presidence}** | {$data['presidence_name']} |\n";
        $current .= "| **{$trans_secretaire}** | {$data['secretaire_name']} |\n";




        $current .= "\n\n";
        $current .= "### $trans_presences_membre\n";
        $current .= "\n\n";
        $current .= "| $trans_membre | $trans_status |\n";
        $current .= "| ----------- | ----------- |\n";
        foreach ($data['presences'] as $presence) {
            $current .= "| {$presence['membre']} | {$presence['status']} |\n";
        }
        $current .= "\n\n";




        $current .= "\n\n";
        $current .= "### $trans_ordre\n";
        $current .= "\n";
        $current .= "1. $trans_statistique\n";
        foreach ($data['sections'] as $key => $section) {
            $current .= "1. {$section['titre']}\n";
        }
        $current .= "1. $trans_finance\n";
        $current .= "\n\n";



        $current .= "### **{$trans_statistique}**\n";
        $current .= "\n";
        $current .= "```sh\n";
        $current .= "{$data['association']['devise']} {$data['encaissement_total']} $trans_encaisse {$data['association']['devise']} {$data['encaissement_attendu']} attendu\n";
        $current .= "```\n";
        $current .= "```sh\n";
        $current .= "{$data['association']['devise']} {$data['decaissement_total']} $trans_decaisse {$data['association']['devise']} {$data['decaissement_attendu']} attendu\n";
        $current .= "```\n";
        $current .= "```sh\n";
        $current .= "$trans_total {$data['association']['devise']} {$data['caisse']}\n";
        $current .= "```\n";
        $current .= "\n\n";


        foreach ($data['sections'] as $section) {

            $current .= "### {$section['titre']}\n\n";

            $output = shell_exec("echo '{$section['contenu']}' | pandoc -f html --to 'markdown_strict+pipe_tables'");
            if ($output)
                $current .= "{$output}\n";
        }


        $current .= "\n\n";


        $current .= "### $trans_finance\n";
        $current .= "\n";



        $current .= "#### $trans_situation\n";
        $current .= "\n";
        $current .= "| $trans_membre | $trans_attendu | $trans_realise | $trans_status |\n";
        $current .= "| ----------- | ----------- | ----------- | ----------- |\n";

        foreach ($data['situation_financiere'] as $item) {
            $percent = 0;
            if ($item['montant_attendu_init'] != 0) {
                $percent =  round(($item['montant_realise'] * 100) / ($item['montant_attendu_init']), 2);
            }
            $current .= "| {$item['membre']} | {$item['montant_attendu_init']} | {$item['montant_realise']} | $percent %  |\n";
        }
        $current .= "\n";
        $current .= "\n";
        $current .= "#### $trans_decaissement\n";
        $current .= "\n";
        $current .= "| $trans_membre | $trans_attendu | $trans_realise | $trans_status |\n";
        $current .= "| ----------- | ----------- | ----------- | ----------- |\n";
        foreach ($data['decaissement'] as $item) {
            $percent = 0;
            if ($item['montant_attendu_init'] != 0) {
                $percent =  round(($item['montant_realise'] * 100) / ($item['montant_attendu_init']), 2);
            }
            if ($item['montant_attendu_init'] != 0)
                $current .= "| {$item['membre']} | {$item['montant_attendu_init']} | {$item['montant_realise']} | $percent % |\n";
        }

        $fully = Storage::url("{$path}$name");
        file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
        try {
            system("sudo pandoc $file -o $full --from markdown --template eisvogel --listings", $exec);

            $chemin = url($fully);
            $ag = Ag::find($ags_id);
            if ($ag) {
                $ag->fill([
                    'file' => $chemin
                ]);
                $ag->save();
            }

            $system->delete($file);
            return array(
                'status' => "OK",
                "data" => $ag
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }
}
