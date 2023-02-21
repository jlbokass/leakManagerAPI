<?php

namespace App\DataFixtures;

use App\Entity\Agency;
use App\Entity\Campaign;
use App\Entity\Gaz;
use App\Entity\Leak;
use App\Entity\LeakStatus;
use App\Entity\Severity;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{


    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $hasher;

    // Phone
    private CONST phone = [
        '+33 06 99 87 45 12',
        '+33 07 45 12 30 25',
        '+33 06 95 74 96 32',
        '+33 07 88 55 44 12'
    ];

    // Severity
    private CONST severity = [
        'little',
        'middle',
        'high'
    ];

    // Status
    private CONST leakStatus = [
        'repair',
        'not repaired'
    ];

    // Severity
    private CONST gaz = [
        'propane',
        'butane',
        'ethanol',
        'methyl'
    ];

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Agency

        $agencies = [];

        for ($i = 0; $i < 6; $i++) {
            $agency = new Agency();
            $agency->setName($faker->unique()->company());

            $manager->persist($agency);

            $agencies[] = $agency;
        }

        // Users
        $users = [];

        for ($i = 0; $i < 4; $i++) {
            $user = new User();

            $password = $this->hasher->hashPassword($user, '1234');

            $user
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setAgency($agencies[array_rand($agencies)])
                ->setEmail($faker->email())
                ->setPassword($password)
                ->setPhoneNumber($faker->randomElement(self::phone))
                ->setRoles(['ROLE_TECH'])
            ;

            $manager->persist($user);

            $users[] = $user;
        }

        //Severity

        $severities = [];

        for ($i = 0; $i < 3; $i++) {
            $severity = new Severity();
            $severity
                ->setSeverityName($faker->unique()->randomElement(self::severity))
                ->setUser($users[array_rand($users)])
            ;

            $manager->persist($severity);

            $severities = [$severity];
        }

        // Status
        $lmStatus = [];

        for ($i = 0; $i < 2; $i++) {
            $status = new LeakStatus();
            $status
                ->setStatusName($faker->unique()->randomElement(self::leakStatus))
                ->setUser($users[array_rand($users)])
            ;

            $manager->persist($status);

            $lmStatus[] = $status;
        }

        // Campaigns
        $campaigns = [];

        for ($i = 0; $i <15; $i++) {
            $campaign = new Campaign();
            $campaign
                ->setSocietyName($faker->company())
                ->setLocation($faker->address())
                ->setKwhPrice($faker->randomFloat(2,0.1, 20))
                ->setNbrCompressorUseByYear($faker->randomNumber())
                ->setElectricityPrice($faker->randomFloat(2, 0,1, 20))
                ->setDescription($faker->paragraph(3, 5))
                ->setUser($users[array_rand($users)])
                ;
            $manager->persist($campaign);

            $campaigns[] = $campaign;
        }

        // Gaz
        for ($i = 0; $i < 3; $i++ ) {
            $gaz = new Gaz();
            $gaz
                ->setGazName($faker->randomElement(self::gaz))
                ->setUser($users[array_rand($users)])
            ;

            $manager->persist($gaz);
        }

        // Leak

        for ($i = 0; $i < 10; $i++) {
            $leak = new Leak();
            $leak
                ->setLeakLocation($faker->paragraph(2))
                ->setLeakDescription($faker->paragraph(5))
                ->setLeakNumber($faker->numberBetween(1, 100))
                ->setLmStatus($lmStatus[array_rand($lmStatus)])
                ->setSeverity($severities[array_rand($severities)])
                ->setUser($users[array_rand($users)])
                ->setLeakImageBig($faker->url())
                ->setLeakImageSmall($faker->url())
                ->setMeasuredFlow($faker->randomFloat(2, 0, 20))
                ->setCampaign($campaigns[array_rand($campaigns)])
                ->setComment($faker->realText(400))
                ;

            $manager->persist($leak);
        }

        $manager->flush();
    }
}
