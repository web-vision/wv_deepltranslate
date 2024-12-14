<?php

return [
    'frontend' => [
        'typo3/json-response/backend-user-authentication' => [
            /**
             * Replace {@see \TYPO3\JsonResponse\Middleware\BackendUserHandler} as target here to incorporate
             * `typo3/testing-framework` pull-request https://github.com/TYPO3/testing-framework/pull/536 as
             * a workaround until resolved within testing-framework.
             *
             * @todo Remove test-fixture extension completely when fixed within typo3/testing-framework. Also requires
             *       temporary workaround in {@see Build/phpunit/FunctionalTestsBootstrap.php} to force framework
             *       extension loading to have have package information available - otherwise dependency ordering would
             *       not work. Needs to be resolved in the testing-framework.
             */
            'target' => \WebVision\TestingFrameworkBackendUserHandlerReplacement\Middleware\BackendUserHandler::class,
        ],
    ],
];
