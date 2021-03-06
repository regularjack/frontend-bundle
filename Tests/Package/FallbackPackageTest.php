<?php

namespace Rj\FrontendBundle\Tests\Package;

use PHPUnit_Framework_MockObject_MockObject;
use Rj\FrontendBundle\Package\FallbackPackage;
use Symfony\Component\Asset\PackageInterface;

class FallbackPackageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PackageInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $package;

    /**
     * @var PackageInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $default;

    /**
     * @var FallbackPackage
     */
    private $fallbackPackage;

    public function setUp()
    {
        $packageBuilder = $this->getMockBuilder('Symfony\Component\Asset\PathPackage');
        $defaultBuilder = $this->getMockBuilder('Symfony\Component\Asset\PathPackage');

        $this->package = $packageBuilder
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->default = $defaultBuilder
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->fallbackPackage = new FallbackPackage(
            ['must_fallback\/'],
            $this->package
        );

        $this->fallbackPackage->setFallback($this->default);
    }

    public function testGetVersion()
    {
        $this->package
            ->method('getVersion')
            ->willReturn('package')
        ;

        $this->default
            ->method('getVersion')
            ->willReturn('default')
        ;

        $this->assertEquals('package', $this->fallbackPackage->getVersion('css/foo.css'));
        $this->assertEquals('default', $this->fallbackPackage->getVersion('must_fallback/foo.css'));
    }

    public function testGetUrl()
    {
        $this->package
            ->method('getUrl')
            ->willReturn('package')
        ;

        $this->default
            ->method('getUrl')
            ->willReturn('default')
        ;

        $this->assertEquals('package', $this->fallbackPackage->getUrl('css/foo.css'));
        $this->assertEquals('default', $this->fallbackPackage->getUrl('must_fallback/foo.css'));
    }
}
