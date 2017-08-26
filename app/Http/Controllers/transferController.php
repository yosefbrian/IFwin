<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Response;
use DateTime;
use DatePeriod;
use DateIntercal;

class transferController extends Controller
{
 //    private static $main_url = 'https://sandbox.bca.co.id'; 
	// private static $client_id = 'd84b8c47-4f7d-4de3-b55c-9ee27045020b'; 
	// private static $client_secret = '32deb748-b3d8-4df6-9ea5-67f44029706c'; 
	// private static $api_key = '5f4749f0-8b9f-4148-ad1d-a12b955e7dae'; 
	// private static $api_secret = '24d3e053-27be-4738-9c6f-04e3a8830e4d'; 

	private static $main_url = 'https://api.finhacks.id'; 
	private static $client_id = '00a2cecf-57a9-495d-b337-05379481cea2'; 
	private static $client_secret = '90f866f0-0bb1-419f-bfcc-abd3ce65d0e1'; 
	private static $api_key = '1b6e44be-df70-4013-8a75-3d7abd2a8046'; 
	private static $api_secret = '60766ed9-2480-4f47-ab3f-68a5a719b54d'; 
	private static $access_token = null;
	private static $signature = null;
	private static $timestamp = null;

	
	private function getToken(){
		$path = '/api/oauth/token';
		$headers = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Authorization: Basic '.base64_encode(self::$client_id.':'.self::$client_secret));
		$data = array(
			'grant_type' => 'client_credentials'
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$main_url.$path);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt_array($ch, array(
			CURLOPT_POST => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => http_build_query($data),
		));
		$output = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($output,true);
		self::$access_token = $result['access_token'];
	}
	private function parseSignature($res){
		$explode_response = explode(',', $res);
		$explode_response_1 = explode(':', $explode_response[17]);
		self::$signature = trim($explode_response_1[1]);
	}
	private function parseTimestamp($res){
		$explode_response = explode(',', $res);
		$explode_response_1 = explode('Timestamp: ', $explode_response[3]);
		self::$timestamp = trim($explode_response_1[1]);
	}
	public function getSignature($url,$method,$data){
		$path = '/utilities/signature';
		$timestamp = date(DateTime::ISO8601);
		$timestamp = str_replace('+','.000+', $timestamp);
		$timestamp = substr($timestamp, 0,(strlen($timestamp) - 2));
		$timestamp .= ':00';
	
		$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => self::$main_url."/utilities/signature",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS=> $data,
				  CURLOPT_HTTPHEADER => array(
				    "accesstoken: ".self::$access_token,
				    "apisecret: ".self::$api_secret,
				    "content-type: application/json",
				    "httpmethod: POST",
				    "timestamp: ".$timestamp,
				    "uri: /banking/corporates/transfers"
				  ),
				));
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				   $this->parseSignature($response);
				 	$this->parseTimestamp($response);
				}
	}
	public function index(){
		$this->getToken();
		$path = '/banking/corporates/transfers';
		$method = 'POST';

		// $data = "{\r\n    \"CorporateID\" : \"BCAAPI2016\",\r\n    \"SourceAccountNumber\" : \"0201245680\",\r\n    \"TransactionID\" : \"00000002\",\r\n    \"TransactionDate\" : \"2017-08-26\",\r\n    \"ReferenceID\" : \"12345/PO/2016\",\r\n    \"CurrencyCode\" : \"IDR\",\r\n    \"Amount\" : \"100000.00\",\r\n    \"BeneficiaryAccountNumber\" : \"0201245681\",\r\n    \"Remark1\" : \"Transfer Test\",\r\n    \"Remark2\" : \"Online Transfer\"\r\n}";


	$data = "{\r\n    \"CorporateID\" : \"finhacks01\",\r\n    \"SourceAccountNumber\" : \"8220000011\",\r\n    \"TransactionID\" : \"00000002\",\r\n    \"TransactionDate\" : \"2017-08-26\",\r\n    \"ReferenceID\" : \"12345/PO/2016\",\r\n    \"CurrencyCode\" : \"IDR\",\r\n    \"Amount\" : \"100000.00\",\r\n    \"BeneficiaryAccountNumber\" : \"8220000118\",\r\n    \"Remark1\" : \"Transfer Test\",\r\n    \"Remark2\" : \"Online Transfer\"\r\n}";

		$this->getSignature($path, $method, $data);

		$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => self::$main_url."/banking/corporates/transfers",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $data,
			  CURLOPT_HTTPHEADER => array(
			    "authorization: Bearer ".self::$access_token,
			    "cache-control: no-cache",
			    "content-type: application/json",
			    "x-bca-key: ".self::$api_key,
			    "x-bca-signature: ".self::$signature,
			    "x-bca-timestamp: ".self::$timestamp
			  ),
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {

			  return $response;
			}
	}
}
