<?php

namespace KnpU\CodeBattle;

use Doctrine\Common\Annotations\AnnotationReader;
use KnpU\CodeBattle\Battle\PowerManager;
use KnpU\CodeBattle\Repository\BattleRepository;
use KnpU\CodeBattle\Repository\ProjectRepository;
use KnpU\CodeBattle\Repository\RepositoryContainer;
use KnpU\CodeBattle\Security\Authentication\ApiEntryPoint;
use KnpU\CodeBattle\Security\Authentication\ApiTokenListener;
use KnpU\CodeBattle\Security\Authentication\ApiTokenProvider;
use KnpU\CodeBattle\Security\Token\ApiTokenRepository;
use KnpU\CodeBattle\Twig\BattleExtension;
use KnpU\CodeBattle\Validator\ApiValidator;
use Silex\Application as SilexApplication;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use KnpU\CodeBattle\DataFixtures\FixturesManager;
use Silex\Provider\SecurityServiceProvider;
use KnpU\CodeBattle\Repository\UserRepository;
use KnpU\CodeBattle\Repository\ProgrammerRepository;
use KnpU\CodeBattle\Battle\BattleManager;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;

class Application extends SilexApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureParameters();
        $this->configureProviders();
        $this->configureServices();
        $this->configureSecurity();
        $this->configureListeners();
    }

    /**
     * Dynamically finds all *Controller.php files in the Controller directory,
     * instantiates them, and mounts their routes.
     *
     * This is done so we can easily create new controllers without worrying
     * about some of the Silex mechanisms to hook things together.
     */
    public function mountControllers()
    {
        $controllerPath = 'src/KnpU/CodeBattle/Controller';
        $finder = new Finder();
        $finder->in($this['root_dir'].'/'.$controllerPath)
            ->name('*Controller.php')
        ;

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            // e.g. Api/FooController.php
            $cleanedPathName = $file->getRelativePathname();
            // e.g. Api\FooController.php
            $cleanedPathName = str_replace('/', '\\', $cleanedPathName);
            // e.g. Api\FooController
            $cleanedPathName = str_replace('.php', '', $cleanedPathName);

            $class = 'KnpU\\CodeBattle\\Controller\\'.$cleanedPathName;

            // don't instantiate the abstract base class
            $refl = new \ReflectionClass($class);
            if ($refl->isAbstract()) {
                continue;
            }

            $this->mount('/', new $class($this));
        }
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

        // Validation
        $this->register(new ValidatorServiceProvider());
        // configure validation to load from a YAML file
        $this['validator.mapping.class_metadata_factory'] = $this->share(function() {
            return new ClassMetadataFactory(
                new AnnotationLoader($this['annotation_reader'])
            );
        });

        // Translation
        $this->register(new TranslationServiceProvider(), array(
            'locale_fallbacks' => array('en'),
        ));
        $this['translator'] = $this->share($this->extend('translator', function($translator) {
            /** @var \Symfony\Component\Translation\Translator $translator */
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', $this['root_dir'].'/translations/en.yml', 'en');

            return $translator;
        }));
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
            $repo = new UserRepository($app['db'], $app['repository_container']);
            $repo->setEncoderFactory($app['security.encoder_factory']);

            return $repo;
        });
        $this['repository.programmer'] = $this->share(function() use ($app) {
            return new ProgrammerRepository($app['db'], $app['repository_container']);
        });
        $this['repository.project'] = $this->share(function() use ($app) {
            return new ProjectRepository($app['db'], $app['repository_container']);
        });
        $this['repository.battle'] = $this->share(function() use ($app) {
            return new BattleRepository($app['db'], $app['repository_container']);
        });
        $this['repository.api_token'] = $this->share(function() use ($app) {
            return new ApiTokenRepository($app['db'], $app['repository_container']);
        });
        $this['repository_container'] = $this->share(function() use ($app) {
            return new RepositoryContainer($app, array(
                'user' => 'repository.user',
                'programmer' => 'repository.programmer',
                'project' => 'repository.project',
                'battle' => 'repository.battle',
                'api_token' => 'repository.api_token',
            ));
        });

        $this['battle.battle_manager'] = $this->share(function() use ($app) {
            return new BattleManager(
                $app['repository.battle'],
                $app['repository.programmer']
            );
        });
        $this['battle.power_manager'] = $this->share(function() use ($app) {
            return new PowerManager(
                $app['repository.programmer']
            );
        });

        $this['fixtures_manager'] = $this->share(function () use ($app) {
            return new FixturesManager($app);
        });

        $this['twig.battle_extension'] = $this->share(function() use ($app) {
            return new BattleExtension(
                $app['request_stack'],
                $app['repository.programmer'],
                $app['repository.project']
            );
        });

        $this['annotation_reader'] = $this->share(function() {
            return new AnnotationReader();
        });
        // you could use a cache with annotations if you want
        //$this['annotations.cache'] = new PhpFileCache($this['root_dir'].'/cache');
        //$this['annotation_reader'] = new CachedReader($this['annotations_reader'], $this['annotations.cache'], $this['debug']);

        $this['api.validator'] = $this->share(function() use ($app) {
            return new ApiValidator($app['validator']);
        });
    }

    private function configureSecurity()
    {
        $app = $this;

        $this->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'api' => array(
                    'pattern' => '^/api',
                    'users' => $this->share(function () use ($app) {
                        return $app['repository.user'];
                    }),
                    'stateless' => true,
                    'anonymous' => true,
                    'api_token' => true,
                ),
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
            // allow anonymous API - if auth is needed, it's handled in the controller
            array('^/api', 'IS_AUTHENTICATED_ANONYMOUSLY'),
            array('^/', 'IS_AUTHENTICATED_FULLY'),
        );

        // setup our custom API token authentication
        $app['security.authentication_listener.factory.api_token'] = $app->protect(function ($name, $options) use ($app) {

            // the class that reads the token string off of the Authorization header
            $app['security.authentication_listener.'.$name.'.api_token'] = $app->share(function () use ($app) {
                return new ApiTokenListener($app['security'], $app['security.authentication_manager']);
            });

            // the class that looks up the ApiToken object in the database for the given token string
            // and authenticates the user if it's found
            $app['security.authentication_provider.'.$name.'.api_token'] = $app->share(function () use ($app) {
                return new ApiTokenProvider($app['repository.user'], $app['repository.api_token']);
            });

            // the class that decides what should happen if no authentication credentials are passed
            $this['security.entry_point.'.$name.'.api_token'] = $app->share(function() use ($app) {
                return new ApiEntryPoint($app['translator']);
            });

            return array(
                // the authentication provider id
                'security.authentication_provider.'.$name.'.api_token',
                // the authentication listener id
                'security.authentication_listener.'.$name.'.api_token',
                // the entry point id
                'security.entry_point.'.$name.'.api_token',
                // the position of the listener in the stack
                'pre_auth'
            );
        });

        // expose a fake "user" service
        $this['user'] = $this->share(function() use ($app) {
            $user = $app['security']->getToken()->getUser();

            return is_object($user) ? $user : null;
        });
    }

    private function configureListeners()
    {
        // todo
    }
} 