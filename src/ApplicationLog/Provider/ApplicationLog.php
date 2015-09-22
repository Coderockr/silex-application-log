<?php

namespace ApplicationLog\Provider;

use Monolog\Logger;
use Silex\Application;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\LogglyHandler;
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

            return $logger;
        });
    }

    public function getStreamHandler($config)
    {
        $stream = isset($config['stream']) ? $config['stream'] : 'php://stderr';
        $level = isset($config['level']) ? $config['level'] : 'DEBUG';
        $bubble = isset($config['bubble']) ? $config['bubble'] : true;
        $filePermission = isset($config['filePermission']) ? $config['filePermission'] : null;
        $useLocking = isset($config['useLocking']) ? $config['useLocking'] : false;

        return new StreamHandler($stream, $this->translateLevel($level), $bubble, $filePermission, $useLocking);
    }

    public function getSlackHandler($config)
    {
        $token = isset($config['token']) ? $config['token'] : '';
        $channel = isset($config['channel']) ? $config['channel'] : '';
        $username = isset($config['username']) ? $config['username'] : 'Monolog';
        $useAttachment = isset($config['useAttachment']) ? $config['useAttachment'] : true;
        $iconEmoji = isset($config['iconEmoji']) ? $config['iconEmoji'] : null;
        $level = isset($config['level']) ? $config['level'] : 'CRITICAL';
        $bubble = isset($config['bubble']) ? $config['bubble'] : true;
        $useShortAttachment = isset($config['useShortAttachment']) ? $config['useShortAttachment'] : false;
        $includeContextAndExtra = isset($config['includeContextAndExtra']) ? $config['includeContextAndExtra'] : false;

        return new SlackHandler(
            $token,
            $channel,
            $username,
            $useAttachment,
            $iconEmoji,
            $this->translateLevel($level),
            $bubble,
            $useShortAttachment,
            $includeContextAndExtra
        );
    }

    public function getLogglyHandler($config)
    {
        $token = isset($config['token']) ? $config['token'] : '';
        $level = isset($config['level']) ? $config['level'] : 'DEBUG';
        $bubble = isset($config['bubble']) ? $config['bubble'] : true;

        $logglyhandler = new LogglyHandler($token, ApplicationLog::translateLevel($level), $bubble);

        return $logglyhandler;
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
