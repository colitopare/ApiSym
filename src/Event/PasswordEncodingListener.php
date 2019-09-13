<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordEncodingListener implements EventSubscriberInterface
{
  protected $encoder;
  public function __construct(UserPasswordEncoderInterface $encoder)
  {
    $this->encoder = $encoder;
  }

  public static function getSubscribedEvents()
  {
    // Doc sur les event dans symfony
    // https://symfony.com/doc/current/reference/events.html
    return [
      // 0 est l'ordre d'appel 
      "kernel.request" => ["convertUserPassword", 0]
    ];
  }

  // Commande pour voir si on a bien notre function d'appelé avant même que l'on tape du code
  // php bin/console debug:event-dispatcher

  public function convertUserPassword(RequestEvent $event)
  {
    // récupération des data de toutes les requêtes 
    $data = $event->getRequest()->attributes->get('data');

    $method = $event->getRequest()->getMethod();

    // Si je suis en train de parler d'un USER et que je suis en méthode POST
    if ($data && $data instanceof User && $method === "POST" && $data->getPassword() !== "") {
      $plainPassword = $data->getPassword();
      // dd($plainPassword);
      $hash = $this->encoder->encodePassword($data, $plainPassword);
      // dd($hash);
      $data->setPassword($hash);
    }
  }
}
