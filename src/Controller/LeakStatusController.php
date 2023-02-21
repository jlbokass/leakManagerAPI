<?php

namespace App\Controller;

use App\Entity\LeakStatus;
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
class LeakStatusController extends AbstractController
{
    /**
     * list all the status
     *
     * @param ManagerRegistry      $doctrine
     * @param SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    #[Route('/status', name: 'app_status', methods: ['GET'])]
    public function findAll(
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $status = $doctrine->getRepository(LeakStatus::class)->findAll();

        $jsonStatus = $serializer->serialize($status, 'json', ['groups' => 'getLeaksStatus']);

        return new JsonResponse(
            $jsonStatus,
            Response::HTTP_OK,
            [],
            true,
        );
    }

    /**
     * Find one status by ID (only integer)
     *
     * @param LeakStatus             $status
     * @param SerializerInterface    $serializer
     *
     * @return JsonResponse
     */
    #[Route(
        '/status/{id}',
        requirements: ['id'=>'\d+'],
        methods: ['GET'])
    ]
    public function findOne(
        LeakStatus $status,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $jsonStatus = $serializer->serialize($status, 'json', ['groups' => 'getLeaksStatus']);

        return new JsonResponse(
            $jsonStatus,
            Response::HTTP_OK,
            ['accept' => 'json'],
            true
        );
    }

    /**
     * Delete a leak status by ID
     *
     * @param LeakStatus             $leakStatus
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse
     */
    #[Route('/status/{id}', methods: ['DELETE'])]
    public function delete(
        LeakStatus $leakStatus,
        EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($leakStatus);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a leak status
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param EntityManagerInterface $manager
     * @param UrlGeneratorInterface  $urlGenerator
     * @param UserRepository         $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/status', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository): JsonResponse
    {
        $leakStatus = $serializer->deserialize(
            $request->getContent(),
            LeakStatus::class,
            'json');

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;
        $leakStatus->setUser($userRepository->find($userId));

        $manager->persist($leakStatus);
        $manager->flush();

        $jsonLeakStatus = $serializer->serialize($leakStatus, 'json', ['groups' => 'getLeaksStatus']);

        $location = $urlGenerator->generate(
            'app_leakstatus_findone',
            ['id' => $leakStatus->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonLeakStatus, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Updated leak status with valid ID
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param LeakStatus             $leakStatus
     * @param EntityManagerInterface $manager
     * @param UserRepository         $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/status/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        SerializerInterface $serializer,
        LeakStatus $leakStatus,
        EntityManagerInterface $manager,
        UserRepository $userRepository
    ): JsonResponse
    {
        $updatedLeakStatus = $serializer->deserialize(
            $request ->getContent(),
            LeakStatus::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $leakStatus]
        );

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;

        $updatedLeakStatus->setUser($userRepository->find($userId));

        $manager->persist($updatedLeakStatus);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
