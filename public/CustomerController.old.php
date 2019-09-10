<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerController extends AbstractController
{
    /**
     * @Route("/customers", name="customer_index")
     */
    public function index(CustomerRepository $customerRepository, SerializerInterface $serializer)
    {
        // récupérer la liste des customers
        $customers = $customerRepository->findAll();

        // version tip-top
        return $this->json($customers, 200, [], [
            "groups" => "customer:read"
        ]);

        // Version SF4 (attention, marche pas trop)
        //return new JsonResponse($string);

        // Version a la mano
        // return new Response($string, 200, [
        //     'Content-type' => 'application/json'
        // ]);

        // /!\ ne marche pas parce que les propriétés de l'entité sont privées
        // return new Response(json_encode($customers), 200, [
        //     'Content-type' => 'application/json'
        // ]);
    }


    /**
     * @Route("/customers/{id}", name="customer_show")
     */
    public function show(Customer $customer)
    {
        return $this->json($customer, 200, [], [
            "groups" => "customer:read"
        ]);
    }
}
