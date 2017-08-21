<?php

namespace Application\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Application\Doctrine\DQL\AnyValue
 */
class AnyValue extends FunctionNode
{
    protected $value; // la valeur  passée en paramètre de la fction ANY_VALUE()

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); //identifie la fonction ANY_VALUE() de mysql
        $parser->match(Lexer::T_OPEN_PARENTHESIS); //parenthèse ouvrante
        $this->value = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);////parenthèse fermante
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'ANY_VALUE(' . $this->value->dispatch( $sqlWalker ) . ')';
    }
}
