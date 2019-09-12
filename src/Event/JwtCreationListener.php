<?php

namespace App\Event;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

//  Cette classe va permettre de personnaliser le payload du Token JWT qui sera renvoyé au client pour contenir les informations utiles
// ATTENTION les informations du Payload sont visibles ne pas mettre de données sensible

class JwtCreationListener
{
  public function injectDataIntoJwt(JWTCreatedEvent $event)
  {

    // 1. Récupérer l'utilisateur (propriété user qui se trouve dans $event)
    $user = $event->getUser();
    //2. Récupérer son avatar
    $avatar = $user->getAvatar();
    // 3. Récupérer les données du token (propriété data qui se trouve dans $event)
    $data = $event->getData();
    // 4. Mettre l'avatar dans les données
    $data['avatar'] = $avatar;
    // 5. Remettre les nouvelles données dans $event
    $event->setData($data);


    // dd($event);
  }
}
