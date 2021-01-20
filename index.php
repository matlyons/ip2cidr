<?php
/***
* Author: Mathew Lyons
* Date: January 2021

* Simple tool to convert IP ranges to CIDR Notation
* IP ranges can be provided as full range

* ie: 192.168.0.0-192.168.0.255

* or as full start IP and last octet of end IP
* ie: 192.168.0.0-255

* Results viewed inline on webpage or can be downloade as TXT

* Limited to no input validation. Will just output 0.0.0.0/24 if input is invalid.

***
*/

    // Function to convert IPs
    // Returns array of CIDR notation IPs
    function iprange2cidr($ipStart, $ipEnd){
        if (is_string($ipStart) || is_string($ipEnd)) {
            $start = ip2long($ipStart);
            $end = ip2long($ipEnd);
        }
        else {
            $start = $ipStart;
            $end = $ipEnd;
        }

        $result = array();

        while($end >= $start) {
            $maxSize = 32;
            while ($maxSize > 0){
                $mask = hexdec(iMask($maxSize - 1));
                $maskBase = $start & $mask;
                if ($maskBase != $start) {
                    break;
                }
                $maxSize--;
            }
            $x = log($end - $start + 1)/log(2);
            $maxDiff = floor(32 - floor($x));

            if($maxSize < $maxDiff){
                $maxSize = $maxDiff;
            }

            $ip = long2ip($start);
            array_push($result, "$ip/$maxSize");
            $start += pow(2, (32-$maxSize));
        }
        return $result;
    }

    function iMask($s) {
        return base_convert((pow(2, 32) - pow(2, (32-$s))), 10, 16);
    }

//Process POST data
if (isset($_POST["ips"])) {
    $result = ""; //Result string to return

    // If user has selected to view results on page
    if (htmlentities($_POST["submit"], ENT_QUOTES, 'UTF-8')=="View") {
        $result .= "<hr/><h2>Results</h2>";

        // Split each IP range by new line
        $ips = explode("\n", htmlentities($_POST["ips"], ENT_QUOTES, 'UTF-8'));
        foreach ($ips as $iprange) {

            // Split start and end IP
            $startEnd = explode("-", $iprange);

            // Remove white space
            $start = trim($startEnd[0]);
            $end = trim($startEnd[1]);

            // If only last octet is provided, make this a full IP using first 3 octets of start IP
            if (strlen($end)<4) {
                $splitStartIP = explode(".", $start);
                $routingPortion = implode(".", array_slice($splitStartIP, 0, 3));
                $end = $routingPortion.".".$end;
            }

            // Build result string
            $result .="<strong>".$start." - ".$end."</strong><br/>";
            $result .= implode("<br/>",iprange2cidr($start, $end));
            $result .= "<br/>----------------<br/>";
        }
    }

    // Else the user wants to download a TXT of results
    else {
        // Split each IP range by new line
        $ips = explode("\n", htmlentities($_POST["ips"], ENT_QUOTES, 'UTF-8'));
        foreach ($ips as $iprange) {

            // Split start and end IP
            $startEnd = explode("-", $iprange);

            // Remove white space
            $start = trim($startEnd[0]);
            $end = trim($startEnd[1]);

            // If only last octet is provided, make this a full IP using first 3 octets of start IP
            if (strlen($end)<4) {
                $splitStartIP = explode(".", $start);
                $routingPortion = implode(".", array_slice($splitStartIP, 0, 3));
                $end = $routingPortion.".".$end;
            }

            // Build result string for TXT file
            $result .= implode("\n", iprange2cidr($start, $end))."\r\n";
        }

        // Generate file
        $filename = "ipranges".rand().".txt";
        $file = fopen($filename,"w");
        fwrite($file, $result);
        fclose($file);

        // Set headers
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename='.basename($filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        header("Content-Type: text/plain");

        // Stream file
        readfile($filename);

        // Delete generated file
        unlink($filename);

        // Halt so HTML doesn't end up in file
        exit();
    }
}

?>
<html>
<body>
<h2>Convert IP ranges to CIDR</h2>
<p>Each IP range on a new line.</p><p>Start and end can be full IPs or the end IP can be just the last octet.</p>For example:<ul><li>192.168.0.0-192.168.0.255</li><li>192.168.0.0-255</li></ul>
<form method="post" action="index.php" id="ip">
<textarea name="ips" rows=4 cols=40 form="ip"></textarea><br/>
<input type="submit" name="submit" value="View">
<input type="submit" name="submit" value="Download TXT">
</form>
<?php
    // If there is a result, display it - only displays if user chose to view results, not download.
    if (isset($result)) {
        echo $result;
    }
?>
</body>
</html>
