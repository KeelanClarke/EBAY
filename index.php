<?php
error_reporting(E_ALL);  // Turn on all errors, warnings, and notices for easier debugging

// API request variables
$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
$s_endpoint = 'http://open.api.ebay.com/shopping';  // Shopping
$f_endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // Finding
$search_num = 3;
$query = "";
// Create a PHP array of the item filters you want to use in your request
$filterarray =
  array(
    array(
    'name' => 'MaxPrice',
    'value' => '25',
    'paramName' => 'Currency',
    'paramValue' => 'USD'),
    array(
    'name' => 'FreeShippingOnly',
    'value' => 'true',
    'paramName' => '',
    'paramValue' => ''),
    array(
    'name' => 'ListingType',
    'value' => array('AuctionWithBIN','FixedPrice','StoreInventory'),
    'paramName' => '',
    'paramValue' => ''),
  );
  
  
if(isset($_POST["save_"])){
	$query = $_POST["querytext"];
	$search_num = $_POST["num_search"];
	$itemSort  = urlencode (utf8_encode($_POST['ItemSort']));
	$itemType  = urlencode (utf8_encode($_POST['ItemType']));
	buildXMLFilter($filterarray);
	$sitearray = array(
	    'EBAY-US' => '0',
	    'EBAY-ENCA' => '2',
	    'EBAY-GB' => '3',
	    'EBAY-AU' => '15',
	    'EBAY-DE' => '77',);
	// Construct the findItemsByKeywords POST call
	// Load the call and capture the response returned by the eBay API
	// the constructCallAndGetResponse function is defined below
	$resp = simplexml_load_string(constructPostCallAndGetResponse($endpoint, $query, $xmlfilter, $search_num));
	$return = Array();
	
	
	// Check to see if the call was successful, else print an error
	if ($resp->ack == "Success") {
	  $results = '';  // Initialize the $results variable
	
	  // Parse the desired information from the response
	  foreach($resp->searchResult->item as $item) {
	    $pic   = $item->galleryURL;
	    $link  = $item->viewItemURL;
	    $itemID = $item->ItemID;
		$CP = $item->ConvertedCurrentPrice;
		$BidCount = $item->BidCount;
		$title = $item->Title;
	
	    // Build the desired HTML code for each searchResult.item node and append it to $results
	    $results .= '<tr><td><img src="'.$pic.'"></td><td><a href="'.$link.'">'.$title.'</a></td></tr>';
	   #$return[$itemID] = array("Link" =>$link, "ID" => $itemID, "Price" => $CP, "Title" => $title);
	  }
	}
	else {  // If the response does not indicate 'Success,' print an error
	  $results  = "<h3>Oops! The request was not successful. Make sure you are using a valid ";
	  $results .= "AppID for the Production environment.</h3>";
	}
}


// Generates an XML snippet from the array of item filters
function buildXMLFilter ($filterarray) {
  global $xmlfilter;
  // Iterate through each filter in the array
  foreach ($filterarray as $itemfilter) {
    $xmlfilter .= "<itemFilter>\n";
    // Iterate through each key in the filter
    foreach($itemfilter as $key => $value) {
      if(is_array($value)) {
        // If value is an array, iterate through each array value
        foreach($value as $arrayval) {
          $xmlfilter .= " <$key>$arrayval</$key>\n";
        }
      }
      else {
        if($value != "") {
          $xmlfilter .= " <$key>$value</$key>\n";
        }
      }
    }
    $xmlfilter .= "</itemFilter>\n";
  }
  return "$xmlfilter";
} // End of buildXMLFilter function

// Build the item filter XML code

?>

