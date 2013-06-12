<?php
// florin, 6/7/13, 5:11 PM
namespace Karybu\Twig\Extension\Tag;

use Karybu\Twig\Extension\Node\LoadNode;

class LoadTokenParser extends \Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
        $path = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
        //$stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
        $value = $parser->getExpressionParser()->parseExpression();
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new LoadNode($name, $value, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'load';
    }
}