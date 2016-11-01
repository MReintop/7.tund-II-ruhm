<?php

require("../../config.php");

	
	// see fail, peab olema kıigil lehtedel kus 
	// tahan kasutada SESSION muutujat
	session_start();
	
	//***************
	//**** SIGNUP ***
	//***************
	
	function signUp ($email, $password) {
		
		$database = "if16_mreintop";
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
		$stmt = $mysqli->prepare("INSERT INTO user_sample (email, password) VALUES (?, ?)");
	
		echo $mysqli->error;
		
		$stmt->bind_param("ss", $email, $password);
		
		if($stmt->execute()) {
			echo "salvestamine ınnestus";
		} else {
		 	echo "ERROR ".$stmt->error;
		}
		
		$stmt->close();
		$mysqli->close();
		
	}
	
	
	function login ($email, $password) {
		
		$error = "";
		echo $email;
		
		$database = "if16_mreintop";
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
		$stmt = $mysqli->prepare("
		SELECT id, email, password, created 
		FROM user_sample
		WHERE email = ?");
	
		echo $mysqli->error;
		
		//asendan k¸sim‰rgi
		$stmt->bind_param("s", $email);
		
		//m‰‰ran v‰‰rtused muutujatesse
		$stmt->bind_result($id, $emailFromDb, $passwordFromDb, $created);
		$stmt->execute();
		
		//andmed tulid andmebaasist vıi mitte
		// on tıene kui on v‰hemalt ¸ks vaste
		if($stmt->fetch()){
			
			
			$hash = hash("whirlpool", $password);
			if ($hash == $passwordFromDb) {
				
				echo "Kasutaja logis sisse ".$id;
				
				//m‰‰ran sessiooni muutujad, millele saan ligi
				// teistelt lehtedelt
				$_SESSION["userId"] = $id;
				$_SESSION["userEmail"] = $emailFromDb;
				$_SESSION["message"] = "<h1>Tere tulemast!</h1>";
				
				
				header("Location: data.php");
				exit();
				
			}else {
				$error = "vale parool";
			}
			
			
		} else {
			
			// ei leidnud kasutajat selle meiliga
			$error = "ei ole sellist emaili";
		}
		
		return $error;
		
	}


	function cleanInput($input){
		
		$input = trim($input);           
		$input = htmlspecialchars($input);
		$input = stripslashes($input);
		
	    return $input;
	}
	
		
	function savePlant ($plant, $watering) {
		
		
		
		$database = "if16_mreintop";
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
		
		$stmt = $mysqli->prepare(
		"INSERT INTO flowers (plant, wateringInterval) VALUES (?,?)");
		
		echo $mysqli->error;
		
		
		
		//asendan k¸sim‰rgi
		$stmt->bind_param("ss", $plant,$watering);
		
		if ( $stmt->execute() )  {
			
			echo "salvestamine ınnestus";
			
		}  else  {
			
			echo "ERROR".$stmt->error;
		}
		
		$stmt->close();
		$mysqli->close();
	}
	
	function getAllPlants () {
		
		
	
		$database = "if16_mreintop";
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
		
		
		$stmt = $mysqli->prepare("
		
		  SELECT id, plant,wateringInterval FROM flowers
		 
		");
		echo $mysqli->error;
		
		
		$stmt -> bind_result ($id, $plant,$watering) ;
		$stmt ->execute();
		
		//tekitan massiivi
		
		$result=array();
		
		//Tee seda seni, kuni on rida andmeid. ($stmt->fech)
		//Mis vastab select lausele.
		//iga uue rea andme kohta see lause seal sees
		
		while($stmt->fetch()){
			
			//tekitan objekti
			
			$plantClass = new StdClass();
			
		    $plantClass->id=$id;
			$plantClass->taim=$plant;
			$plantClass->intervall=$watering;
			
			
			
			array_push($result, $plantClass);
		}
		$stmt->close();
		$mysqli->close();
		return $result;
		
		
	}
	
	
		
	function saveInterest ($interest) {
		
		$database = "if16_mreintop";
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
		$stmt = $mysqli->prepare("INSERT INTO interests (interest) VALUES (?)");
	
		echo $mysqli->error;
		
		$stmt->bind_param("s", $interest);
		
		if($stmt->execute()) {
			echo "salvestamine ınnestus";
		} else {
		 	echo "ERROR ".$stmt->error;
		}
		
		$stmt->close();
		$mysqli->close();
		
	}
	
	
		function getAllInterests() {
		
		$database = "if16_mreintop";
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
		
		$stmt = $mysqli->prepare("
			SELECT  id, interest
			FROM interests
		");
		 
		echo $mysqli->error;
		
		
		$stmt->bind_result($id, $interest);
		$stmt->execute();
		
		
		//tekitan massiivi
		$result = array();
		
		// tee seda seni, kuni on rida andmeid
		// mis vastab select lausele
		while ($stmt->fetch()) {
			
			//tekitan objekti
			$i = new StdClass();
			
			$i->id = $id;
			$i->interest = $interest;
		
			array_push($result, $i);
		}
		
		$stmt->close();
		$mysqli->close();
		
		return $result;
	}
	
	function saveUserInterest ($interest)  {
		
		echo ("ii".$_SESSION["userId"]."ii".$interest);
		
		$database = "if16_mreintop";
		$mysqli = new mysqli ($GLOBALS["serverHost"],$GLOBALS["serverUsername"],$GLOBALS["serverPassword"], $database);
		
		$stmt = $mysqli->prepare("
		SELECT id FROM user_interests WHERE user_id=? AND userinterest_id=?
		");
		$stmt->bind_param("ii", $_SESSION["userId"], $interest);
		$stmt->bind_result($id);
		
		$stmt->execute();
		
		if($stmt->fetch())  {
			//oli olemas juba selline rida
			echo "juba olemas";
			return; //edasi midagi ei tehta
			
				
		}
		// KUI EI OLNUD, SIIS SISESTAN
		
		
		$stmt = $mysqli->prepare("
		INSERT INTO user_interests(user_id,userinterest_id)VALUES(?,?)
		");
		
		$stmt->bind_param("ii",$_SESSION["userId"],$interest);
		
		if ($stmt->execute()){
			
			echo"salvestamine ınnestus";
			} else {
				echo "ERROR".$stmt->error;
		}
			}
	function getAllUserInterests() {
		
		$database = "if16_mreintop";
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
		
		$stmt = $mysqli->prepare("
			SELECT  interest
			FROM interests	
			JOIN user_interests 
			ON interests.id=user_interests.userinterest_id
			WHERE user_interests.user_id = ?
		");
		 
		echo $mysqli->error;
		$stmt->bind_param("i",$_SESSION["userId"]);
		
		$stmt->bind_result($interest);
		$stmt->execute();
		
		
		//tekitan massiivi
		$result = array();
		
		// tee seda seni, kuni on rida andmeid
		// mis vastab select lausele
		while ($stmt->fetch()) {
			
			//tekitan objekti
			$i = new StdClass();
			
			
			$i->interest = $interest;
		
			array_push($result, $i);
		}
		
		$stmt->close();
		$mysqli->close();
		
		return $result;
	}
?>