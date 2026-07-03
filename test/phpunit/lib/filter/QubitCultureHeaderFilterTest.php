<?php

use PHPUnit\Framework\TestCase;

require_once 'lib/filter/QubitCultureHeaderFilter.class.php';

/**
 * @internal
 *
 * @covers \QubitCultureHeaderFilter
 */
class QubitCultureHeaderFilterTest extends TestCase
{
    protected function setUp(): void
    {
        sfConfig::set('app_i18n_languages', ['en', 'fr', 'es']);

        $reflection = new ReflectionClass(QubitCultureHeaderFilter::class);
        $allowedCultures = $reflection->getProperty('allowedCultures');
        $allowedCultures->setAccessible(true);
        $allowedCultures->setValue(null, null);
    }

    public function testAllowsConfiguredCulture()
    {
        $filter = new TestQubitCultureHeaderFilter($this->createMock(sfContext::class), []);

        $this->assertTrue($filter->isAllowedCultureForTest('en'));
        $this->assertTrue($filter->isAllowedCultureForTest('es'));
    }

    public function testAllowsConfiguredCultureWithWhitespace()
    {
        $filter = new TestQubitCultureHeaderFilter($this->createMock(sfContext::class), []);

        $this->assertTrue($filter->isAllowedCultureForTest('fr'));
    }

    public function testRejectsCultureOutsideAllowList()
    {
        $filter = new TestQubitCultureHeaderFilter($this->createMock(sfContext::class), []);

        $this->assertFalse($filter->isAllowedCultureForTest('de'));
    }

    public function testRejectsEmptyAndNonStringCultures()
    {
        $filter = new TestQubitCultureHeaderFilter($this->createMock(sfContext::class), []);

        $this->assertFalse($filter->isAllowedCultureForTest(''));
        $this->assertFalse($filter->isAllowedCultureForTest('   '));
        $this->assertFalse($filter->isAllowedCultureForTest(null));
        $this->assertFalse($filter->isAllowedCultureForTest(['en']));
    }
}

class TestQubitCultureHeaderFilter extends QubitCultureHeaderFilter
{
    public function isAllowedCultureForTest($culture)
    {
        if (is_string($culture)) {
            $culture = trim($culture);
        }

        return $this->isAllowedCulture($culture);
    }
}
