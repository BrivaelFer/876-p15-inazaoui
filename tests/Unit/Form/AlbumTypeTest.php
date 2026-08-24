<?php

namespace App\Tests\Unit\Form;

use App\Entity\Album;
use App\Form\AlbumType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;

class AlbumTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);
    }

    #[DataProvider('fieldProvider')]
    public function testFormContainsExpectedField(string $field, string $type, string $label): void
    {
        $form = $this->formFactory->create(AlbumType::class, new Album());

        self::assertTrue($form->has($field));
        self::assertSame($type, $form->get($field)->getConfig()->getType()->getInnerType()::class);
        self::assertSame($label, $form->get($field)->getConfig()->getOption('label'));
    }

    public static function fieldProvider(): array
    {
        return [
            'album name' => ['name', TextType::class, 'Nom'],
        ];
    }
}