# ApiSym

API symfony de la formation Angular Symfony 3WA

## API Platform

https://api-platform.com/

## Le modèle ce maturité Richardson

C'est le format des données renvoyés par l'API
https://guide-api-rest.wishtack.io/api-rest/le-modele-de-maturite-de-richardson

Level 3 Hypermedia Controls
Json Linked Data
Convention de formatage

## Materialize pour le design

https://materializecss.com/

## pour la gestion des token dans l'API

https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#getting-started

## Comprendre JWT

https://jwt.io/

## DANS VOTRE API :

Dans le dossier src/Doctrine ajoutez cette classe (fichier CurrentUserExtension) :

<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Customer;
use App\Entity\Invoice;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // On chope l'utilisateur courant
        $user = $this->security->getUser();

        // Si l'API n'est pas en train de traiter de Customer ou d'Invoice
        // On n'intervient pas !
        if (Customer::class !== $resourceClass && Invoice::class !== $resourceClass)
            return;


        // Sinon, on va déformer la requête DQL
        $rootAlias = $queryBuilder->getRootAliases()[0];
        // Si on traite des customers
        if ($resourceClass === Customer::class) {
            // Il suffit d'ajouter un "WHERE c.user = :current_user"
            $queryBuilder->andWhere(sprintf('%s.user = :current_user', $rootAlias));
        }
        // Sinon, si on traite des invoices
        else if ($resourceClass === Invoice::class) {
            // On va faire la jointure avec le customer de l'invoice
            // et s'assurer que ce customer appartient bien à l'utilisateur
            $queryBuilder->innerJoin(sprintf('%s.customer', $rootAlias), 'c')
                ->andWhere('c.user = :current_user');
        }

        // Au final, on donne la valeur de current_user (qui est l'user connecté)
        $queryBuilder->setParameter('current_user', $user);
    }
}

============================================================================
============================================================================

Dans le dossier src/Event ajoutez cette classe (fichier CustomerUserListener) :

<?php

namespace App\Event;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Customer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class CustomerUserListener implements EventSubscriberInterface
{
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setCurrentUserOnCustomer', EventPriorities::PRE_VALIDATE]
        ];
    }

    public function setCurrentUserOnCustomer(ViewEvent $event)
    {
        // On récupère le customer qui est en train d'être créé
        $data = $event->getControllerResult();

        // Si on est en POST et qu'on traite un Customer
        if ($event->getRequest()->getMethod() === "POST" && $data instanceof Customer) {
            // On donne au customer l'utilisateur actuellement connecté
            $data->setUser($this->security->getUser());
        }
    }
}
