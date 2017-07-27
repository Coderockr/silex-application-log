<?php

namespace ApplicationLog\Provider;

use Monolog\Logger;
use Silex\Application;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\RavenHandler;
use Silex\ServiceProviderInterface;

class ApplicationLog implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['logger'] = function () use ($app) {
            return $app['monolog'];
        };

        $app['monolog'] = $app->share(function () use ($app) {

            if (!isset($app['config']) || !isset($app['config']['applicationLog'])) {
                throw new \InvalidArgumentException("Config error");
            }

            $name = 'applicationLog';
            if (isset($app['config']['applicationLog']['name'])) {
                $name = $app['config']['applicationLog']['name'];
            }

            $processors = [];
            if (isset($app['config']['applicationLog']['processor'])) {
                $processor = $app['config']['applicationLog']['processor'];

                if (is_array($processor)) {
                    foreach ($processor as $value) {
                        $processors[] = new $value();
                    }
                }

                if (is_string($processor)) {
                    $processors[] = new $processor();
                }
            }

            $logger = new Logger($name, [], $processors);

            if (isset($app['config']['applicationLog']['streamHandler'])) {
                $logger->pushHandler($this->getStreamHandler($app['config']['applicationLog']['streamHandler']));
            }

            if (isset($app['config']['applicationLog']['slackHandler'])) {
                $logger->pushHandler($this->getSlackHandler($app['config']['applicationLog']['slackHandler']));
            }

            if (isset($app['config']['applicationLog']['logglyHandler'])) {
                $logger->pushHandler($this->getLogglyHandler($app['config']['applicationLog']['logglyHandler']));
            }

            if (isset($app['config']['applicationLog']['sentryHandler'])) {
                $logger->pushHandler($this->getSentryHandler($app['config']['applicationLog']['sentryHandler']));
            }

            return $logger;
        });
    }

    public function getStreamHandler($config)
    {
        $default = [
            'stream' => 'php://stderr',
            'level' => 'DEBUG',
            'bubble' => true,
            'filePermission' => null,
            'useLocking' => false
        ];

        $data = array_merge($default, $config);

        return new StreamHandler(
            $data['stream'],
            $this->translateLevel($data['level']),
            $data['bubble'],
            $data['filePermission'],
            $data['useLocking']
        );
    }

    public function getSlackHandler($config)
    {
        $default = [
            'token' => null,
            'channel' => null,
            'username' => 'Monolog',
            'useAttachment' => true,
            'iconEmoji' => null,
            'level' => 'CRITICAL',
            'bubble' => true,
            'useShortAttachment' => false,
            'includeContextAndExtra' => false
        ];

        $data = array_merge($default, $config);

        return new SlackHandler(
            $data['token'],
            $data['channel'],
            $data['username'],
            $data['useAttachment'],
            $data['iconEmoji'],
            $this->translateLevel($data['level']),
            $data['bubble'],
            $data['useShortAttachment'],
            $data['includeContextAndExtra']
        );
    }

    public function getSentryHandler($config)
    {
        $default = [
            'token' => null,
            'level' => 'CRITICAL',
            'includeContextAndExtra' => true,
            'bubble' => true,
        ];

        $data = array_merge($default, $config);

        $client = new \Raven_Client($data['token']);

        return new RavenHandler(
            $client,
            $this->translateLevel($data['level']),
            $data['bubble']
        );
    }

    public function getLogglyHandler($config)
    {
        $default = [
            'token' => null,
            'level' => 'DEBUG',
            'bubble' => true
        ];

        $data = array_merge($default, $config);

        return new LogglyHandler($data['token'], $this->translateLevel($data['level']), $data['bubble']);
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }

    public static function translateLevel($name)
    {
        if (is_int($name)) {
            return $name;
        }

        $levels = Logger::getLevels();
        $upper = strtoupper($name);

        if (!isset($levels[$upper])) {
            throw new \InvalidArgumentException("Provided logging level '$name' does not exist. Must be a valid monolog logging level.");
        }

        return $levels[$upper];
    }
}
