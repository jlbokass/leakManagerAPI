<?php

namespace App\Controller;

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
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class UserController extends AbstractController
{
    /**
     * list all the user
     *
     * @param ManagerRegistry      $doctrine
     * @param SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    #[Route('/users', name: 'app_user', methods: ['GET'])]
    public function findAll(
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $users = $doctrine->getRepository(User::class)->findAll();

        $jsonUser = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);

        return new JsonResponse(
            $jsonUser,
            Response::HTTP_OK,
            [],
            true,
        );
    }

    /**
     * Find one user by id (integer)
     *
     * @param SerializerInterface $serializer
     * @param User                $user
     *
     * @return JsonResponse
     */
    #[Route('/users/{id}',
        requirements: ['id' => '\d+'],
        methods: ['GET'])
    ]
    public function findOne(SerializerInterface $serializer, User $user): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        return new JsonResponse(
            $jsonUser,
            Response::HTTP_OK,
            ['accept' => 'json'],
            true
        );
    }

    /**
     * Delete user by ID
     *
     * @param User                   $user
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse
     */
    #[Route('/users/{id}', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($user);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a user
     *
     * @param Request                                                   $request
     * @param SerializerInterface                                       $serializer
     * @param EntityManagerInterface                                    $manager
     * @param UrlGeneratorInterface                                     $urlGenerator
     * @param AgencyRepository                                          $agencyRepository
     * @param UserPasswordHasherInterface                               $hasher
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     */
    #[Route('/users', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        AgencyRepository $agencyRepository,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $content = $request->toArray();

        $agencyId = $content['agencyId'] ?? -1;
        $user->setAgency($agencyRepository->find($agencyId));

        $userPassword = $content['password'];
        $password = $hasher->hashPassword($user, $userPassword);
        $user->setPassword($password);

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($user);
        $manager->flush();

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate(
            'app_user_findone',
            ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Updated user with valid ID
     *
     * @param Request                                                   $request
     * @param SerializerInterface                                       $serializer
     * @param User                                                      $user
     * @param EntityManagerInterface                                    $manager
     * @param UserRepository                                            $userRepository
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     */
    #[Route('/users/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        SerializerInterface $serializer,
        User $user,
        EntityManagerInterface $manager,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $updatedUser = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );

        $content = $request->toArray();

        $agencyId = $content['agencyId'] ?? -1;

        $updatedUser->setAgency($userRepository->find($agencyId));

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($updatedUser);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
