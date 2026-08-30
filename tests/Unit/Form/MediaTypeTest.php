<?php

namespace App\Tests\Unit\Form;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use App\Form\MediaType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MediaTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);
    }

    #[DataProvider('baseFieldProvider')]
    public function testGuestFormContainsBaseFields(string $field, string $type, string $label): void
    {
        $form = $this->formFactory->create(MediaType::class, new Media());
        $config = $form->get($field)->getConfig();

        self::assertSame($type, $config->getType()->getInnerType()::class);
        self::assertSame($label, $config->getOption('label'));
    }

    public function testGuestFormDoesNotContainAdminFields(): void
    {
        $form = $this->formFactory->create(MediaType::class, new Media());

        self::assertFalse($form->has('user'));
        self::assertFalse($form->has('album'));
    }

    #[DataProvider('adminFieldProvider')]
    public function testAdminFormContainsAdminField(string $field, string $class, string $label): void
    {
        $form = $this->formFactory->create(MediaType::class, new Media(), ['is_admin' => true]);
        $config = $form->get($field)->getConfig();

        self::assertSame(EntityType::class, $config->getType()->getInnerType()::class);
        self::assertSame($class, $config->getOption('class'));
        self::assertSame($label, $config->getOption('label'));
        self::assertFalse($config->getOption('required'));
    }

    /**
     * @return array{file: string[], title: string[]}
     */
    public static function baseFieldProvider(): array
    {
        return [
            'file' => ['file', FileType::class, 'Image'],
            'title' => ['title', TextType::class, 'Titre'],
        ];
    }

    /**
     * @return array{album: string[], user: string[]}
     */
    public static function adminFieldProvider(): array
    {
        return [
            'user' => ['user', User::class, 'Utilisateur'],
            'album' => ['album', Album::class, 'Album'],
        ];
    }
}