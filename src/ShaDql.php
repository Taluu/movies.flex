<?php
namespace App;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * Implements sha1 for DQL
 *
 * "SHA1" "(" StringPrimary ")"
 *
 * @link https://stackoverflow.com/a/25104669/624544
 */
class ShaDql extends FunctionNode
{
    private $value;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->value = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return "sha1({$this->value->dispatch($sqlWalker)})";
    }
}
