<?php

$systemConfig = \Pimcore\Config::getSystemConfiguration();
$emailRecipients = array_filter(preg_split('/,|;/', (string)$systemConfig['applicationlog']['mail_notification']['mail_receiver']));

return [
    'general' => [
        'archive_treshold_logs' => 7, //keep monitoring items for x Days
        'executeWithMaintenance' => false, //do execute with maintenance (deactivate if you set up a separate cronjob)
        'processTimeoutMinutes' => 600, //if no update of the monitoring item is done within this amount of minutes the process is considered as "hanging"
        //'additionalScriptExecutionUsers' => ['deployer', 'vagrant']
        //'disableShortcutMenu' => true, //disables the shortcut menu on the left side in the admin interface
    ],
    'email' => [
        'recipients' => $emailRecipients, //gets a reporting e-mail when a process is dead
    ],
    'executorClasses' => [
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\PimcoreCommand'
        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\CliCommand'
        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\ClassMethod'
        ]
    ],
    'executorLoggerClasses' => [
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\File'

        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\Console'
        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\Application'
        ],
        [
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\EmailSummary"
        ]
    ],
    'executorActionClasses' => [
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\Download'
        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\OpenItem'
        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\JsEvent'
        ]
    ],
    'executorCallbackClasses' => [
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Callback\\ExecutionNote'
        ],
        [
            "name" => "scheduledExport",
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Callback\\General",
            "extJsClass" => "pimcore.plugin.DivanteScheduledExportBundle.processmanager.executor.callback.scheduledexport",
        ],
    ]
];
