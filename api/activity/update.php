<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: PUT");
	header("Content-Type: application/json");

	if($_SERVER["REQUEST_METHOD"] == "PUT") {
		$input = json_decode(file_get_contents("php://input"), true);

		$utils = require_once("../utils.php");
		$helper = new Utils();

		$token = !empty($input["token"]) ? $input["token"] : die();
		if($helper->verifySession($token)) {
			$txID = !empty($input["txID"]) ? $input["txID"] : die();
			$id = !empty($input["id"]) ? $input["id"] : die();
			$symbol = !empty($input["symbol"]) ? $input["symbol"] : die();
			$date = !empty($input["date"]) ? $input["date"] : die();
			$type = !empty($input["type"]) ? $input["type"] : die();
			$amount = !empty($input["amount"]) ? $input["amount"] : die();
			$fee = !empty($input["fee"]) ? $input["fee"] : die();
			$notes = !empty($input["notes"]) ? $input["notes"] : die();

			if($helper->validDate($date)) {
				$activity = array("id" => $id, "symbol" => $symbol, "date" => $date, "time" => strtotime($date), "type" => $type, "amount" => $amount, "fee" => $fee, "notes" => $notes);
			
				if($type == "buy" || $type == "sell" || $type == "transfer") {
					if($type == "buy" || $type == "sell") {
						$exchange = !empty($input["exchange"]) ? $input["exchange"] : die();
						$pair = !empty($input["pair"]) ? $input["pair"] : die();
						$price = !empty($input["price"]) ? $input["price"] : die();
					
						$activity["exchange"] = $exchange;
						$activity["pair"] = $pair;
						$activity["price"] = $price;
					} else if($type == "transfer") {
						$from = !empty($input["from"]) ? $input["from"] : die();
						$to = !empty($input["to"]) ? $input["to"] : die();

						$activity["from"] = $from;
						$activity["to"] = $to;
					}
				} else {
					echo json_encode(array("error" => "Invalid activity type."));
				}

				$current = json_decode(file_get_contents($helper->activityFile), true);
		
				if(array_key_exists($txID, $current)) {
					$current[$txID] = $activity;

					$update = file_put_contents($helper->activityFile, json_encode($current));

					if($update) {
						echo json_encode(array("message" => "The activity has been updated."));
					} else {
						echo json_encode(array("error" => "Activity couldn't be updated."));
					}
				} else {
					echo json_encode(array("error" => "Activity not found."));
				}
			} else {
				echo json_encode(array("error" => "Invalid date."));
			}
		} else {
			echo json_encode(array("error" => "You need to be logged in to do that."));
		}
	} else {
		echo json_encode(array("error" => "Wrong request method. Please use PUT."));
	}
?>