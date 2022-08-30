<?php

namespace App\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ByteConversionTwigExtension extends AbstractExtension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [new TwigFilter('format_bytes', [$this, 'formatBytes'])];
    }

    /**
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public function formatBytes($bytes, $precision = 0)
    {
        $size = ['Byte', 'KByte', 'MByte', 'GByte', 'TByte', 'PByte', 'EByte', 'ZByte', 'YByte'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$precision}f", $bytes / pow(1000, $factor)) . ' ' . @$size[$factor];
    }

}