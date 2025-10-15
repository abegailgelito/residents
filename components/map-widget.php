<?php
function renderMapWidget() {
    ?>
    <div class="dashboard-card map-card">
        <h5 class="card-title">Flood Risk Map</h5>
        <div class="map-container" id="mini-map"></div>
    </div>
<!-- <style>
    .dashboard-card3 {
    border: 1px solid #ddd;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-left: 25%;
    }

 .map-card {
        padding: 10px !important;
        height: 526px !important;
        width: 290px  !important;
    }

.map-container {
        width: 100%;
        height: 100%;
        border-radius: 5px;
        overflow: hidden;
    }


    
</style> -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var donaImeldaCoords = [14.6151, 121.0177];
        var donaImeldaBoundary = [
            [14.618895, 121.014998],
            [14.619204, 121.016134],
            [14.618549, 121.016925],
            [14.618784, 121.019209],
            [14.615562, 121.020689],
            [14.611376, 121.019757],
            [14.607833, 121.022271],
            [14.604648, 121.019936],
            [14.607290, 121.017461],
            [14.615302, 121.016682],
            [14.618833, 121.015037]
        ];

        var miniMap = L.map('mini-map', {
            center: [14.6151, 121.0177],
            dragging: false,
            zoom: 20,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            boxZoom: false,
            keyboard: false,
            zoomControl: false,
            attributionControl: false
        }).setView(donaImeldaCoords, 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(miniMap);

        var polygon = L.polygon(donaImeldaBoundary, {
            color: '#28a745',
            weight: 1,
            fillColor: '#28a745',
            fillOpacity: 0.2
        }).addTo(miniMap)
          .bindPopup('Doña Imelda, Quezon City');

        miniMap.fitBounds(polygon.getBounds());
    });
    </script>

    
    <?php
}
?>