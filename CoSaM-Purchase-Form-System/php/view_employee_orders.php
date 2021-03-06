<?php
####################
# Name: PHP view_employee_orders.php
# Description: Views all orders made by the logged-in user
# Initial Creation Date: 10/14/2018
# Last Modification Date: 02/21/2019
# Author: Wyly Andrews
####################

require "../php/initialization.php";

function makeJSButton($orderIDForView) {
	print "<td>";
	print "<form action='../php/view_individual_order.php' method='POST'>";
	print "<input type='hidden' name='viewOrderButton' value='$orderIDForView'>";
	print "<input type='submit' value='View'/>";
	print "</form>";
	print "</td>";
}

#Search details
$searchRequests = array();
$searchTypes = array();
$searchOperators = array();
$sortTypes = array();
$sortDirections = array();

#Default values for all available orders
array_push($searchRequests, "0");
array_push($searchTypes, "0");
array_push($searchOperators, "=");
array_push($sortTypes, "orderID");
array_push($sortDirections, "");

if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' )
{
	# overwrite default values above
	$searchRequests = $_POST['searchRequest'];
	$searchTypes = $_POST['searchType'];
	$searchOperators = array();
	$sortTypes = $_POST['sortType'];
	$sortDirections = $_POST['sortDirection'];

	foreach ($searchRequests as $key => $searchRequest) {
		// Search

		if($searchTypes[ $key ] == "" ) {
			$searchTypes[ $key ] = "orderID"; #Dummy value
		}

		# clean $searchTypes entries
		switch ($searchTypes[ $key ]) {
			case "orderDepartment":
			case "funding":
			case "vendName":
			case "orderID":
				break;
			default:
				$searchTypes[ $key ] = "orderID";
				break;
		}

		if($searchRequest == "") {
			$searchRequest == 0;
			$searchTypes[ $key ] = 0;
		}

		array_push($searchOperators, "=");

		if ( $searchTypes[ $key ] == "orderDepartment" || $searchTypes[ $key ] == "funding" ) {
			$searchOperators[ $key ] = "LIKE";
			$searchRequests[ $key ] = "%".$searchRequests[ $key ]."%";
		}
	}

	foreach ($sortTypes as $key => $sortType) {
		# clean $sortTypes entries
		switch ($sortTypes[ $key ]) {
			case "orderID":
			case "creationDT":
			case "vendName":
			case "funding":
			case "shippingHandlingCost":
			case "additionalCost":
			case "totalCost":
			case "orderStatus":
			case "orderDepartment":
				break;
			default:
				$sortTypes[ $key ] = "orderID";
				break;
		}

		# clean $sortDirections entries
		switch ($sortDirections[ $key ]) {
			case "desc":
				break;
			default:
				$sortDirections[ $key ] = " ";
		}
	}
}

