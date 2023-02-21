<?php

namespace App\Controller;

use App\Entity\Severity;
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
class SeverityController extends AbstractController
{
    /**
     * list all the severity
     *
     * @param ManagerRegistry      $doctrine
     * @param SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    #[Route('/severities', name: 'app_severity', methods: ['GET'])]
    public function findAll(
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $severity = $doctrine->getRepository(Severity::class)->findAll();

        $jsonSeverity = $serializer->serialize($severity, 'json', ['groups' => ['getSeverities']]);

        return new JsonResponse(
            $jsonSeverity,
            Response::HTTP_OK,
            [],
            true,
        );
    }

    /**
     * Find one severity by ID (only integer)
     *
     * @param Severity             $severity
     * @param SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    #[Route(
        '/severities/{id}',
        requirements: ['id'=>'\d+'],
        methods: ['GET'])
    ]
    public function findOne(
        Severity $severity,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $jsonSeverity = $serializer->serialize($severity, 'json', ['groups' => ['getSeverities']]);

        return new JsonResponse(
            $jsonSeverity,
            Response::HTTP_OK,
            ['accept' => 'json'],
            true
        );
    }

    /**
     * Delete a severity by ID
     *
     * @param Severity               $severity
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse
     */
    #[Route('/severities/{id}', methods: ['DELETE'])]
    public function delete(
        Severity $severity,
        EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($severity);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a severity
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param EntityManagerInterface $manager
     * @param UrlGeneratorInterface  $urlGenerator
     * @param UserRepository         $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/severities', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository): JsonResponse
    {
        $severity = $serializer->deserialize(
            $request->getContent(),
            Severity::class,
            'json');

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;
        $severity->setUser($userRepository->find($userId));

        $manager->persist($severity);
        $manager->flush();

        $jsonUser = $serializer->serialize($severity, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate(
            'app_severity_findone',
            ['id' => $severity->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Updated severity with valid ID
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param Severity               $severity
     * @param EntityManagerInterface $manager
     * @param UserRepository         $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/severities/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        SerializerInterface $serializer,
        Severity $severity,
        EntityManagerInterface $manager,
        UserRepository $userRepository
    ): JsonResponse
    {
        $updatedSeverity = $serializer->deserialize(
            $request ->getContent(),
            Severity::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $severity]
        );

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;

        $updatedSeverity->setUser($userRepository->find($userId));

        $manager->persist($updatedSeverity);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
