<?php
/*
 **********************************************************************************************************************
*
* Name: checkdate.class.php
* This function is checking the date - format
* Writer: Bach Nguyen (bnguye21@uncc.edu
* Last Updated: 11/18/2013
*
 ***********************************************************************************************************************
 */
class CheckDate {
	
	public function myCheckDate($date) {
		$result = array();
		$result["status"] = "";
		$result["msg"] = "";
		if($date=='')
		{
			$result["status"] = "error";
			$result["msg"] = "Please select 'Date' before clicking Submit";
			return $result;
		}
		else
		{
			if((strlen($date) < 10) OR (strlen($date) > 10)) {
				$result["status"] = "error";
				$result["msg"] = "Error 1. Please enter the date in 'mm/dd/yyyy' format.";
				return $result;
			} elseif ((substr_count($date,"/")) <> 2) {
				$result["status"] = "error";
				$result["msg"] = "Error. Please enter the date in 'mm/dd/yyyy' format";
				return $result;
			} else {
				$pos = strpos($date,"/");
				$pos2 = strrpos($date,"/");
				if ($pos <> 2 or $pos2 <> 5) {
					$result["status"] = "error";
					$result["msg"] = "Error 2. Please enter the date in 'mm/dd/yyyy' format";
					return $result;
				}
			
				$monthOnly = substr($date,0,($pos));
				$checkDigit = ctype_digit($monthOnly);
				if (!($checkDigit)) {
					$result["status"] = "error";
					$result["msg"] = "Error 3. Please enter a Valid Month. Month accepts digits only";
					return $result;
				} else {
					if (((int)$monthOnly <= 0) OR ((int)$monthOnly > 12)) {
						$result["status"] = "error";
						$result["msg"] = "Error 4. Please enter a Valid Month";
						return $result;
					}
				} 
				
				$dateOnly = substr($date,($pos+1),($pos));
				$checkDigit = ctype_digit($dateOnly);
				if (!($checkDigit)) {
					$result["status"] = "error";
					$result["msg"] = "Error 5. Please enter a Valid Date. Date accepts digits only";
					return $result;
				} else {
					if (((int)$dateOnly <= 0) OR ((int)$dateOnly > 31)) {
						$result["status"] = "error";
						$result["msg"] = "Error 6. Please enter a Valid Date";
						return $result;
					} 
				}
			
				$yearOnly = substr($date,($pos+4),strlen($date));
				$checkDigit = ctype_digit($yearOnly);
				if (!($checkDigit)) {
					$result["status"] = "error";
					$result["msg"] = "Error 7. Please enter a Valid Year. Year accepts digits only";
					return $result;
				} 
			
				//Best of both worlds
				// - flexibility of DateTime (next thursday, 2 weeks ago, etc)
				// - strictness of checkdate (2000-02-31 is not allowed)
				//echo "date: ".$date."<br>";
				$m=false;
				$d=false;
				$y=false;
				$warning_count = 0;
				$error_count = 0;
				$parts=date_parse($date);
				$warning_count = $parts['warning_count'];
				$error_count = $parts['error_count'];
				//echo "<pre>";
				//echo print_r($parts);
				//echo "</pre>";
				//echo "warning count: ".$warning_count."<br>";
				//echo "error count: ".$error_count."<br>";
				if($parts!==false)
				{
					$m=$parts['month'];
					$d=$parts['day'];
					$y=$parts['year'];
				}

				if ($error_count > 0) {
					$result["status"] = "error";
					$msg = implode(",", array_values($parts["errors"]));
					//echo "msg: ".$msg."<br>";
					$result["msg"] = $msg;		
				} elseif ($warning_count > 0) {
					$result["status"] = "error";
					$msg = implode(",", array_values($parts["warnings"]));
					//echo "msg: ".$msg."<br>";
					$result["msg"] = $msg;
				} elseif($m==false || $d==false || $y==false) {
					$result["status"] = "error";
					$result["msg"] = "Error 8. Please enter the date in 'dd/mm/yyyy' format";
					//if(checkdate($m,$d,$y)) $result=sprintf('%04d-%02d-%02d',$y,$m,$d);
				}
				//else {
					//Try for something more generic - this allows entries like 'next thursday'      
					//   $dt=false;
					//    try{ $dt=new \DateTime($date); }catch(\Exception $e){ $dt=false; }
					//    if($dt!==false) $result=$dt->format('Y-m-d');
					//}
				return $result;
			} 
		}
    //return $result;
	}
}
?>