# select orders
function makeOrdersTable() {
	global $dbc;
	global $emplID;
	global $searchRequests;
	global $searchTypes;
	global $searchOperators;
	global $sortTypes;
	global $sortDirections;
	$searchQuery = " SELECT * FROM orders WHERE 0 = 0 ";
	$bindChars = '';
	foreach($searchRequests as $key => $searchRequest) {
		$bindChars .= 's';
		$searchQuery .= " AND ".$searchTypes[$key]." ".$searchOperators[$key]." "." ? "; 
	}


	if( count($sortTypes) >= 1 ) { $searchQuery .= " ORDER BY "; }

	foreach($sortTypes as $key => $sortType) {
		if ($key != 0) { $searchQuery .= ", "; }
		$searchQuery .= $sortTypes[$key]." ".$sortDirections[$key];
	}

	//$searchQuery = ( " SELECT * FROM orders WHERE employeeID = $emplID AND $searchType $operator ? ORDER BY $sortType $sortDirection");

	$preparedStatement = mysqli_prepare($dbc, $searchQuery); 

	if (count($searchRequests) == 1) {
		mysqli_stmt_bind_param($preparedStatement, $bindChars, $searchRequests[0]);
	} else if (count($searchRequests) == 2) {
		mysqli_stmt_bind_param($preparedStatement, $bindChars, $searchRequests[0], $searchRequests[1]);
	} else /*if (count($searchRequests) == 3)*/ {
		mysqli_stmt_bind_param($preparedStatement, $bindChars, $searchRequests[0], $searchRequests[1], $searchRequests[2]);
	}

	$isSuccess = mysqli_stmt_execute($preparedStatement);

	if ($isSuccess) 
	{
		#echo "search query submitted successfully.";
	}
	else 
	{
		echo "Error occurred. Record not submitted. (error 100)";
		echo $emplID;
		echo $searchType;
		echo $searchRequest;
		echo $searchQuery;
		mysqli_close($dbc);
		exit();
	}

	$result = mysqli_stmt_get_result($preparedStatement);
	if ($row = mysqli_fetch_array($result, MYSQLI_NUM))
	{
		print "<div id=divTable>";
		print "<table border>";
		print "<tr>";

		print"<td>Order ID</td>";
		//print"<td>Employee ID</td>";
		print"<td>Order Submission</td>";
		print"<td>Vendor Name</td>";
		//print"<td>Vendor Email</td>";
		//print"<td>Vendor Phone</td>";
		print"<td>Funding</td>";
		//print"<td>S&H Cost</td>";
		//print"<td>Additional Fees</td>";
		print"<td>Total Cost</td>";
		print"<td>Order Status</td>";
		print"<td>Additional Information</td>";
		print"<td>Department</td>";
		//print"<td>Shipping Notes</td>";
		//print"<td>Billing Notes</td>";

		print "</tr>";
		print '<br/>';

	
		do
		{
		
			$rowID = "row".$i;
			print "<tr id='$rowID'>";

			$undisplayedKeys = array(1, 4, 5, 7, 8, 13, 14);
			foreach($row as $key => $value)
			{
				if(!in_array($key, $undisplayedKeys)) {	
					if($key >= 7 && $key <= 9)
						print "<td><p>$$value</p></td>";
					else
						print "<td><p>$value</p></td>";
				}
			}
			makeJSButton($row[0]);
			print "</tr>";
	
		} while($row = mysqli_fetch_array($result, MYSQLI_NUM));

		print "</table>";
		print "</div>";
		
		
	} else { # No orders founds
		print "<p>No matches found.<p/>";
	}
}

?>

<!DOCTYPE html>

<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>View Employee Orders</title>
	<script src="../javascript/view_orders.js" defer></script>
	<link rel="stylesheet" type="text/css" href="../css/view_employee_orders.css">
	<link rel="stylesheet" type="text/css" href="../css/tables.css">
</head>
<body>
	<div id=searchSection>
		<h1>View Orders</h1>
		<form action="../php/view_employee_orders.php" method="POST" >
			<table>
				<tbody>
					<tr id="searchRow">
						<td><label>Search by:</label></td>
						<td><input type="text" id="searchRequest" name="searchRequest[]" /></td>
						<td><select id="searchType" name="searchType[]" >
							<option value="">Select a value to search</option>
							<option value="orderID">Order ID</option>
							<option value="vendName">Vendor Name</option>
							<option value="funding">Funding</option>
							<option value="orderDepartment">Department</option>
						</select></td>	
						<td id="subtractButtonCell"><button type="button" id="subtractButton" style="visibility:hidden"><img src="../images/subtract.png" style="width:18px;height:18px"></button></td>
						<td id="addButtonCell"><button type="button" id="addButton"><img src="../images/add.png" style="width:18px;height:18px"></button></td>
					</tr>
				</tbody>
			</table>
			</br>
			<table>
				<tbody>
					<tr id="sortRow">
						<td><label>Sort by:</label></td>
						<td><select id="sortType" name="sortType[]" >
							<option value="">Select a value to sort</option>
							<option value="orderID">Order ID</option>
							<option value="creationDT">Order Submission</option>
							<option value="vendName">Vendor Name</option>
							<option value="funding">Funding</option>
							<option value="shippingHandlingCost">Shipping and Handling Cost</option>
							<option value="additionalCost">Additional Cost</option>
							<option value="totalCost">Total Cost</option>
							<option value="orderStatus">Order Status</option>
							<option value="orderDepartment">Department</option>
						</select></td>
						<td><select id="sortDirection" name="sortDirection[]" >
							<option value="">Ascending</option>
							<option value="desc">Descending</option>
						</select></td>
						<td id="sortSubtractButtonCell"><button type="button" id="sortSubtractButton" style="visibility:hidden"><img src="../images/subtract.png" style="width:18px;height:18px"></button></td>
						<td id="sortAddButtonCell"><button type="button" id="sortAddButton"><img src="../images/add.png" style="width:18px;height:18px"></button></td>
					</tr>
				</tbody>
			</table>
			<input type="submit">
		</form>
		<div id=searchResults>
			<?php makeOrdersTable(); ?>
		</div>
	</div>
</body>
