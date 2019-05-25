<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Validator;

use AppBundle\Utils\Messages\Validator\UserAfkValidator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserAfkValidatorTest extends TestCase
{
    /**
     * @var MockObject & SessionInterface
     */
    private $session;
    /**
     * @var UserAfkValidator
     */
    private $userAfkValidator;

    protected function setUp()
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->userAfkValidator = new UserAfkValidator($this->session);
    }

    public function testValidateUserAfkReturnsFalse(): void
    {
        $this->session->expects($this->at(0))
            ->method('get')
            ->with('afk')
            ->willReturn(false);

        $this->session->expects($this->at(1))
            ->method('get')
            ->with('afk')
            ->willReturn(false);

        $this->assertFalse($this->userAfkValidator->validateUserAfk('text'));
        $this->assertFalse($this->userAfkValidator->validateUserAfk('/afk'));
    }

    public function testValidateUserAfkReturnsTrue()
    {
        $this->session->expects($this->at(0))
            ->method('get')
            ->with('afk')
            ->willReturn(true);

        $this->assertTrue($this->userAfkValidator->validateUserAfk('some text'));
    }
}
