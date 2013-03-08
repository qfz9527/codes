<?php

namespace SanchoBBDO\Codes\Coder;

use SanchoBBDO\Codes\Utils;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class Coder implements CoderInterface
{
    private $secretKey;

    public function __construct($config = array())
    {
        $processor = new Processor();
        $config = $processor->processConfiguration($this, array($config));

        $this->secretKey = $config['secret_key'];
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('coder');
        $rootNode
            ->children()
                ->scalarNode('secret_key')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();
        return $treeBuilder;
    }

    public function encode($digit)
    {
        $key = Utils::base36Encode($digit);
        $key = Utils::zerofill($key, 4);

        return $key.substr($this->encrypt($key), 0, 6);
    }

    public function isValid($code)
    {
        list($digit, $mac) = $this->parse($code);
        return $this->encode($digit) == $code;
    }

    public function parse($code)
    {
        return array(
            Utils::base36Decode(substr($code, 0, 4)),
            substr($code, 4)
        );
    }

    protected function encrypt($key)
    {
        return sha1($key.$this->getSecretKey());
    }

    public function getSecretKey()
    {
        return $this->secretKey;
    }
}