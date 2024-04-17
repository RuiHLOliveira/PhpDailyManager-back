<?php

namespace App\Tests;

use App\Entity\Dia;
use App\Entity\User;
use DateTimeImmutable;
use App\Service\AuthService;
use App\Entity\InvitationToken;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoginTest extends AppWebTestCase //webTestCase
{
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

    /**
     * @var AuthService;
     */
    protected $authService;

    /**
     * @var User;
     */
    protected $user;

    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $kernel = self::bootKernel();
        $this->doctrine = $kernel->getContainer()->get('doctrine');
        $this->entityManager = $this->doctrine->getManager();

        $this->authService = $kernel->getContainer()->get('App\Service\AuthService');
        $this->user = $this->serviceCreateUser();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }



    public function testLogin(): void
    {
        $data = [
            'password' => '123456',
            'email' => $this->user->getEmail()
        ];
        [$response, $json] = $this->request('POST','/auth/login', $data);
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals($json->message, 'success!');
    }

    
    public function testNaoPodeLogarSemEmail(): void
    {
        $data = [
            'password' => '123456',
        ];
        [$response, $json] = $this->request('POST','/auth/login', $data);
        $this->assertResponseStatusCodeSame(500);
        $this->assertEquals($json->message, 'E-mail não enviado.');
    }

    public function testNaoPodeLogarSemSenha(): void
    {
        $data = [
            'email' => $this->user->getEmail()
        ];
        [$response, $json] = $this->request('POST','/auth/login', $data);
        $this->assertResponseStatusCodeSame(500);
        $this->assertEquals($json->message, 'Senha não enviada.');
    }
    
    public function testNaoPodeLogarComEmailErrado(): void
    {
        $data = [
            'email' => $this->user->getEmail() . '.',
            'password' => '123456',
        ];
        [$response, $json] = $this->request('POST','/auth/login', $data);
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals($json->message, 'E-mail não existe.');
    }
    
    public function testNaoPodeLogarComSenhaErrada(): void
    {
        $data = [
            'email' => $this->user->getEmail(),
            'password' => '123456' . '.',
        ];
        [$response, $json] = $this->request('POST','/auth/login', $data);
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals($json->message, 'Senha incorreta.');
    }
}
