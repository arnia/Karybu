<?php
// florin, 3/4/13, 12:55 PM
namespace GlCMS\Module\Shop\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use GlCMS\Module\Shop\DependencyInjection\Module\Extension;

class ShopExtension extends Extension
{
    public function getAlias()
    {
        return 'shop';
    }
}