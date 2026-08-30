<?php

namespace App\Tests\Functional;

use App\Entity\Album;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    #[DataProvider('staticPageProvider')]
    public function testStaticPagesAreDisplayed(string $path, string $expectedText): void
    {
        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', $expectedText);
    }

    #[DataProvider('guestsPageProvider')]
    public function testGuestsPageDisplaysOnlyActiveGuests(
        int $expectedGuestCount,
        string $expectedGuest,
        string $unexpectedGuest
    ): void
    {
        $this->client->request('GET', '/guests');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Invités');
        self::assertCount($expectedGuestCount, $this->client->getCrawler()->filter('.guest'));
        self::assertStringContainsString($expectedGuest, $this->client->getResponse()->getContent());
        self::assertStringNotContainsString($unexpectedGuest, $this->client->getResponse()->getContent());
    }

    #[DataProvider('guestPageProvider')]
    public function testGuestPageAccordingToRole(
        string $email,
        bool $expectedSuccess,
        string $expectedText = ''
    ): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        $this->client->request('GET', '/guest/'.$user->getId());

        if ($expectedSuccess) {
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('h3', $expectedText);
            return;
        }

        self::assertResponseRedirects('/guests');
    }

    #[DataProvider('portfolioProvider')]
    public function testPortfolioDisplaysExpectedMedia(?int $albumId, int $expectedMediaCount): void
    {
        $path = $albumId === null ? '/portfolio' : '/portfolio/'.$albumId;

        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3', 'Portfolio');
        self::assertCount($expectedMediaCount, $this->client->getCrawler()->filter('.media'));
    }

    /**
     * @return array<string, string[]>
     */
    public static function staticPageProvider(): array
    {
        return [
            'home' => ['/', 'Photographe'],
            'about' => ['/about', 'Qui suis-je ?']
        ];
    }

    /**
     * @return array<string, array<string|bool>>
     */
     public static function guestPageProvider(): array
    {
        return [
            'active guest' => ['invite+0@example.com', true, 'Invité 0'],
            'inactive admin' => ['ina@zaoui.com', false]
        ];
    }

    /**
     * @return array<string, array{?int, int}>
     */
    public static function portfolioProvider(): array
    {
        return [
            'all media' => [null, 45],
            'one album' => [1, 9]
        ];
    }

    /**
     * @return array<string, array{int, string, string}>
     */
    public static function guestsPageProvider(): array
    {
        return [
            'active guests only' => [55, 'Invité 0', 'Ina Zaoui ('],
        ];
    }
}
