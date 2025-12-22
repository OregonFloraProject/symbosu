<?php
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class MySqlRegexReplace extends FunctionNode
{
    public static string $dqlName = 'REGEXREPLACE';
    public $input;
    public $regex;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $sql = 'REGEXP_SUBSTR(' . 
            $this->input->dispatch($sqlWalker) . ', ' .
            $this->regex->dispatch($sqlWalker) . ')';
        return $sql;
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->input = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->regex = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
?>