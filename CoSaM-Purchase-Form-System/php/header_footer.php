﻿<?php
####################
# Name: PHP header_footer.php
# Description: Includes php and html used on almost every page
# Initial Creation Date: 10/10/2018
# Last Modification Date: 01/28/2019
# Author: Wyly Andrews
####################

	require "../php/timeout.php";
	include_once("../php/CAS_authentication.php");
	
	#start session so we can access session variables
	session_start();
	if ( !isset( $_SESSION[ 'emplID' ] ) ) 
	{ 
		header("Location: ../html/login.html");
	}

	$emplType = $_SESSION[ 'emplType' ];

?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>Purchase Form System</title>
	<link rel="stylesheet" type="text/css" href="../css/home.css">
	<link rel="icon" href="../images/favicon.ico" type="image/vnd.microsoft.icon">
</head>
<body>
	<div class="header">
		<a href="https://www.ndsu.edu"><img src="../images/NDSU.logo.typebox.gif" alt="NDSU Logo"></a>
		<label id="pageName">Purchase Order System</label>
		<label id="name"><?php echo $_SESSION[ 'emplFirstName' ]." ".$_SESSION[ 'emplLastName' ]; ?></label>
	</div>
	<nav id=homeOptions>
		<ul>
			<li><a href="../php/home.php">Home</a></li>
			<li><a href="../php/form.php">Make a new order</a></li>
			<li><a href="../php/AUTOFILLED_FORM.php">Make a new order (AUTOFILLED DEMO)</a></li>
			<li><a href="../php/view_employee_orders.php">Your orders</a></li>
			
			<?php if ( $emplType >= 1)
			{
				echo "<li><a href='../php/view_employee_assistants.php'>Your assistants</a></li>";
			}
			?>

			<?php if ( $emplType == 2 ) 
			{ 
				echo "<li><a href='../php/view_all_orders.php'>All orders</a></li>"; 
				echo "<li><a href='../php/view_all_employees.php'>All employees</a></li>";
			} 
			?>

			<li><a href="../php/profile.php">Profile</a></li>

			<li><a href="../php/logout_action.php">Logout</a></li>
		</ul>
	</nav>
</body>
</html>

<?php

	include("../html/footer.html");

?>