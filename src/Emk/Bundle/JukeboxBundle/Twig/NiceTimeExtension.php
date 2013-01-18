<?php
namespace Emk\Bundle\JukeboxBundle\Twig;

use Twig_Extension;
use Twig_Filter_Method;

class NiceTimeExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array
        (
                'niceTime' => new Twig_Filter_Method($this, 'niceTimeFilter', array('is_safe' => array('html')))
        );
    }

    /**
     * réduit une chaine de caractères sans couper les mots
     *
     * @param string $duree  durée à transformer
     * @param string $format format dans lequel retourner la date si pas transformée
     *
     * @return string date plus nice à lire
     */
    public function niceTimeFilter($duree, $format = 'i:s')
    {
        $t_minute = 60;
        $minute = floor($duree / $t_minute);
        $seconde = $duree - ($minute * $t_minute);

        return str_pad($minute, 2, "0", STR_PAD_LEFT).':'.str_pad($seconde, 2, "0", STR_PAD_LEFT);
    }

    public function getName()
    {
        return 'niceTime_extension';
    }
}
