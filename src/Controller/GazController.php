<?php

namespace App\Controller;

use App\Entity\Gaz;
use App\Entity\LeakStatus;
use App\Entity\User;
use App\Repository\AgencyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class GazController extends AbstractController
{
    /**
     * list all the gaz
     *
     * @param ManagerRegistry      $doctrine
     * @param SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    #[Route('/gaz', methods: ['GET'])]
    public function findAll(
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $gaz = $doctrine->getRepository(Gaz::class)->findAll();

        $jsonGaz = $serializer->serialize($gaz, 'json', ['groups' => ['getGaz', 'getUsers']]);

        return new JsonResponse(
            $jsonGaz,
            Response::HTTP_OK,
            [],
            true,
        );
    }

    /**
     * Find one gaz by ID (only integer)
     *
     * @param Gaz                 $gaz
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    #[Route(
        '/gaz/{id}',
        requirements: ['id'=>'\d+'],
        methods: ['GET'])
    ]
    public function findOne(
        Gaz $gaz,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $jsonGaz = $serializer->serialize($gaz, 'json', ['groups' => 'getGaz']);

        return new JsonResponse(
            $jsonGaz,
            Response::HTTP_OK,
            ['accept' => 'json'],
            true
        );
    }

    /**
     * Delete a gaz by ID
     *
     * @param Gaz                    $gaz
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse
     */
    #[Route('/gaz/{id}', methods: ['DELETE'])]
    public function delete(
        Gaz $gaz,
        EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($gaz);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a gaz
     *
     * @param Request                        $request
     * @param SerializerInterface            $serializer
     * @param EntityManagerInterface         $manager
     * @param UrlGeneratorInterface          $urlGenerator
     * @param UserRepository                 $userRepository
     * @param UserPasswordHasherInterface    $hasher
     *
     * @return JsonResponse
     */
    #[Route('/gaz', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher): JsonResponse
    {
        $gaz = $serializer->deserialize($request->getContent(), Gaz::class, 'json');

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;
        $gaz->setUser($userRepository->find($userId));

        $manager->persist($gaz);
        $manager->flush();

        $jsonUser = $serializer->serialize($gaz, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate(
            'app_gaz_findone',
            ['id' => $gaz->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Updated gaz with valid ID
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param Gaz                    $gaz
     * @param EntityManagerInterface $manager
     * @param UserRepository         $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/gaz/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        SerializerInterface $serializer,
        Gaz $gaz,
        EntityManagerInterface $manager,
        UserRepository $userRepository
    ): JsonResponse
    {
        $updatedGaz = $serializer->deserialize(
            $request->getContent(),
            Gaz::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $gaz]
        );

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;

        $updatedGaz->setUser($userRepository->find($userId));

        $manager->persist($updatedGaz);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
