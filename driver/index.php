<?php
include '../verifyLogin.php';

if ($login){
    switch ($acctType){
        case 'passenger':
            header("location: passenger/");
            exit;
            break;
        case 'operator':
            header("location: operator/");
            exit;
            break;
        case 'admin':
            header("location: admin/");
            exit;
            break;
    }
}else{
    header("location: ../index.php");
    exit;
}


?>

<html>
<head>
    <title>Driver - MAP</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="../js/jquery.min.js"></script>
    <script type="text/javascript" src="../js/bootstrap.min.js"></script>
    <style>
        .nav>li>a:hover, .nav>li>a:focus{
            text-decoration: none;
            background-color: #E7CE26;
        }
        a, a:hover{
            color: #8c7a00;
        }
    </style>
</head>
<body style="height: 100%">
<nav class="navbar navbar-default" style="margin-bottom:0; background-color: #eff85b;border-color: #e7ce26; border-radius: 0">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="../index.php">TaxiTap</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#"><b>Driver</b></a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Menu <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#"></a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="../logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<div style="right: 10px; position: absolute; z-index: 999999; margin-top: 10px; visibility: hidden" id="status">
    <button class="btn btn-success btn-sm" style="float: right" disabled>On Progress...</button>
    <br>
    <button class="btn btn-primary btn-sm" style="float: right; margin-top: 5px" onclick="finishTransaction();">Finish</button>
    <br>
    <button class="btn btn-default btn-sm" style="margin-top: 5px" onclick="cancelTransaction()">Cancel Transaction</button>
</div>

<div class="map-canvas" style="height: -moz-calc(100% - 50px); ">
    <div id="map_canvas" style="height: -moz-calc(100%); width: 100%"></div>
</div>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&ext=.js&key=AIzaSyDCJNjN8MqWNDlrVotbXZaJFAuhG3HGp_c"></script>
<script type="text/javascript">

    var map;
    var searchService;
    var marker; // Marker of current location.
    var originCoords; // origin coordinates
    var directionsService = new google.maps.DirectionsService();
    var directionsDisplay = new google.maps.DirectionsRenderer();
    var myLocation;


    function saveCurrentLocation(position) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        originCoords = new google.maps.LatLng(lat, lng);
        //alert ("Got you location");
        setMarker(); // Marker of your current location

        // Whenever the driver's location changed.
        conn.shareLocation(originCoords, "driver")
    }

    // Set the Default Location
    function saveDefaultLocation(position){
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        myLocation = new google.maps.LatLng(lat, lng);
        initialize(myLocation);
    }

    // GET Your location in realtime
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(saveCurrentLocation,function () {
            handleLocationError(true)
        });
    }
    // Browser doesn't support Geolocation
    else {
        handleLocationError(false);
    }

    // This is the error that will be throw when there is an error to the geolocation
    function handleLocationError(browserHasGeolocation) {
        alert(
            browserHasGeolocation ?
                'Error: The Geolocation service failed.' :
                'Error: Your browser doesn\'t support geolocation.'
        );
    }

    // First when the google map load find the current position
    // before setting it to the map
    function justDoit(){
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(saveDefaultLocation,function () {handleLocationError(true)});
        }else {
            // Browser doesn't support Geolocation
            handleLocationError(false);
        }
    }

    function initialize(location) {
        // GET Your location in realtime
        var mapOption = {
            center: location,
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        map = new google.maps.Map(document.getElementById("map_canvas"), mapOption);

        // This is marker of your current location
        // map.setCenter(originCoords);
        // This gives you access to the paths and the landmark
        searchService = new google.maps.places.PlacesService(map);
        directionsDisplay.setMap(map);
        setMarker();

        execute('<?php echo $log_username ?>');
    }

    function setMarker(){
        if(marker != null){
            marker.setMap(null);
        }

        // This is the marker of your location
        marker = new google.maps.Marker({
            position: originCoords,
            map: map,
            disableDoubleClickZoom: false,
            title: "Your Current Location."
        });
    }

    var markerList = {};
    var currentPassenger = "";
    var onProgress = false;

    // Add plot Marker is like the setMarket, but this handles the marker of the other users
    function plotMarker(username, location){

        if (username in markerList){
            markerList[username].setMap(null);
        }

        markerList[username] = new google.maps.Marker({
            position: location,
            map: map,
            disableDoubleClickZoom: false,
            icon: {
                url: "../img/passenger.png"
            }
        });
    }

    function removeMarker(username){

    }

    // This is used to run the initialized function
    google.maps.event.addDomListener(window, "load", justDoit);
</script>

<script type="text/javascript" src="../js/DriverConfig.js"></script>

<script type="text/javascript">
    var conn;

    function execute(name){
        // Connect To the socket
        conn = new Connection(name, location.host+":2000");
    }

    function transactionModal(destination, passengerNumber, username){
        $("#destination").html(destination);
        $("#passengerNumber").html(passengerNumber);
        $("#modalAccept").attr("onclick", "$('#transactionModal').modal('hide'); acceptTransaction('" +username+ "'); updateCurrentPassenger('" +username+ "'); ");
        $("#modalDecline").attr("onclick", "$('#transactionModal').modal('hide'); declineTransaction('" +username+ "'); ");
        $('#transactionModal').modal("show");
    }

    function updateCurrentPassenger(username){
        currentPassenger = username;
        onProgress = true;
    }

    function removeCurrentPassenger(){
        currentPassenger = "";
        onProgress = false;
    }

    function declineTransaction(username){
        conn.transactionReply(username, "decline")
    }

    function acceptTransaction(username){
        conn.transactionReply(username, "accept")
    }

    function cancelTransaction(){
        removeCurrentPassenger();
        $("#status").css("visibility", "hidden");
        conn.transactionReply(currentPassenger, "cancel")
    }

    function finishTransaction(){
        removeCurrentPassenger();
        $("#status").css("visibility", "hidden");
        conn.transactionReply(currentPassenger, "finish")
    }

</script>

<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" id="transactionModal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">TaxiTap Transaction:</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <b>Destination:</b>
                    <span id="destination"></span>
                </div>
                <div class="form-group">
                    <b>Number of Passengers:</b>
                    <span id="passengerNumber"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="modalDecline">Decline</button>
                <button type="button" class="btn btn-primary" id="modalAccept">Accept</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>