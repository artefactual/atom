<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitRequireLoginFilter
 */
class QubitRequireLoginTest extends TestCase
{
    protected $contextMock;
    protected $filter;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(sfContext::class);

        sfConfig::set('sf_login_module', 'user');
        sfConfig::set('sf_login_action', 'login');

        $this->filter = new QubitRequireLoginFilter($this->contextMock);
    }

    public function testLoginActionIsAuthRoute()
    {
        $this->assertTrue($this->filter->isAuthRoute('user', 'login'));
    }

    public function testLogoutActionIsAuthRoute()
    {
        $this->assertTrue($this->filter->isAuthRoute('user', 'logout'));
    }

    public function testOidcModuleIsAuthRoute()
    {
        $this->assertTrue($this->filter->isAuthRoute('oidc', 'login'));
        $this->assertTrue($this->filter->isAuthRoute('oidc', 'logout'));
    }

    public function testCasModuleIsAuthRoute()
    {
        $this->assertTrue($this->filter->isAuthRoute('cas', 'login'));
        $this->assertTrue($this->filter->isAuthRoute('cas', 'logout'));
    }

    public function testNonAuthUserActionIsNotAuthRoute()
    {
        $this->assertFalse($this->filter->isAuthRoute('user', 'list'));
        $this->assertFalse($this->filter->isAuthRoute('user', 'index'));
    }

    public function testBrowsingRoutesAreNotAuthRoutes()
    {
        $this->assertFalse($this->filter->isAuthRoute('informationobject', 'browse'));
        $this->assertFalse($this->filter->isAuthRoute('staticpage', 'index'));
    }
}
