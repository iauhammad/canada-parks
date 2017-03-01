<?php
//Author: Sam
require '../lib/DatabaseAccess.php';
require '../lib/park.php';
require '../lib/ParkRepository.php';

$p = new Park();

$provinces = array(
    'Alberta' => 'AB',
    'British Columbia' => 'BC',
    'Manitoba' => 'MB',
    'New Brunswick' => 'NB',
    'Newfoundland and Labrador' => 'NL',
    'Northwest Territories' => 'NT',
    'Nova Scotia' => 'NS',
    'Nunavut' => 'NU',
    'Ontario' => 'ON',
    'Prince Edward Island' => 'PE',
    'Quebec' => 'QC',
    'Saskatchewan' => 'SK',
    'Yukon' => 'YT'
);
$db = DatabaseAccess::getConnection();
$province = isset($_GET['province']) ? $_GET['province'] : '';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$parkRepository = new ParkRepository($db);
$parks = $parkRepository->getParks($name, $province);


?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
			$team_cssglobal_custom = "/static/css/globe.css";
			$team_icon_custom = "/static/img/logo.png";
			$team_bootstrap_custom = "/static/vendor/bootstrap/css/bootstrap.min.css";
			$team_bootjs_custom= "/static/vendor/bootstrap/js/bootstrap.min.js";
			$team_jquery_custom = "/static/vendor/jquery-3.1.1.min.js";
			include "../templates/meta.php";
		?>
		<meta name="author" content="Sam">
        <title>Park List</title>
        <link rel="stylesheet" href="../static/css/parks.css">
    </head>
    <body>
        <div class="container-fluid">
            <?php
				$team_logo_custom = "/static/img/logo.png";
				$team_personal_custom = "/static/img/users/profile/0.png";
				include "../templates/header.php";
			?>
			<main id="main" class="row col-md-10 col-md-offset-1">
                <h1 id="parks-headline" class="text-center">Park List</h1>
                <form id="search" class="form-inline" method="GET">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?=$name?>" placeholder="Park Name">
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <select class="form-control" id="province" name="province">
                            <option value="">Select a Province</option>
                            <?php foreach($provinces as $name => $value) {?>
                            <option <?=($province == $value) ? "selected" : ""?> value="<?=$value?>"><?=$name?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-success" value="Search"/>
                    </div>
                </form>
                <?php if (count($parks) != 0) {?>
                <!-- Don't know where show be this be
                <div id="compare-wrapper" >
                    <a disabled="disabled" id="compare" class="btn btn-primary">Compare Parks</a>
                </div>
                -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#park-list" aria-controls="park-list" role="tab" data-toggle="tab">List</a></li>
                    <li role="presentation"><a href="#map" id="toMap" aria-controls="map" role="tab" data-toggle="tab">Map</a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane" id="map"></div>
                    <div role="tabpanel" class="tab-pane active row parks" id="park-list">
                        <div class="col-xs-12 col-sm-4 col-md-3 park-sizer"></div>
                        <?php foreach($parks as $park) {?>
                        <div class="col-xs-12 col-sm-4 col-md-3 park" id="park-<?=$park['id']?>">
                            <?php if (!empty($park["banner"])) { ?>
                            <img class="img-responsive" src="<?=$park["banner"]?>" />
                            <?php } ?>
                            <div class="caption">
                                <h2 class="name"><?=$park['name']?></h2>
                                <p><?=$park['address']?></p>
                                <p><a href="/park?id=<?=$park['id']?>" class="btn btn-primary" role="button">Detail</a> <a  data-id="<?=$park['id']?>" href="#" class="btn btn-default select" role="button">Compare</a></p>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } else { ?>
                <h2 class="text-center">No Park</h2>
                <?php } ?>
            </main>
            <?php
    			include "../templates/footer.php";
    		?>
        </div>

		<script type="text/javascript">
		    var parks = <?=json_encode($parks)?>;
		</script>

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD1aO6SHBdMTgsBbV_sn5WI8WVGl4DCu-k&libraries=places"></script>
        <script type="text/javascript" src="https://npmcdn.com/isotope-layout@3.0.2/dist/isotope.pkgd.min.js"></script>
        <script type="text/javascript" src="../static/js/map.js"></script>
        <script type="text/javascript" src="/static/js/parks.js"></script>
    </body>
</html>
