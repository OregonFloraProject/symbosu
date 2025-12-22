<?php
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class MySqlCast extends FunctionNode
{
    public static string $dqlName = 'CAST';
    public $input;
    public $type;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        // $this->type->value to avoid dispatch adding single quotes
        $sql = 'CAST(' . 
            $this->input->dispatch($sqlWalker) . ' as ' .
            $this->type->value . ')';
        return $sql;
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->input = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->type = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
?>