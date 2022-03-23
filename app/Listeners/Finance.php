<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Notifications\FinanceNotification;
use App\CustomModels\UserMethods;
use App\CustomModels\AssociationMethods;
use App\CustomModels\MembreMethods;
use App\CustomModels\MembresHasUserMethods;
use App\CustomModels\UserHasNotificationCategorieMethods;

use App\CustomModels\NotificationMethods;
use App\CustomModels\FCMTokenMethods;


class Finance
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
                $user = UserMethods::getById($assoc->admin_id);
                if($user != "not found"){
                    $notif = array(
                        "membre" => $membre,
                        "message" => $membre->firstName.' '.$membre->lastName. ' a fait un '.$event->operation['debit_credit'].' d\'un montant de  '.$event->operation['montant'],
                        "titre" => $assoc->nom.' - '.$event->titre.' - '.$event->operation['debit_credit'],
                        "operation" => $event->operation,
                        "association" => $membre->associations_id
                    );
                    $user->notify(new FinanceNotification($notif));
                    NotificationMethods::FCMMessage($user->id, $assoc->nom.' - '.$event->titre.' - '.$event->operation['debit_credit'], $membre->firstName.' '.$membre->lastName. ' a fait un '.$event->operation['debit_credit'].' d\'un montant de  '.$event->operation['montant'], array("operation" => $event->operation,"membre" => $membre));

                }
                $members = MembreMethods::getMemberAssociationActif($membre->associations_id);
                foreach ($members as $key => $member) {


                    $usr = MembresHasUserMethods::getUserByMemberId($member->id);
                    if($usr != "not found"){
                        if($assoc->visibilite_financiere == "OUVERT"){
                            if($usr != "not found" && ($usr->id != $assoc->admin_id)){
        
                                if(UserHasNotificationCategorieMethods::HasCategorie($usr->id, 1) || UserHasNotificationCategorieMethods::HasCategorie($usr->id, 2)){
                                    $notif = array(
                                        "membre" => $membre,
                                        "message" =>$membre->firstName.' '.$membre->lastName. ' a fait un '.$event->operation['debit_credit'].' d\'un montant de  '.$event->operation['montant'],
                                        "titre" => $assoc->nom.' - '.$event->titre.' - '.$event->operation['debit_credit'],
                                        "operation" => $event->operation,
                                        "association" => $membre->associations_id
                                    );
                                    $usr->notify(new FinanceNotification($notif));
    
                                    NotificationMethods::FCMMessage($usr->id, $assoc->nom.' - '.$event->titre.' - '.$event->operation['debit_credit'], $membre->firstName.' '.$membre->lastName. ' a fait un '.$event->operation['debit_credit'].' d\'un montant de  '.$event->operation['montant'], array("operation" => $event->operation,"membre" => $membre));
    
                                }else if(UserHasNotificationCategorieMethods::HasCategorie($usr->id, 3) && MembresHasUserMethods::userHasMember($usr->id, $membre->id)){
                                    $notif = array(
                                        "membre" => $membre,
                                        "message" => $membre->firstName.' '.$membre->lastName. ' a fait un '.$event->operation['debit_credit'].' d\'un montant de  '.$event->operation['montant'],
                                        "titre" => $assoc->nom.' - '.$event->titre,
                                        "operation" => $event->operation,
                                        "association" => $membre->associations_id
                                    );
                                    $usr->notify(new FinanceNotification($notif));
                                    NotificationMethods::FCMMessage($usr->id, $assoc->nom.' - '.$event->titre.' - '.$event->operation['debit_credit'], $membre->firstName.' '.$membre->lastName. ' a fait un '.$event->operation['debit_credit'].' d\'un montant de  '.$event->operation['montant'], array("operation" => $event->operation,"membre" => $membre));
    
                                }
        
                            }
                        }else{
                            if(UserHasNotificationCategorieMethods::HasCategorie($usr->id, 3) && MembresHasUserMethods::userHasMember($usr->id, $member->id)){
                                
                                  
                                $notif = array(
                                        "membre" => $membre,
                                        "message" =>$membre->firstName.' '.$membre->lastName. ' a fait un '.$event->operation['debit_credit'].' d\'un montant de  '.$event->operation['montant'],
                                        "titre" => $assoc->nom.' - '.$event->titre,
                                        "operation" => $event->operation,
                                        "association" => $membre->associations_id
                                    );
                                    $usr->notify(new FinanceNotification($notif));
    
                                    NotificationMethods::FCMMessage($usr->id, $assoc->nom.' - '.$event->titre.' - '.$event->operation['debit_credit'], $membre->firstName.' '.$membre->lastName. ' a fait un '.$event->operation['debit_credit'].' d\'un montant de  '.$event->operation['montant'], array("operation" => $event->operation,"membre" => $membre));
                            
                            }else{
                                
                            }
    
                        }

                    }
                }


            }
        }

    }
}
