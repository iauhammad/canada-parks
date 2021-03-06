<?php
#error log
ini_set("error_log", "../error.log");
ini_set("display_errors", 'Off');


require '../lib/DatabaseAccess.php';
require '../lib/park.php';
require '../lib/ParkRepository.php';

$db = DatabaseAccess::getConnection();
$parkRepository = new ParkRepository($db);
$provinces = $parkRepository->getProvinces();
$id = $_GET['id'];
$park1 = $parkRepository->getPark($id);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <?php include "../templates/meta.php"; ?>
        <meta name="author" content="Navpreet">
        <title><?=$park1["name"]?></title>
        <link rel="stylesheet" href="../lib/profile/owl.carousel.min.css">
        <link rel="stylesheet" href="../lib/profile/owl.theme.default.min.css">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="../static/css/footprints.css" />
        <link rel="stylesheet" href="../static/css/park_fix.css" />
        <script src="http://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>
        <script src="../lib/profile/owl.carousel.js"></script>
        <script>
            var iParkId = <?php
                    if(isset($_GET['id'])) {
                        echo $_GET['id'];
                    } else {
                        echo 0;
                    }
              ?>
        </script>
    </head>
    <body>
    <?php
    $postalCode = $park1["postal_code"];
    $postalCode = str_replace(' ', '', $postalCode);
    $url = "http://apidev.accuweather.com/locations/v1/search?q=" . $postalCode . "&apikey=hoArfRosT1215";
    //var_dump($postalCode);
    //$url = "http://apidev.accuweather.com/locations/v1/search?q=P0E 1E0&apikey=hoArfRosT1215";
    $display = json_decode(file_get_contents($url), true);
    $key = $display[0]["Key"];

    $url = "http://apidev.accuweather.com/currentconditions/v1/" . $key .".json?language=en&apikey=hoArfRosT1215";
    //$url = "http://apidev.accuweather.com/locations/v1/search?q=P0E 1E0&apikey=hoArfRosT1215";
    $display = json_decode(file_get_contents($url), true);
    //var_dump($display);
    $temperature = $display[0]["Temperature"];
    //var_dump($temperature);
    $metric = $temperature["Metric"];

    $weatherText = $display[0]["WeatherText"];

    $weatherIcon = $display[0]["WeatherIcon"];
    ?>


        <!-- Include Page header -->
        <?php include_once "../templates/header.php"; ?>

        <main id="main" class="container-fluid">
            <!-- Page Content -->
            <div class="row" style="margin-top: 100px">
                <div class="col-md-10 col-md-offset-1">
                    <aside id="sidebar" class="col-md-3 col-sm-3 visible-lg visible-md visible-sm">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <b><i><h3>Weather</h3></i></b>
                    </div>
                    <div style="width:100%;background-color:lightblue;height:90%;margin-right:500px;border:1px solid green;">
                        <!--
                        <img src="http://apidev.accuweather.com/developers/Media/Default/WeatherIcons/0<?=$weatherIcon?>-s.png" width="100%" height="30%"/>
                    -->

                           <h4 style="text-align:center; margin: 10px 0"> <?=$metric["Value"]?> &#8451</h4>
                          <!--  <h1> <b> <?=$metric["Unit"]?></b></h1>   -->
                             <h4 style="text-align:center">  The weather is <?=$weatherText?> </h4></br>



                    </div>
                    <div class="panel-footer">

                <!-- div -->
              <!-- <a href="https://www.accuweather.com/en/us/toronto-on/10007/current-weather/349727" class="aw-widget-legal">
                </a><div id="awtd1491400412000" class="aw-widget-36hour"  data-locationkey=""
                         data-unit="f" data-language="en-us" data-useip="true" data-uid="awtd1491400412000"
                       -editlocation="true"></div><script type="text/javascript" src="https://oap.accuweather.com/launch.js"></script>   -->
            </aside>
	    <div class="col-md-8 col-sm-8">

	  <h2 style="text-align:center"><?=$park1["name"]?></h2>

	  <img id="parks-image" class="img-responsive" id="pic1" alt="Problem Loading Image" src="<?=$park1["banner"]?>">
	  <p id="parks-desc"> <?=$park1["description"]?>
	  </p>
    <?php include "imagegallery.php"; ?>
    <?php include "../templates/attitude.php"; ?>

                        <!-- Allow visitors to load footprints from users for the current park -->
                        <div class="row park-footprints">
                            <h2>Park's Footprints</h2>
                            <div class="park-footprints__container"></div>
                            <div class="park-footprints__load-btn">
                                <button type="button" class="btn btn-primary btn-lg btn-block btn-load-footprints"
                                        title="Click to load footprints members posted about this park">View Footprints</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Footer -->
            <?php include_once "../templates/footer.php";?>
        </main>

        <!-- JavaScript File -->
        <script type="application/javascript" src="../static/js/parkDescription.js"></script>
    </body>
</html>
