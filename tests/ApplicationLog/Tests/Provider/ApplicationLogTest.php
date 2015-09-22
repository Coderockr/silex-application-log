<?php

namespace ApplicationLog\Tests\Provider;

use Mockery as m;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Logger;
use Silex\Application;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\LogglyHandler;
use ApplicationLog\Provider\ApplicationLog;

class ApplicationLogTest extends \PHPUnit_Framework_TestCase 
{
    public function testCheckIfImplementsServiceProviderInterface()
    {
        $this->assertInstanceOf('Silex\ServiceProviderInterface', new ApplicationLog());
    }

    public function testMethodTranslateLevel()
    {
        $applicationLog = new ApplicationLog();

        $level = $applicationLog->translateLevel(200);
        $this->assertEquals(200, $level);

        $level = $applicationLog->translateLevel('DEBUG');
        $this->assertEquals(100, $level);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Provided logging level 'FOO' does not exist. Must be a valid monolog logging level.
     */
    public function testMethodTranslateLevelReturnException()
    {
        $applicationLog = new ApplicationLog();
        $applicationLog->translateLevel('FOO');
    }

    public function testMethodGetStreamHandler()
    {
        $config = [
            'stream' => 'php://stderr',
            'level' => 'DEBUG',
            'bubble' => true,
            'filePermission' => null,
            'useLocking' => false
        ];

        $applicationLog = new ApplicationLog();
        $stream = $applicationLog->getStreamHandler($config);

        $this->assertInstanceOf(StreamHandler::class, $stream);
    }

    public function testMethodGetStreamHandlerDefaultParameters()
    {
        $config = [];

        $applicationLog = new ApplicationLog();
        $stream = $applicationLog->getStreamHandler($config);

        $this->assertEquals($stream->getLevel(), 100);
        $this->assertEquals($stream->getBubble(), true);

        $this->assertInstanceOf(StreamHandler::class, $stream);
    }

    public function testMethodGetSlackHandler()
    {
        $config = [
            'token' => '1234567890',
            'channel' => '#tests',
            'username' => 'tests',
            'useAttachment' => 'attach user',
            'iconEmoji' => ':foo:',
            'level' => 'CRITICAL',
            'bubble' => true,
            'useShortAttachment' => false,
            'includeContextAndExtra' => false
        ];

        $applicationLog = new ApplicationLog();
        $stream = $applicationLog->getSlackHandler($config);

        $this->assertInstanceOf(SlackHandler::class, $stream);
    }

    public function testMethodGetSlackHandlerDefaultParameters()
    {
        $config = [];

        $applicationLog = new ApplicationLog();
        $stream = $applicationLog->getSlackHandler($config);

        $this->assertEquals($stream->getLevel(), 500);
        $this->assertEquals($stream->getBubble(), true);

        $this->assertInstanceOf(SlackHandler::class, $stream);
    }

    public function testMethodGetLogglyHandler()
    {
        $config = [
            'token' => '1234567890',
            'level' => 'ERROR',
            'bubble' => true
        ];

        $applicationLog = new ApplicationLog();
        $stream = $applicationLog->getLogglyHandler($config);

        $this->assertInstanceOf(LogglyHandler::class, $stream);
        $this->assertInstanceOf(LogglyFormatter::class, $stream->getFormatter());
    }

    public function testMethodGetLogglyHandlerDefaultParameters()
    {
        $config = [];

        $applicationLog = new ApplicationLog();
        $stream = $applicationLog->getLogglyHandler($config);

        $this->assertEquals($stream->getLevel(), 100);
        $this->assertEquals($stream->getBubble(), true);

        $this->assertInstanceOf(LogglyHandler::class, $stream);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Config error
     */
    public function testShareMonologReturnExceptionNotParamete()
    {
        $app = new Application();
        $app->register(new ApplicationLog());
        $app['monolog'];
    }

    public function testNameDefaultLog()
    {
        $config = [
            'config' => [
                'applicationLog' => []
            ]
        ];

        $app = new Application();
        $app->register(new ApplicationLog(), $config);

        /**
         * @var $logger \Monolog\Logger
         */
        $logger = $app['monolog'];

        $this->assertEquals('applicationLog', $logger->getName());
    }
    
    public function testShareMonologConfigStreamHandler()
    {
        $config = [
            'config' => [
                'applicationLog' => [
                    'name' => 'foo',
                    'streamHandler' => []
                ]
            ]
        ];

        $app = new Application();
        $app->register(new ApplicationLog(), $config);

        /**
         * @var $logger \Monolog\Logger
         */
        $logger = $app['monolog'];

        $this->assertEquals('foo', $logger->getName());
        $this->assertInstanceOf(StreamHandler::class, $logger->popHandler());
    }

    public function testShareMonologConfigSlackHandler()
    {
        $config = [
            'config' => [
                'applicationLog' => [
                    'name' => 'foo-slack',
                    'slackHandler' => []
                ]
            ]
        ];

        $app = new Application();
        $app->register(new ApplicationLog(), $config);

        /**
         * @var $logger \Monolog\Logger
         */
        $logger = $app['monolog'];

        $this->assertEquals('foo-slack', $logger->getName());
        $this->assertInstanceOf(SlackHandler::class, $logger->popHandler());
    }

    public function testShareMonologConfigLogglyHandler()
    {
        $config = [
            'config' => [
                'applicationLog' => [
                    'name' => 'foo-loggly',
                    'logglyHandler' => []
                ]
            ]
        ];

        $app = new Application();
        $app->register(new ApplicationLog(), $config);

        /**
         * @var $logger \Monolog\Logger
         */
        $logger = $app['monolog'];

        $this->assertEquals('foo-loggly', $logger->getName());
        $this->assertInstanceOf(LogglyHandler::class, $logger->popHandler());
    }

    public function testShareLogger()
    {
        $config = [
            'config' => [
                'applicationLog' => [
                    'name' => 'foo-looger',
                ]
            ]
        ];

        $app = new Application();
        $app->register(new ApplicationLog(), $config);

        /**
         * @var $logger \Monolog\Logger
         */
        $logger = $app['logger'];

        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testPushProcessorInArray()
    {
        $config = [
            'config' => [
                'applicationLog' => [
                    'processor' => [
                        'Monolog\Processor\IntrospectionProcessor',
                        'Monolog\Processor\MemoryUsageProcessor',
                        'Monolog\Processor\ProcessIdProcessor',
                        'Monolog\Processor\WebProcessor',
                    ],
                ]
            ]
        ];

        $app = new Application();
        $app->register(new ApplicationLog(), $config);

        /**
         * @var $logger \Monolog\Logger
         */
        $logger = $app['monolog'];
        
        $this->assertTrue(!empty($logger->getProcessors()));
        
        foreach ($logger->getProcessors() as $key => $value) {
            $this->assertInstanceOf($config['config']['applicationLog']['processor'][$key], $value);
        }
    }

    public function testPushProcessorNotArray()
    {
        $config = [
            'config' => [
                'applicationLog' => [
                    'processor' => 'Monolog\Processor\IntrospectionProcessor'
                ]
            ]
        ];

        $app = new Application();
        $app->register(new ApplicationLog(), $config);

        /**
         * @var $logger \Monolog\Logger
         */
        $logger = $app['monolog'];

        $this->assertTrue(!empty($logger->getProcessors()));

        foreach ($logger->getProcessors() as $value) {
            $this->assertInstanceOf($config['config']['applicationLog']['processor'], $value);
        }
    }

    public function testMethodBoot()
    {
        $application = new ApplicationLog();
        $boot = $application->boot(m::mock(Application::class));
        
        $this->assertNull($boot);
    }
}
