[![Codacy Badge](https://api.codacy.com/project/badge/84ca7d0251cb4be8ae819ada7e973118)](https://www.codacy.com/app/eminetto/silex-application-log)

# Silex Application Log

### Require

`"silex/silex": "v1.3.4"` and `"monolog/monolog": "^1.17"`

### Install

    php composer.phar require coderockr/silex-application-log

### Configuration

Keys `processor`, `streamHandler`, `slackHandler` and `logglyHandler` opcional

    $config = [
        'config' => [
            'applicationLog' => [
                'name' => 'Name You Log',
                'processor' => [ //opcional
                    'Monolog\Processor\IntrospectionProcessor',
                    'Monolog\Processor\MemoryUsageProcessor',
                    'Monolog\Processor\ProcessIdProcessor',
                    'Monolog\Processor\WebProcessor',
                ],
                'streamHandler' => [
                    'stream' => 'path/to/application.log',
                    'level' => 'DEBUG', //opcional
                    'bubble' => true, //opcional
                    'filePermission' => null, //opcional
                    'useLocking' => false //opcional
                ],
                'slackHandler' => [
                    'token' => '1234567890',
                    'channel' => '#tests',
                    'username' => 'tests', //opcional
                    'useAttachment' => 'attach user', //opcional
                    'iconEmoji' => ':sweat:', //opcional
                    'level' => 'CRITICAL', //opcional
                    'bubble' => true, //opcional
                    'useShortAttachment' => false, //opcional
                    'includeContextAndExtra' => false //opcional
                ],
                'logglyHandler' => [
                    'token' => '1234567890',
                    'level' => 'ERROR', //opcional
                    'bubble' => true //opcional
                ],
                'sentryHandler' => [
                    'token' => '1234567890',
                    'level' => 'ERROR', //opcional
                ],
            ]
        ]
    ];

### Usage

    $app = new Application();
    $app->register(new \ApplicationLog\Provider\ApplicationLog(), $config);
