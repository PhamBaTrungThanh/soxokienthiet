<!-- <?php
        ini_set("memory_limit", -1);
        require_once 'vendor/autoload.php';

        use Illuminate\Container\Container;
        use Illuminate\Events\Dispatcher;
        use Illuminate\Http\Request;
        use Illuminate\Routing\Redirector;
        use Illuminate\Routing\Router;
        use Illuminate\Routing\UrlGenerator;
        use Illuminate\Database\Capsule\Manager as Capsule;
        use Jenssegers\Blade\Blade;

        $dotenv = new Dotenv\Dotenv(__DIR__);
        $dotenv->load();

        // Create a service container
        $container = new Container;
        // Create a request from server variables, and bind it to the container; optional
        $request = Request::capture();
        $container->instance('Illuminate\Http\Request', $request);
        $events = new Dispatcher($container);
        // Create the router instance
        $router = new Router($events, $container);
        // Load the routes
        require_once 'routes.php';



        $redis = new Predis\Client();

        function redis()
        {
            global $redis;
            return $redis;
        }

        // Dispatch the request through the router
        $response = $router->dispatch($request);
        // Send the response back to the browser
        $response->send();




        function redirect()
        {
            // Create the redirect instance
            global $router, $request;
            $redirect = new Redirector(new UrlGenerator($router->getRoutes(), $request));
            return $redirect;
        }

        function view($name, $variables)
        {
            $blade = new Blade('view', 'cache');
            echo $blade->make($name, $variables);
        }

        if (!function_exists('dd')) {
            function dd()
            {
                array_map(function ($x) {
                    dump($x);
                }, func_get_args());
                die;
            }
        }
