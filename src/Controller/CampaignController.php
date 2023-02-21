<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Repository\CampaignRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class CampaignController extends AbstractController
{
    /**
     * list all the campaigns
     *
     * @param CampaignRepository  $campaignRepository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return JsonResponse
     */
    #[Route('/campaigns', methods: ['GET'])]
    public function findAll(
        CampaignRepository $campaignRepository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit', 3);

        $campaigns = $campaignRepository->findAllWithPagination($page, $limit);

        $jsonCampaigns = $serializer->serialize($campaigns, 'json', ['groups' => 'getCampaigns']);

        return new JsonResponse(
            $jsonCampaigns,
            Response::HTTP_OK,
            [],
            true,
        );
    }

    /**
     * Find one campaign by ID (only integer)
     *
     * @param Campaign             $campaign
     * @param SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    #[Route(
        '/campaigns/{id}',
        requirements: ['id'=>'\d+'],
        methods: ['GET'])
    ]
    public function findOne(
        Campaign $campaign,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $jsonCampaign = $serializer->serialize($campaign, 'json', ['groups' => 'getCampaigns']);

        return new JsonResponse(
            $jsonCampaign,
            Response::HTTP_OK,
            ['accept' => 'json'],
            true
        );
    }

    /**
     * Delete a campaign by ID
     *
     * @param Campaign               $campaign
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse
     */
    #[Route('/campaigns/{id}', methods: ['DELETE'])]
    public function delete(
        Campaign $campaign,
        EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($campaign);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a campaigns status
     *
     * @param Request                        $request
     * @param SerializerInterface            $serializer
     * @param EntityManagerInterface         $manager
     * @param UrlGeneratorInterface          $urlGenerator
     * @param UserRepository                 $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/campaigns', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository): JsonResponse
    {
        $campaign = $serializer->deserialize(
            $request->getContent(),
            Campaign::class,
            'json');

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;
        $campaign->setUser($userRepository->find($userId));

        $manager->persist($campaign);
        $manager->flush();

        $jsonCampaign = $serializer->serialize($campaign, 'json', ['groups' => 'getCampaigns']);

        $location = $urlGenerator->generate(
            'app_campaign_findone',
            ['id' => $campaign->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCampaign, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Updated a campaign with valid ID
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param Campaign               $campaign
     * @param EntityManagerInterface $manager
     * @param UserRepository         $userRepository
     *
     * @return JsonResponse
     */
    #[Route('/campaigns/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        SerializerInterface $serializer,
        Campaign $campaign,
        EntityManagerInterface $manager,
        UserRepository $userRepository
    ): JsonResponse
    {
        $updatedCampaign = $serializer->deserialize(
            $request ->getContent(),
            Campaign::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $campaign]
        );

        $content = $request->toArray();

        $userId = $content['userId'] ?? -1;

        $updatedCampaign->setUser($userRepository->find($userId));

        $manager->persist($updatedCampaign);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