<!-- Build the HTML page with values from the call response -->
<html>
<head>
<title>eBay Search Results for <?php echo $query; ?></title>
<link type="text/css" href="style.css" rel="stylesheet" /> 
</head>
<body>
<div class = "container">
<h1>eBay Search Results for <?php echo $query; ?></h1>
<div class = "input-wrapper">
	<form id="add_contact" action="" method="post">
		<div id = "primary">
			<label id = "lbl_search_ebay" class = "primary_labels">Search Query:</label>
			<textarea name= "querytext" id="textinput" ></textarea>
			<input type= "hidden" name = "save_" value = "1">
			<input type = 'submit' id = "submit-query" value = '** check ebay **' >
		</div>
		<div id = "secndary">
			<!-- <label id = "lbl_search_num" class = "secondary_labels">Return #:</label> -->
			<!-- <textarea name= "num_search" id="input_search_num" class = "secondary_input">5</textarea> -->
		
		</div>
		<table cellpadding="2" border="0">
  <tr>
    <th>Return Items</th>
    <th>Site</th>
    <th>ItemType</th>
    <th>ItemSort</th>
    <th>Debug</th>
  </tr>
  <tr>
    <td><input type="text" name="num_search" value=""></td>
    <td>
      <select name="GlobalID">
        <option value="EBAY-AU">Australia - EBAY-AU (15) - AUD</option>
        <option value="EBAY-ENCA">Canada (English) - EBAY-ENCA (2) - CAD</option>
        <option value="EBAY-DE">Germany - EBAY-DE (77) - EUR</option>
        <option value="EBAY-GB">United Kingdom - EBAY-GB (3) - GBP</option>
        <option selected value="EBAY-US">United States - EBAY-US (0) - USD</option>
      </select>
    </td>
    <td>
      <select name="ItemType">
        <option selected value="All">All Item Types</option>
        <option value="Auction">Auction Items Only</option>
        <option value="FixedPriced">Fixed Priced Item Only</option>
        <option value="StoreInventory">Store Inventory Only</option>
      </select>
    </td>
    <td>
      <select name="ItemSort">
        <option value="BidCountFewest">Bid Count (fewest bids first) [Applies to Auction Items Only]</option>
        <option selected value="EndTimeSoonest">End Time (soonest first)</option>
        <option value="PricePlusShippingLowest">Price + Shipping (lowest first)</option>
        <option value="CurrentPriceHighest">Current Price Highest</option>
      </select>
    </td>
    <td>
    <select name="Debug">
      <option value="1">true</option>
      <option selected value="0">false</option>
      </select>
    </td>

  </tr>
  </table>
	</form>
</div>
<table>
<tr>
  <td>
    <?php echo $results;?>
  </td>
</tr>
</table>
</div>
</body>
</html>

<?php
function constructPostCallAndGetResponse($endpoint, $query, $xmlfilter, $search_num) {
  global $xmlrequest;

  // Create the XML request to be POSTed
  $xmlrequest  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
  $xmlrequest .= "<findItemsByKeywordsRequest xmlns=\"http://www.ebay.com/marketplace/search/v1/services\">\n";
  $xmlrequest .= "<keywords>";
  $xmlrequest .= $query;
  $xmlrequest .= "</keywords>\n";
  $xmlrequest .= $xmlfilter;
  $xmlrequest .= "<paginationInput>\n <entriesPerPage>";
  $xmlrequest .= $search_num;
  $xmlrequest .= "</entriesPerPage>\n</paginationInput>\n";
  $xmlrequest .= "</findItemsByKeywordsRequest>";

  // Set up the HTTP headers
  $headers = array(
    'X-EBAY-SOA-OPERATION-NAME: findItemsByKeywords',
    'X-EBAY-SOA-SERVICE-VERSION: 1.3.0',
    'X-EBAY-SOA-REQUEST-DATA-FORMAT: XML',
    'X-EBAY-SOA-GLOBAL-ID: EBAY-US',
    'X-EBAY-SOA-SECURITY-APPNAME: KeelanCl-e594-4520-a1ce-ed56dd565feb',
    'Content-Type: text/xml;charset=utf-8',
  );

  $session  = curl_init($endpoint);                       // create a curl session
  curl_setopt($session, CURLOPT_POST, true);              // POST request type
  curl_setopt($session, CURLOPT_HTTPHEADER, $headers);    // set headers using $headers array
  curl_setopt($session, CURLOPT_POSTFIELDS, $xmlrequest); // set the body of the POST
  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);    // return values as a string, not to std out

  $responsexml = curl_exec($session);                     // send the request
  curl_close($session);                                   // close the session
  return $responsexml;                                    // returns a string

}  // End of constructPostCallAndGetResponse function
?>
