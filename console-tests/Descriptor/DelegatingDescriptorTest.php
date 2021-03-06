<?php

/*
 * This file is part of the webmozart/gitty package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Descriptor;

use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Descriptor\DelegatingDescriptor;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DelegatingDescriptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DelegatingDescriptor
     */
    private $descriptor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface
     */
    private $output;

    /**
     * @var \stdClass
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DescriptorInterface
     */
    private $delegate1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DescriptorInterface
     */
    private $delegate2;

    /**
     * @var InputDefinition
     */
    private $inputDefinition;

    protected function setUp()
    {
        $this->descriptor = new DelegatingDescriptor();
        $this->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $this->object = new \stdClass();
        $this->delegate1 = $this->getMock('Symfony\Component\Console\Descriptor\DescriptorInterface');
        $this->delegate2 = $this->getMock('Symfony\Component\Console\Descriptor\DescriptorInterface');
        $this->inputDefinition = new InputDefinition(array(
            new InputOption('format', null, InputOption::VALUE_REQUIRED),
        ));
    }

    public function testDescribe()
    {
        $options = array(
            'input' => new StringInput('--format=xml', $this->inputDefinition),
            'option' => 'value',
        );

        $this->descriptor->register('text', $this->delegate1);
        $this->descriptor->register('xml', $this->delegate2);

        $this->delegate1->expects($this->never())
            ->method('describe');

        $this->delegate2->expects($this->once())
            ->method('describe')
            ->with($this->output, $this->object, $options)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $this->object, $options);

        $this->assertSame(123, $status);
    }

    public function testDescribeUsesFirstRegisteredDescriptorByDefault()
    {
        $options = array(
            'input' => new StringInput('', $this->inputDefinition),
            'option' => 'value',
        );

        $this->descriptor->register('text', $this->delegate1);
        $this->descriptor->register('xml', $this->delegate2);

        $this->delegate1->expects($this->once())
            ->method('describe')
            ->with($this->output, $this->object, $options)
            ->will($this->returnValue(123));

        $this->delegate2->expects($this->never())
            ->method('describe');

        $status = $this->descriptor->describe($this->output, $this->object, $options);

        $this->assertSame(123, $status);
    }

    public function testDescribeUsesDefaultDescriptor()
    {
        $options = array(
            'input' => new StringInput('', $this->inputDefinition),
            'option' => 'value',
        );

        $this->descriptor = new DelegatingDescriptor('xml');
        $this->descriptor->register('text', $this->delegate1);
        $this->descriptor->register('xml', $this->delegate2);

        $this->delegate1->expects($this->never())
            ->method('describe');

        $this->delegate2->expects($this->once())
            ->method('describe')
            ->with($this->output, $this->object, $options)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $this->object, $options);

        $this->assertSame(123, $status);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDescribeFailsIfFormatNotSupported()
    {
        $this->descriptor->describe($this->output, $this->object, array(
            'input' => new StringInput('--format=xml', $this->inputDefinition),
        ));
    }
}
