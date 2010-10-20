<?php
/*
 * This file is part of the UploadJuicer API library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
/**
 * PHP UploadJuicer API library
 */
class UploadJuicerApi
{
   /**
    * Request timeout
    * @var integer
    */
   public $request_timeout = 2;
   
   /**
    * Proxy address (if needed)
    * @var string
    */
   public $proxy_address = null;
   
   /**
    * Proxy port (if needed)
    * @var integer
    */
   public $proxy_port = null;
   
   /**
    * Proxy username (if needed)
    * @var string
    */
   public $proxy_user = null;
   
   /**
    * Proxy password (if needed)
    * @var string
    */
   public $proxy_pass = null;
   
   /**
    * Max number of socket reads
    * @var integer
    */
   public $read_retries = 3;
   
   /**
    * API key
    * @var string
    */
   public $key = '';
   
   /**
    * S3 Bucket to upload into (optional)
    * @var string
    */
   public $s3_bucket = null;
   
   /**
    * API URL
    * @var string
    */
   public static $url = 'http://app.uploadjuicer.com/jobs/';
   
   
   /**
    * Constructor
    * @param string $key API key
    */
   public function __construct($key, $s3_bucket = null)
   {
      $this->key = $key;
      $this->s3_bucket = $s3_bucket;
   }
   
   /**
    * Format a file path for storage on s3
    * @param string $file_path path to file
    * @param string $style optional style to prepend to file_path
    * @return string S3 file path
    */
   public function urlForS3($file_path, $style = null)
   {
      if (trim($this->s3_bucket) == '') {
         throw new RuntimeException('You haven\'t set an s3_bucket for this object.');
      }
      
      return 'http://' . $this->s3_bucket . '.s3.amazonaws.com/' . (!is_null($style) ? $style . '/' : '') . $file_path;
   }
   
   /**
    * Submit file to service
    * @see http://app.uploadjuicer.com/guides/getting_started
    * @param string $file_path accessible file path
    * @param array $outputs Output options
    * @param boolean $raw_json return the raw JSON back from request
    */
   public function submit($file_path, $outputs = array(), $raw_json = false)
   {
      if (trim($file_path) == '') {
         throw new InvalidArgumentException('You must provide a file_path to submit()');
      } elseif (count($outputs) == 0) {
         throw new InvalidArgumentException('You must provide at least one output to submit()');
      }
      
      $data = array(
         'url' => $file_path,
         'outputs' => $outputs,
      );
      return $this->sendRequest('UploadJuicerData', $this->buildRequestUrl(), $data, $raw_json);
   }
   
   /**
    * Submit file to service
    * @see http://app.uploadjuicer.com/guides/getting_started
    * @param string $file_path accessible file path
    * @param string $notification_path notification path
    * @param array $outputs Output options
    * @param boolean $raw_json return the raw JSON back from request
    */
   public function submitWithNotification($file_path, $notification_path, $outputs = array(), $raw_json = false)
   {
      if (trim($file_path) == '') {
         throw new InvalidArgumentException('You must provide a file_path to submit()');
      } elseif (trim($notification_path) == '') {
         throw new InvalidArgumentException('You must provide a notification_path to submitWithNotification()');
      } elseif (count($outputs) == 0) {
         throw new InvalidArgumentException('You must provide at least one output to submit()');
      }

      $data = array(
         'url' => $file_path,
         'notification' => $notification_path,
         'outputs' => $outputs,
      );
      
      return $this->sendRequest('UploadJuicerData', $this->buildRequestUrl(), $data, $raw_json);
   }
   
   /**
    * Get file information
    * @param string $file_id file id
    * @param boolean $raw_json return the raw JSON back from request
    */
   public function info($file_id, $raw_json = false)
   {
      if (trim($file_id) == '') {
         throw new InvalidArgumentException('You must provide a file_id to info()');
      }
      
      return $this->sendRequest('UploadJuicerData', $this->buildRequestUrl($file_id), array(), $raw_json);
   }
   
   /**
    * Send API request
    * @param string $klass return type
    * @param string $url url
    * @param array $data request data
    * @param boolean $raw_json return the raw JSON response
    * @return mixed parsed return (type is $klass)
    */
   protected function sendRequest($klass, $url, $data = array(), $raw_json = false)
   {
      $rc = new $klass();
      try {
         $json = $this->callWebService($url, $data);
      } catch (Exception $e) {
         $rc->error_code = $e->getCode() != 0 ? $e->getCode() : 500;
         $rc->error_message = $e->getMessage() != '' ? $e->getMessage() : 'General exception';
         return $raw_json ? null : $rc;
      }
      
      if ($raw_json) {
         return $json;
      }
      
      $rc->parseJson($json);
      
      return $rc;
   }
   
   /**
    * Builds the url for the request
    * @param string $file_id file id
    * @return string request url
    */
   protected function buildRequestUrl($file_id = null)
   {
      $opts = array(
         'token' => $this->key,
      );
      
      if (!is_null($file_id)) {
         $url = self::$url . $file_id . '?' . $this->optionsToParameters($opts);
      } else {
         $url = self::$url . '?' . $this->optionsToParameters($opts);
      }
      
      return $url;
   }
   
   /**
    * Turns an array of key/value pairs into URL parameters
    * @param array $option options
    * @return string url parameters
    */
   protected function optionsToParameters($options)
   {
      $rc = array();
      foreach ($options as $key => $value) {
         if (!is_null($value)) {
            $rc[] = trim($key) . '=' . urlencode(trim($value));
         }
      }
      
      return implode('&', $rc);
   }
   
   /**
    * Makes HTTP request to geocoder service
    * @param string $url URL to request
    * @param array data fields hash
    * @return string service response
    * @throws Exception if cURL library is not installed
    * @throws Exception on cURL error
    */
   protected function callWebService($url, $data = null)
   {
      if (!function_exists('curl_init')) {
         throw new RuntimeException('The cURL library is not installed.');
      }
      
      $url_info = parse_url($url);
      
      $curl = curl_init();
      
      curl_setopt($curl, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_VERBOSE, true);
      
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->request_timeout);
      curl_setopt($curl, CURLOPT_TIMEOUT, $this->request_timeout);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
         'Content-Type: application/json',
         'Accept: application/json',
      ));
      
      if (!is_null($data) && (is_array($data) && count($data) > 0)) {
         curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      }
      
      // check for proxy
      if (!is_null($this->proxy_address)) {
         curl_setopt($curl, CURLOPT_PROXY, $this->proxy_address . ':' . $this->proxy_port);
         curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxy_user . ':' . $this->proxy_pass);
      }
      
      // check for http auth:
      if (isset($url_info['user'])) {
         $user_name = $url_info['user'];
         $password = isset($url_info['pass']) ? $url_info['pass'] : '';
         
         curl_setopt($curl, CURLOPT_USERPWD, $user_name . ':' . $password);
      }
      
      $rc = '';
      $error = 'error';
      $retries = 0;
      while (trim($error) != '' && $retries < $this->read_retries) {
         $rc = curl_exec($curl);
         $error = curl_error($curl);
         $retries++;
      }
      print_r(curl_getinfo($curl));
      curl_close($curl);
      
      if (trim($error) != '') {
         throw new Exception($error);
      }
      
      return $rc;
   }
}
