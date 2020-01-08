<?php

namespace Tests\Markdown;

use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Statamic\Markdown\Manager;
use Statamic\Markdown\Parser;

class ManagerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    function it_forwards_calls_to_default_parser()
    {
        $manager = new Manager;
        $manager->extend('default', function () {
            return Mockery::mock(Parser::class)->shouldReceive('foo')->once()->andReturn('bar')->getMock();
        });

        $this->assertEquals('bar', $manager->foo());
    }

    /** @test */
    function it_makes_a_new_parser_instance()
    {
        $manager = new Manager;
        $parser = $manager->makeParser($config = ['foo' => 'bar']);

        $this->assertInstanceOf(Parser::class, $parser);
        $this->assertNotSame($parser, $manager->defaultParser());
        $this->assertEquals('bar', $parser->environment()->getConfig('foo'));
    }

    /** @test */
    function parser_instances_can_be_saved_and_retrieved()
    {
        $manager = new Manager;

        try {
            $parser = $manager->parser('a');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Markdown parser [a] is not defined.', $e->getMessage());
        }

        $parserA = null;
        $manager->extend('a', function ($parser) use (&$parserA) {
            return $parserA = $parser;
        });

        $parserB = null;
        $manager->extend('b', function ($parser) use (&$parserB) {
            return $parserB = $parser;
        });

        $this->assertSame($parserA, $manager->parser('a'));
        $this->assertNotSame($parserB, $manager->parser('a'));
    }
}
