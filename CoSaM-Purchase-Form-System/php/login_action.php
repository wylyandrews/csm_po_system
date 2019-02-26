<?php
####################
# Name: PHP login_action.php
# Description: Handles Login attempts
# Initial Creation Date: 10/2018
# Last Modification Date: 02/21/2019
# Author: Wyly Andrews
####################

require "../php/CAS_authentication.php";

$attr = phpCAS::getAttributes();
$ePUID = $attr['eduPersonUniqueId'];

# Open database connection
require ('../php/database_connect.php');

// Form search query to get employee information located across multiple tables
$searchQuery =  "SELECT T1.ID, T1.emplFirstName, T1.emplLastName, T1.department, T1.emplEmail, T1.emplType, employees.emplFirstName, employees.emplLastName, T1.ePUID ";
$searchQuery .= "FROM employees RIGHT JOIN ";
$searchQuery .= "( SELECT ID, emplFirstName, emplLastName, department, emplEmail, emplType, advisorID, ePUID ";
$searchQuery .= " FROM employees LEFT JOIN advisorAssistant ON advisorAssistant.assistantID = employees.ID ) ";
$searchQuery .= "AS T1 ON T1.advisorID = employees.ID ";

$searchQuery .= "WHERE T1.ePUID = ? "; 

$preparedStatement = mysqli_prepare($dbc, $searchQuery);

echo "<script>console.log('search query: $searchQuery')</script>";
echo "<script>console.log('ePUID: $ePUID')</script>";

mysqli_stmt_bind_param($preparedStatement, 's', $ePUID);

$isSuccess = mysqli_stmt_execute($preparedStatement);

if ($isSuccess) 
{
	echo "search query submitted successfully.";

	$result = mysqli_stmt_get_result($preparedStatement);
	$row = mysqli_fetch_array($result, MYSQLI_NUM);

	# Start session
	session_start();
	$_SESSION[ 'emplID' ] = $row[0];
	$_SESSION[ 'emplFirstName' ] = $row[1];
	$_SESSION[ 'emplLastName' ] = $row[2];
	$_SESSION[ 'emplDepartment' ] = $row[3];
	$_SESSION[ 'emplEmail' ] = $row[4];
	$_SESSION[ 'emplType' ] = $row[5];

	$emplAdvisor = $row[6]." ".$row[7];
	if ($emplAdvisor == " ") { $emplAdvisor = "none"; }
	$_SESSION[ 'emplAdvisor' ] = $emplAdvisor;

	echo "<script type='text/javascript'>";
	echo "console.log('row[0]: $row[0]');";
	echo "</script>";
	
	header( 'Location: ../php/home.php' );/*
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM))
    {
        foreach ($row as $r)
        {
            print "$r ";
        }
        print "\n";
    }*/
	echo "<script>console.log('made it to the end')</script>";
}
else
{

	# send to login_newEmplCheck to check for registration
	echo "<script type='text/javascript'>";
	echo "alert('Unable to find your log-in! Checking new users...');";
	echo "</script>";

	header("Location: ../php/login_newEmplCheck.php");
} 


?>