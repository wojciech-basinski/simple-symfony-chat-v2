<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Validator;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Validator\AddMessageValidator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AddMessageValidatorTest extends TestCase
{
    /**
     * @var MockObject & SessionInterface
     */
    private $session;
    /**
     * @var MockObject & ChatConfig
     */
    private $config;
    /**
     * @var AddMessageValidator
     */
    private $addMessageValidator;

    protected function setUp()
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->config = $this->createMock(ChatConfig::class);

        $this->addMessageValidator = new AddMessageValidator($this->session, $this->config);
    }

    public function testValidateMessage(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->at(0))
            ->method('getId')
            ->willReturn(0);
        $user->method('getId')
            ->willReturn(1);

        $this->session->expects($this->at(0))
            ->method('set')
            ->with('errorMessage', 'Nie możesz wysyłać wiadomości będąc nie zalogowanym');
        $this->assertFalse($this->addMessageValidator->validateMessage($user, 1, 'text'));

        $this->session->expects($this->at(0))
            ->method('set')
            ->with('errorMessage', 'Wiadomośc nie może być pusta');
        $this->assertFalse($this->addMessageValidator->validateMessage($user, 1, null));

        $this->session->expects($this->at(0))
            ->method('set')
            ->with('errorMessage', 'Wiadomośc nie może być pusta');
        $this->assertFalse($this->addMessageValidator->validateMessage($user, 1, ''));

        $this->config->expects($this->at(0))
            ->method('getChannels')
            ->willReturn([]);
        $this->session->expects($this->at(0))
            ->method('set')
            ->with('errorMessage', 'Nie możesz pisać na tym kanale');
        $this->assertFalse($this->addMessageValidator->validateMessage($user, 1, 'text'));

        $this->config->method('getChannels')
            ->willReturn([1 => 1]);

        $this->session->expects($this->at(0))
            ->method('set')
            ->with('errorMessage', 'Wiadomośc nie może zaczynać się od (pm)');
        $this->assertFalse($this->addMessageValidator->validateMessage($user, 1, '(pm)'));

        $this->session->expects($this->at(0))
            ->method('set')
            ->with('errorMessage', 'Wiadomośc nie może zaczynać się od (pw)');
        $this->assertFalse($this->addMessageValidator->validateMessage($user, 1, '(pw)'));

        $this->assertTrue($this->addMessageValidator->validateMessage($user, 1, 'some text'));
    }
}
