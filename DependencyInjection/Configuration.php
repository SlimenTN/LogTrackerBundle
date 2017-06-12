<?php

namespace SBC\LogTrackerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('log_tracker');

        /**
         * Logic of LogTracker configuration
         * app_name: The name of the application
         * sender_mail: Email of the sender of this application (Without it Swift_Mailer won't work)
         * recipients: List of mails that will receive notification when an exception rise
         */
        $rootNode
            ->children()
                ->scalarNode('app_name')->end()
                ->scalarNode('sender_mail')->end()
                ->scalarNode('handler_text')->end()
                ->arrayNode('recipients')
                    ->info('List of mails that will receive a notification on the rise of any exception.')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
