
<?php
    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/Dispensary.php";
    require_once __DIR__."/../src/DispensaryDemand.php";
    require_once __DIR__."/../src/Grower.php";
    require_once __DIR__."/../src/GrowersStrains.php";


    $server = 'mysql:host=localhost;dbname=herbonomics';
    $username = 'root';
    $password = 'root';
    $DB = new PDO($server, $username, $password);

    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    $app = new Silex\Application();

    use Symfony\Component\Debug\Debug;
    $app['debug'] = true;

    $app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views'
    ));
 // instantiate Silex app, add twig capability to app


    $app->get("/", function() use ($app) {
        //home page
        return $app['twig']->render('index.html.twig');
    });

    $app->get("/login", function() use ($app) {
        return $app['twig']->render('login.html.twig');
    });

    $app->get("/sign_up", function() use ($app) {
        return $app['twig']->render('sign_up.html.twig');
    });

    $app->get("/dispensary/sign_in", function() use ($app) {//get or post?

        $dispensary = Dispensary::signIn($_GET['username'], $_GET['password']);

        if ($dispensary == null) {
            return $app['twig']->render('login.html.twig');
        } else

        $demands =
        DispensaryDemand::findByDispensary($dispensary->getId());

        return $app['twig']->render('dispensary_account.html.twig', array('dispensary' => $dispensary, 'demands' => $demands));
    });

    $app->get("/grower/sign_in", function() use ($app) {//get or post?

        $grower = Grower::signIn($_GET['username'], $_GET['password']);

        if ($grower == null) {
            return $app['twig']->render('login.html.twig');
        } else

        return $app['twig']->render('grower_account.html.twig', array('grower' => $grower));
    });

    $app->post("/dispensary/sign_up", function() use ($app) {//get or post?
        $name = $_POST['name'];
        $website = $_POST['website'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $dispensary = new Dispensary($name, $website, $email, $username, $password);
        $dispensary->save();

        $demands = array();

        return $app['twig']->render('dispensary_account.html.twig', array('dispensary' => $dispensary, 'demands' => $demands));
    });

    $app->post("/grower/sign_up", function() use ($app) {//get or post?
        $name = $_POST['name'];
        $website = $_POST['website'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $grower = new Grower($name, $website, $email, $username, $password);
        $grower->save();

        return $app['twig']->render('grower_account.html.twig', array('grower' => $grower));
    });

    $app->post("/dispensary/demand_add", function() use ($app) {//get or post?
        $strain_name = $_POST['strain_name'];
        $pheno = $_POST['pheno'];
        $dispensary_id = $_POST['dispensary_id'];
        $quantity = $_POST['quantity'];
        $dispensary_demand = new DispensaryDemand($strain_name, $dispensary_id, $pheno, $quantity);
        $dispensary_demand->save();

        $dispensary = Dispensary::find($dispensary_id);
        $demands = DispensaryDemand::findByDispensary($dispensary_id);

        return $app['twig']->render('dispensary_account.html.twig', array('dispensary' => $dispensary, 'demands' => $demands));
    });


    return $app;
?>
