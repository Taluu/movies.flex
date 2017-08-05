<?php
namespace App;

use ReflectionProperty;

use PHPUnit\Framework\TestCase;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\Literal;

class ShaDqlTest extends TestCase
{
    public function testParse()
    {
        $parser = $this->prophesize(Parser::class);
        $parser->match(Lexer::T_IDENTIFIER)->shouldBeCalled();
        $parser->match(Lexer::T_OPEN_PARENTHESIS)->shouldBeCalled();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)->shouldBeCalled();
        $parser->StringPrimary()->willReturn('foo')->shouldBeCalled();

        $function = new ShaDql('foo');
        $function->parse($parser->reveal());

        $this->assertAttributeSame('foo', 'value', $function);
    }

    public function testGetSql()
    {
        $function = new ShaDql('foo');
        $literal = new Literal('foo', 'bar');

        $walker = $this->prophesize(SqlWalker::class);
        $walker->walkLiteral($literal)->willReturn('\'baz\'')->shouldBeCalled();

        // have to reflect on ShaDql to set a value for $value attribute
        $refl = new ReflectionProperty(ShaDql::class, 'value');
        $refl->setAccessible(true);
        $refl->setValue($function, $literal);

        $this->assertSame('sha1(\'baz\')', $function->getSql($walker->reveal()));
    }
}
