<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\NouvelleNotification;
use App\CustomModels\UserMethods;
use App\CustomModels\AssociationMethods;
use App\CustomModels\MembreMethods;
use App\CustomModels\MembresHasUserMethods;
use App\CustomModels\UserHasNotificationCategorieMethods;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

use App\CustomModels\NotificationMethods;
class Nouvelle
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //
        $membre = MembreMethods::getById($event->member_id);
        if($membre != "not found"){
            $assoc = AssociationMethods::getById($membre->associations_id);
            if($assoc != "not found"){
                $members = MembreMethods::getMemberAssociationActif($membre->associations_id);
                foreach ($members as $key => $member) {
                    $usr = MembresHasUserMethods::getUserByMemberId($member->id);
                    if($usr != "not found"){

                        if(UserHasNotificationCategorieMethods::HasCategorie($usr->id, 1) || UserHasNotificationCategorieMethods::HasCategorie($usr->id, 4)){
                            
                            $notif = array(
                                "membre" => $membre,
                                "message" => $event->nouvelle->description,
                                "titre" => $assoc->nom.' - '.$event->titre.' - '.$event->nouvelle->titre,
                                "nouvelle" => $event->nouvelle,
                                "association" => $membre->associations_id
                            );
                            $usr->notify(new NouvelleNotification($notif));
                            NotificationMethods::FCMMessage($usr->id, $assoc->nom.' - '.$event->titre.' - '.$event->nouvelle->titre, $event->nouvelle->description, array("nouvelle" => $event->nouvelle,"membre" => $membre));

                        }

                    }
                }

            }
        }

    }
}
