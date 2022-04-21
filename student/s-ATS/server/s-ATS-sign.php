<?php
		header('Access-Control-Allow-Origin: *');

		$data_directory = "../../database/";
		
		function loginTeacher($teacher_id) {
			$teacher_hash = hash('md5', "Technoboard".$teacher_id."194021119402241940261");
			$teacher_directory_name = "t-".$teacher_hash;

			return $teacher_directory_name."/";
		}

		function loginClass($teacher_key, $class_id) {
			$class_directory_name = "c-".$class_id;

			return $teacher_key.$class_directory_name."/";
		}

		function findPortal($class_key) {
			global $data_directory;

			$portal_path = $data_directory.$class_key."portal.json";
			$portal_json = file_get_contents($portal_path);
			$portal = json_decode($portal_json, true);

			return $portal;
		}

		function loginSession($class_key, $portal) {
			$session_id = $portal['sessionID'];
			$session_directory_name = "s-".$session_id;

			return $class_key.$session_directory_name."/";
		}

		function loginRequest($session_key, $portal) {
			$request_id = $portal['requestID'];
			$request_file_name = "r-".$request_id.".json";
			
			return $session_key.$request_file_name;
		}

		function signRequest($request_key, $student_id) {
			global $data_directory;

			$path = $data_directory.$request_key;
			$existing_json = file_get_contents($path);
			$existing_array = json_decode($existing_json, true);

			$existing_length = count($existing_array);
			$id = $existing_length;
			
			if(newSignature($student_id, $existing_array)) {
				
				$updated_array = $existing_array;
				$updated_array[$id] = Array (
					"ID" => strval($id + 1),
					"StudentID" => strval($student_id)
				);
				$updated_json = json_encode($updated_array);
				
				if (file_put_contents($path, $updated_json))
					echo "Success";
				else
					echo "Failed to write to file";
			}
			else {
				echo "Already signed";
			}
		}

		function newSignature($student_id, $existing_array) {
			for($i = 0; $i < count($existing_array); $i++) {
				if ($existing_array[$i]["StudentID"] == $student_id)
					return false;
			}
			return true;
		}

		if(!empty($_GET['id']) and !empty($_GET['t']) and !empty($_GET['c'])) {
			$student_id = $_GET['id'];
			$teacher_id = $_GET['t'];
			$class_id = $_GET['c'];
			
			$class = loginClass(
				loginTeacher($teacher_id),
				$class_id
			);

			$portal = findPortal($class);

			$request = loginRequest(
				loginSession(
					$class,
					$portal
				),
				$portal
			);
			signRequest($request, $student_id);
		}
?>