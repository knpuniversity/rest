<?php

namespace KnpU\CodeBattle;

use KnpU\CodeBattle\Twig\BattleExtension;
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
use KnpU\CodeBattle\Repository\ProgrammerRepository;

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
        // URL generation
        $this->register(new UrlGeneratorServiceProvider());

        // Twig
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $this['root_dir'].'/views',
        ));
        $app['twig'] = $this->share($this->extend('twig', function(\Twig_Environment $twig, $app) {
            $twig->addExtension($app['twig.battle_extension']);

            return $twig;
        }));

        // Sessions
        $this->register(new SessionServiceProvider());

        // Doctrine DBAL
        $this->register(new DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'   => 'pdo_sqlite',
                'path'     => $this['sqlite_path']
            ),
        ));

        // Monolog
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
            $repo = new UserRepository($app['db']);
            $repo->setEncoderFactory($app['security.encoder_factory']);

            return $repo;
        });
        $this['repository.programmer'] = $this->share(function() use ($app) {
            return new ProgrammerRepository($app['db']);
        });

        $this['fixtures_manager'] = $this->share(function () use ($app) {
            return new FixturesManager($app);
        });

        $this['twig.battle_extension'] = $this->share(function() use ($app) {
            return new BattleExtension($app['request_stack']);
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
            array('^/register', 'IS_AUTHENTICATED_ANONYMOUSLY'),
            array('^/login', 'IS_AUTHENTICATED_ANONYMOUSLY'),
            array('^/', 'IS_AUTHENTICATED_FULLY'),
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