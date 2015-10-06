#Silex Application Log

###require

`"silex/silex": "v1.3.4"` and `"monolog/monolog": "^1.17"`

###Install

    php composer.phar require coderockr/silex-application-log

###Configuration

Keys `processor`, `streamHandler`, `slackHandler` and `logglyHandler` opcional

    $config = array(
        'config' => array(
            'applicationLog' => array(
                'name' => 'Name You Log',
                'processor' => array( //opcional
                    'Monolog\Processor\IntrospectionProcessor',
                    'Monolog\Processor\MemoryUsageProcessor',
                    'Monolog\Processor\ProcessIdProcessor',
                    'Monolog\Processor\WebProcessor',
                ),
                'streamHandler' => array(
                    'stream' => 'path/to/application.log',
                    'level' => 'DEBUG', //opcional
                    'bubble' => true, //opcional
                    'filePermission' => null, //opcional
                    'useLocking' => false //opcional
                ),
                'slackHandler' => array(
                    'token' => '1234567890',
                    'channel' => '#tests',
                    'username' => 'tests', //opcional
                    'useAttachment' => 'attach user', //opcional
                    'iconEmoji' => ':sweat:', //opcional
                    'level' => 'CRITICAL', //opcional
                    'bubble' => true, //opcional
                    'useShortAttachment' => false, //opcional
                    'includeContextAndExtra' => false //opcional
                ),
                'logglyHandler' => array(
                    'token' => '1234567890',
                    'level' => 'ERROR', //opcional
                    'bubble' => true //opcional
                )
            )
        )
    );

###Usage

    $app = new Application();
    $app->register(new \ApplicationLog\Provider\ApplicationLog(), $config);
