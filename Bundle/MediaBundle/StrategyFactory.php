<?php

namespace SmImageServer\Bundle\MediaBundle;

use Shopware\Bundle\MediaBundle\Strategy\StrategyFactory as BaseStrategyFactory;

class StrategyFactory extends BaseStrategyFactory
{
    /**
     * @var BaseStrategyFactory
     */
    private $factory;

    /**
     * @var ImageServerStrategy
     */
    private $strategy;

    public function __construct(BaseStrategyFactory $factory, ImageServerStrategy $strategy)
    {
        $this->factory  = $factory;
        $this->strategy = $strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function factory($strategy)
    {
        if ($strategy === 'ImageServer') {
            return $this->strategy;
        }

        return $this->factory->factory($strategy);
    }
}