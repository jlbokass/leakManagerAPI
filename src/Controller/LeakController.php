<?php

namespace App\Controller;

use App\Entity\Leak;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class LeakController extends AbstractController
{
    /**
     * list all the leak
     *
     * @param ManagerRegistry      $doctrine
     * @param SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    #[Route('/leaks', methods: ['GET'])]
    public function findAll(
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $leaks = $doctrine->getRepository(Leak::class)->findAll();

        $jsonLeaks = $serializer->serialize($leaks, 'json', ['groups' => ['getLeaks']]);

        return new JsonResponse(
            $jsonLeaks,
            Response::HTTP_OK,
            [],
            true,
        );
    }

    /**
     * Find one leak by ID (only integer)
     *
     * @param Leak                  $leak
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    #[Route(
        '/leaks/{id}',
        requirements: ['id'=>'\d+'],
        methods: ['GET'])
    ]
    public function findOne(
        Leak $leak,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $jsonLeak = $serializer->serialize($leak, 'json', ['groups' => 'getLeaks']);

        return new JsonResponse(
            $jsonLeak,
            Response::HTTP_OK,
            ['accept' => 'json'],
            true
        );
    }

    /**
     * Delete a leak by ID
     *
     * @param Leak                   $leak
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse
     */
    #[Route('/leaks/{id}', methods: ['DELETE'])]
    public function delete(
        Leak $leak,
        EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($leak);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a leak status
     *
     * @param Request                        $request
     * @param SerializerInterface            $serializer
     * @param EntityManagerInterface         $manager
     * @param UrlGeneratorInterface          $urlGenerator
     * @param UserRepository                 $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/leaks', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository): JsonResponse
    {
        $leak = $serializer->deserialize(
            $request->getContent(),
            Leak::class,
            'json');

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;
        $leak->setUser($userRepository->find($userId));

        $manager->persist($leak);
        $manager->flush();

        $jsonLeak = $serializer->serialize($leak, 'json', ['groups' => 'getLeaks']);

        $location = $urlGenerator->generate(
            'app_leak_findone',
            ['id' => $leak->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonLeak, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Updated a leak with valid ID
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param Leak                   $leak
     * @param EntityManagerInterface $manager
     * @param UserRepository         $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/leaks/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        SerializerInterface $serializer,
        Leak $leak,
        EntityManagerInterface $manager,
        UserRepository $userRepository
    ): JsonResponse
    {
        $updatedLeak = $serializer->deserialize(
            $request ->getContent(),
            Leak::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $leak]
        );

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;

        $updatedLeak->setUser($userRepository->find($userId));

        $manager->persist($updatedLeak);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
