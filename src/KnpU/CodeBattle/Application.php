<?php

namespace KnpU\CodeBattle;

use Silex\Application as SilexApplication;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\DataFixtures\FixturesManager;
use Silex\Provider\SecurityServiceProvider;
use KnpU\CodeBattle\Repository\UserRepository;

class Application extends SilexApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureParameters();
        $this->configureProviders();
        $this->configureServices();
        $this->configureSecurity();
        $this->registerListeners();
    }

    private function configureProviders()
    {
        $this->register(new UrlGeneratorServiceProvider());

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $this['root_dir'].'/views',
        ));

        $this->register(new SessionServiceProvider());

        $this->register(new DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'   => 'pdo_sqlite',
                'path'     => $this['sqlite_path']
            ),
        ));

        $this->register(new MonologServiceProvider(), array(
            'monolog.logfile' => $this['root_dir'].'/logs/development.log',
        ));
    }

    private function configureParameters()
    {
        $this['root_dir'] = __DIR__.'/../../..';
        $this['sqlite_path'] = $this['root_dir'].'/data/code_battles.sqlite';
    }

    private function configureServices()
    {
        $app = $this;

        $this['repository.user'] = $this->share(function() use ($app) {
            return new UserRepository($app['db']);
        });

        $this['fixtures_manager'] = $this->share(function () use ($app) {
            return new FixturesManager($app);
        });
    }

    private function configureSecurity()
    {
        $app = $this;

        $this->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'main' => array(
                    'pattern' => '^/',
                    'form' => true,
                    'users' => $this->share(function () use ($app) {
                        return $app['repository.user'];
                    }),
                    'anonymous' => true,
                    'logout' => true,
                ),
            )
        ));

        // require login for application management
        $this['security.access_rules'] = array(
            // placeholder access control for now
            array('^/foo', 'IS_AUTHENTICATED_FULLY'),
        );
    }

    private function registerListeners()
    {
        $app = $this;

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this['dispatcher'];
        // a quick event listener to inject the container into our BaseController
        $dispatcher->addListener(
            KernelEvents::CONTROLLER,
            function (FilterControllerEvent $event) use ($app) {
                $controller = $event->getController();
                if (!is_array($controller)) {
                    return;
                }

                $controllerObject = $controller[0];
                if ($controllerObject instanceof BaseController) {
                    $controllerObject->setContainer($app);
                }
            }
        );
    }
} 