<?php
function renderWeatherWidget() {
    $apiKey = "9ddde3c10f92faf33793ea3f68f27b63"; // Replace with your API key
    $city = "Doña Imelda, PH"; // Your city
    $city = urlencode($city); // Encode spaces and special characters
    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";

    // Fetch weather data
    $response = @file_get_contents($apiUrl);

    if ($response === FALSE) {
        echo "<p>Error fetching weather data. Please try again later.</p>";
        return;
    }

    $data = json_decode($response, true);

    if ($data && $data['cod'] == 200) {
        $temperature = $data['main']['temp'];
        $humidity = $data['main']['humidity'];
        $windSpeed = $data['wind']['speed'];
        $weatherDescription = ucfirst($data['weather'][0]['description']);
        $weatherIcon = $data['weather'][0]['icon'];
    } else {
        $temperature = "N/A";
        $humidity = "N/A";
        $windSpeed = "N/A";
        $weatherDescription = "N/A";
        $weatherIcon = "01d"; // Default icon
    }

?>
<div class="weather-widget">
    <h5 class="card-title">Live Weather Data of Doña Imelda</h5>
        <div class="weather-metrics">
            <div class="weather-metric">
                <i class="fas fa-thermometer-half"></i>
                <div class="weather-metric-value"><span id="temp"><?php echo $temperature; ?></span>°C</div>
                <div class="weather-metric-label">Temperature</div>
            </div>
                <div class="weather-metric">
                    <i class="fas fa-tint"></i>
                    <div class="weather-metric-value"><span id="humidity"><?php echo $humidity; ?></span>%</div>
                    <div class="weather-metric-label">Humidity</div>
                </div>
                <div class="weather-metric">
                    <i class="fas fa-wind"></i>
                    <div class="weather-metric-value"><span id="wind"><?php echo $windSpeed; ?></span> km/h</div>
                    <div class="weather-metric-label">Wind Speed</div>
                </div>
        </div>
                <div class="weather-condition text-center mt-3">
                <img src="https://openweathermap.org/img/wn/<?php echo $weatherIcon; ?>@2x.png" alt="Weather Icon">
                <p class="weather-info"><strong>Condition:</strong> <span id="description"><?php echo $weatherDescription; ?></span></p>
                </div>
</div>
    <?php
}
?>