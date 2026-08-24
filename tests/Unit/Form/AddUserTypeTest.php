<?php

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\AddUserType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;

class AddUserTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);
    }

    #[DataProvider('fieldProvider')]
    public function testFormContainsExpectedField(string $field, string $type, ?string $label): void
    {
        $form = $this->formFactory->create(AddUserType::class, new User());
        $config = $form->get($field)->getConfig();

        self::assertTrue($form->has($field));
        self::assertSame($type, $config->getType()->getInnerType()::class);
        self::assertSame($label, $config->getOption('label'));
    }

    public static function fieldProvider(): array
    {
        return [
            'email' => ['email', EmailType::class, null],
            'name' => ['name', TextType::class, 'Nom'],
            'description' => ['description', TextareaType::class, null],
            'password' => ['password', PasswordType::class, 'Mot de passe'],
        ];
    }
}