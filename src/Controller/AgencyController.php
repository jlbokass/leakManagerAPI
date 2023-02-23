<?php

namespace App\Controller;

use App\Entity\Agency;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Hateoas\Configuration\Annotation as Hateoas;

#[Route('/api')]
class AgencyController extends AbstractController
{
    /**
     * List all the agencies
     *
     * @param ManagerRegistry     $doctrine
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    #[Route('/agencies', name: 'app_agency', methods: ['GET'])]
    public function findAll(
        ManagerRegistry $doctrine,
        SerializerInterface $serializer): JsonResponse
    {
        $agencies = $doctrine->getRepository(Agency::class)->findAll();

        $jsonAgencies = $serializer->serialize($agencies, 'json', ['groups' => ['getAgencies']]);

        return new JsonResponse(
            $jsonAgencies,
            Response::HTTP_OK,
            [],
            true
            );
    }

    /**
     * Find one agency by ID (only integer)
     *
     * @param Agency              $agency
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    #[Route(
        '/agencies/{id}',
        name: 'app_agency_find_one',
        requirements: ['id'=>'\d+'],
        methods: ['GET'])
    ]
    public function findOne(
        Agency $agency,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $jsonAgency = $serializer->serialize($agency, 'json', ['groups' => ['getAgencies']]);

        return new JsonResponse(
            $jsonAgency,
            Response::HTTP_OK,
            ['accept' => 'json'],
            true
        );
    }

    /**
     * Delete agency by ID
     *
     * @param Agency                 $agency
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse
     */
    #[Route('/agencies/{id}', methods: ['DELETE'])]
    public function delete(
        Agency $agency,
        EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($agency);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create an agency
     *
     * @param Request                                                   $request
     * @param SerializerInterface                                       $serializer
     * @param EntityManagerInterface                                    $manager
     * @param UrlGeneratorInterface                                     $urlGenerator
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     */
    #[Route('/agencies', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator): JsonResponse
    {
        $agency = $serializer->deserialize($request->getContent(), Agency::class, 'json');

        $errors = $validator->validate($agency);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($agency);
        $manager->flush();

        $jsonAgency = $serializer->serialize($agency, 'json', ['groups' => 'getAgencies']);

        $location = $urlGenerator->generate(
            'app_agency_findone',
            ['id' => $agency->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAgency, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Updated agency with valid ID
     *
     * @param Request                                                   $request
     * @param SerializerInterface                                       $serializer
     * @param Agency                                                    $agency
     * @param EntityManagerInterface                                    $manager
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     */
    #[Route('/agencies/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        SerializerInterface $serializer,
        Agency $agency,
        EntityManagerInterface $manager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $updatedAgency = $serializer->deserialize(
            $request->getContent(),
            Agency::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $agency]
        );

        $errors = $validator->validate($agency);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($updatedAgency);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
