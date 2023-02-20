<!DOCTYPE html>
<html>
  <head>
    <title>WHOIS Tool</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="container">
      <h1>WHOIS Tool</h1>
      <table class="table table-bordered">
        <thead>
        <thead>
  <tr>
    <th>Domains To Scan</th>
    <th>Scan Time</th>
    <th>Time Taken (Hours)</th>
    <th>Time Taken (Mins)</th>
    <th>Time Taken (Sec)</th>
    <th>Status</th>
    <th>Progress</th>
  </tr>
</thead>

        </thead>
        <tbody>

        <?php

// Database connection information
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "domains";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// WHOIS API key
$api_key = "at_fNTIbOOwAQzNiYUOP2eJKmKKvHvpS";

// Domains file path
$domains_file_path = "domains.txt";

// Read domains from file into an array
$domains = file($domains_file_path, FILE_IGNORE_NEW_LINES);
$total_domains = count($domains);
    $domains_scanned = 0;
// Loop through domains
foreach ($domains as $domain) {
  // increment domains scanned
  $domains_scanned++;
  // Get WHOIS information for domain
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://www.whoisxmlapi.com/whoisserver/WhoisService?apiKey=$api_key&domainName=$domain&outputFormat=JSON");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);

  // Parse WHOIS information
  $json = json_decode($output, true);
  $expiry_date = $json["WhoisRecord"]["registryData"]["expiresDate"];
  $status = $json["WhoisRecord"]["registryData"]["status"];

  // Update database with WHOIS information
      $sql = "UPDATE domains SET expiry_date='$expiry_date', status='$status' WHERE domain_name='$domain'";
      if ($conn->query($sql) === TRUE) {
        $status = "Success";
      } else {
        $status = "Error updating domain $domain: " . $conn->error;
      }

  // Calculate time taken
  $time_taken = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
  $hours = floor($time_taken / 3600);
  $mins = floor(($time_taken - ($hours * 3600)) / 60);
  $secs = round($time_taken - ($hours * 3600) - ($mins * 60), 2);
  $time_taken_str = "$hours:$mins:$secs";

  // Calculate progress percentage
  $progress_percentage = round(($domains_scanned / $total_domains) * 100);
// Output row for domain
echo "<tr>";
echo "<td>$domain</td>";
echo "<td>" . date("F j, Y, g:i a") . "</td>";
echo "<td>$hours</td>";
echo "<td>$mins</td>";
echo "<td>$secs</td>";
echo "<td>$status</td>";
echo "<td><div class='progress'><div class='progress-bar' role='progressbar' style='width: $progress_percentage%' aria-valuenow='$progress_percentage' aria-valuemin='0' aria-valuemax='100'>$progress_percentage%</div></div></td>";
echo "</tr>";

}

?>
<p>Total domains scanned: <?php echo $total_domains; ?></p>

</tbody>
</table>
</div>

<script>
$(document).ready(function() {
// Refresh page every minute
setInterval(function() {
  location.reload();
}, 60000);
});
</script>
</body>
</html>