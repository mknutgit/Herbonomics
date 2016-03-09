<?php
    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/Dispensary.php";
    require_once __DIR__."/../src/DispensaryDemand.php";
    require_once __DIR__."/../src/Grower.php";
    require_once __DIR__."/../src/GrowersStrains.php";

    $app['debug'] = true;
    $server = 'mysql:host=localhost;dbname=herbonomics';
    $username = 'root';
    $password = 'root';
    $DB = new PDO($server, $username, $password);

    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    $app = new Silex\Application();

    use Symfony\Component\Debug\Debug;


    $app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views'
    ));
 // instantiate Silex app, add twig capability to app


    $app->get("/", function() use ($app) {
        //home page
        return $app['twig']->render('index.html.twig');
    });

    $app->get("/sign_up", function() use ($app) {
        //home page
        return $app['twig']->render('sign_up.html.twig');
    });

    $app->get("/dispensary/sign_in", function() use ($app) {//get or post?

        $dispensary = Dispensary::signIn($_GET['username'], $_GET['password']);

        if ($dispensary == null) {
            return $app['twig']->render('index.html.twig');
        } else

        $demands =
        DispensaryDemand::findByDispensary($dispensary->getId());

        return $app['twig']->render('dispensary_account.html.twig', array('dispensary' => $dispensary, 'demands' => $demands));
    });

    $app->get("/grower/sign_in", function() use ($app) {//get or post?

        $grower = Grower::signIn($_GET['username'], $_GET['password']);


        if ($grower == null) {
            return $app['twig']->render('index.html.twig');
        } else

        $strains = GrowersStrains::findByGrower($grower->getId());
        return $app['twig']->render('grower_account.html.twig', array(
            'grower' => $grower,
            'strains' => $strains
        ));
    });

    //*takes user to the individual grower account page*//
    $app->get("/grower/{id}/account", function() use ($app) {
        $grower = Grower::find($id);
        return $app['twig']->render('grower_account.html.twig', array('$grower' => $grower));
    });

    //*takes user to the edit account information page*//
    $app->get("/grower/{id}/edit_account_info", function($id) use ($app) {
        $grower = Grower::findById($id);

        return $app['twig']->render('grower_edit_account_info.html.twig', array('grower' => $grower));
    });

    //*takes grower to the add strain page*//
    $app->get("/grower/{id}/add_strain", function($id) use ($app) {
        $grower = Grower::findById($id);
        return $app['twig']->render('grower_strain_add.html.twig', array('grower' => $grower));
    });

    //*takes grower to the edit strain information page*//
    $app->get("/strain/{id}/edit_strain", function($id) use ($app) {
        $strain = GrowersStrains::findById($id);

        return $app['twig']->render('grower_strain_edit.html.twig', array('strain' => $strain));
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
        $grower = new Grower($id = null, $name, $website, $email, $username, $password);
        $grower->save();

        $strains = array();

        return $app['twig']->render('grower_account.html.twig', array(
            'grower' => $grower,
            'strains' => $strains
        ));
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

    //*adds new strain to growers inventory returns to growers account page*//
    $app->post("/grower/add_strain", function() use ($app) {//get or post?
        $strain_name = $_POST['strain_name'];
        $pheno = $_POST['pheno'];
        $thc = $_POST['thc'];
        $cbd = $_POST['cbd'];
        $cgc = $_POST['cgc'];
        $price = $_POST['price'];
        $growers_id = $_POST['growers_id'];
        $grower_strain = new GrowersStrains($id = null, $strain_name, $pheno, $thc, $cbd, $cgc, $price, $growers_id);
        $grower_strain->save();

        $grower = Grower::findById($growers_id);
        $strains = GrowersStrains::findByGrower($growers_id);

        return $app['twig']->render('grower_account.html.twig', array('grower' => $grower, 'strains' => $strains));
    });

    $app->get("/dispensary_demand/{id}/edit", function($id) use ($app) {
        $demand = DispensaryDemand::find($id);
        return $app['twig']->render('dispensary_demand_edit.html.twig', array('demand' => $demand));
    });

    $app->patch("/dispensary/{id}/edit_post", function($id) use ($app) {
        $demand = DispensaryDemand::find($id);
        $demand->update($_POST['strain_name'], $_POST['pheno'], $_POST['quantity']);

        $dispensary = Dispensary::find($demand->getDispensaryId());
        $demands = DispensaryDemand::findByDispensary($demand->getDispensaryId());

        return $app['twig']->render('dispensary_account.html.twig', array('dispensary' => $dispensary, 'demands' => $demands));
    });

    $app->get("/demand/{id}/delete", function($id) use ($app) {
        $demand = DispensaryDemand::find($id);
        $demand_id = $demand->getDispensaryId();
        $demand->delete();
        $dispensary = Dispensary::find($demand_id);
        $demands = DispensaryDemand::findByDispensary($demand_id);

        return $app['twig']->render('dispensary_account.html.twig', array('dispensary' => $dispensary, 'demands' => $demands));
    });

    $app->get("/dispensary/{id}/edit_account_info", function($id) use ($app) {
        $dispensary = Dispensary::find($id);
        return $app['twig']->render('dispensary_edit_account_info.html.twig', array('dispensary' => $dispensary));
    });

    $app->patch("/dispensary/{id}/edit_account_info", function($id) use ($app) {
        $dispensary = Dispensary::find($id);
        $dispensary->update($_POST['name'], $_POST['website'], $_POST['email'], $_POST['username'], $_POST['password']);

        $demands = DispensaryDemand::findByDispensary($id);

        return $app['twig']->render('dispensary_account.html.twig', array('dispensary' => $dispensary, 'demands' => $demands));
    });

    //*Updates grower account detail information and routes back to individual account home*//
    $app->patch("/grower/{id}/edit_account_info", function($id) use ($app) {
        $grower = Grower::findById($id);

        $grower->update($_POST['name'], $_POST['website'], $_POST['email'], $_POST['username'], $_POST['password']);

        $strains = GrowersStrains::findByGrower($id);

        return $app['twig']->render('grower_account.html.twig', array('grower' => $grower, 'strains' => $strains));
    });

    //* Update to capture grower ID and be returned to the correct growers account page*//
    $app->patch("/strain/{id}/edit_strain", function($id) use ($app) {
        $strain = GrowersStrains::findById($id);

        $strain->update($_POST['strain_name'], $_POST['pheno'], $_POST['thc'], $_POST['cbd'], $_POST['cgc'], $_POST['price']);

        $grower = Grower::findById($strain->getGrowersId());
        $strains = GrowersStrains::findByGrower($strain->getGrowersId());

        return $app['twig']->render('grower_account.html.twig', array('grower' => $grower, 'strains' => $strains));
    });

    $app->get("/allstrains", function() use ($app) {
        //all strains page
        $strains = GrowersStrains::getAll();
        return $app['twig']->render('grower_supply.html.twig', array(
            'strains' => $strains
        ));
    });

    $app->get("/grower_supply/search", function() use ($app) {
        //all strains page
        $strains = GrowersStrains::search($_GET['search']);
        return $app['twig']->render('grower_supply.html.twig', array(
            'strains' => $strains
        ));
    });

    $app->get("/dispensary_demands", function() use ($app) {
        $demands = DispensaryDemand::getAll();
        return $app['twig']->render('dispensary_demand.html.twig', array('demands' => $demands));
    });

    $app->get("/dispensary_demands/search", function() use ($app) {
        $demands = DispensaryDemand::search($_GET['search']);

        return $app['twig']->render('dispensary_demand.html.twig', array('demands' => $demands));
    });



    return $app;
?>
