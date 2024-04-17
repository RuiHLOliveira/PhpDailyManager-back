<?php

namespace App\Tests;

use App\Entity\Dia;
use Doctrine\Persistence\ManagerRegistry;

class DiasTest extends AppWebTestCase
{

    protected $qtdHorasPrevistaNoDia = 17;
    protected $user;
    protected $authService;
    protected $diasService;
    
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser;
     */
    protected $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $kernel = self::bootKernel();
        $this->doctrine = $kernel->getContainer()->get('doctrine');
        $this->entityManager = $this->doctrine->getManager();
        
        $this->authService = $kernel->getContainer()->get('App\Service\AuthService');
        $this->diasService = $kernel->getContainer()->get('App\Service\DiasService');
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testNaoPodeListarDiasNaoLogado(): void
    {
        [$response, $json] = $this->request('GET', '/dias', []);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListarDias(): void
    {
        $this->serviceLoggedInUser();
        $dias[] = $this->testerCreateNewDiaFromDataCompleta();
        $dias[] = $this->testerCreateNewDiaFromDataCompleta();

        [$response, $json] = $this->request('GET', '/dias', []);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(count($dias), $json);
    }

    public function testCriarDia(): void
    {
        $this->serviceLoggedInUser();
        [$response, $json] = $this->request('POST', '/dias', ['dataCompleta' => '2022-03-03']);

        $this->assertResponseStatusCodeSame(201);
        $diaDb = $this->entityManager->getRepository(Dia::class)->findOneBy(['id' => $json->id, 'usuario' => $this->user]);
        $this->assertNotNull($diaDb);
        $this->assertEquals($diaDb->getId(), $json->id);
    }
}
