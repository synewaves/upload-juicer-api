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
 * Base API return type
 */
class UploadJuicerData
{
   /**
    * Url
    * @var string
    */
   public $url;
   
   /**
    * File id
    * @var string
    */
   public $id;
   
   /**
    * Status
    * @var string
    */
   public $status;
   
   /**
    * Outputs
    * @var array
    */
   public $outputs;
   
   /**
    * Error message
    * @var string
    */
   public $error_message = null;
   
   /**
    * Response error code
    * @var integer
    */
   public $error_code = null;
   
   
   /**
    * Was the response a success?
    * @return boolean success
    */
   public function success()
   {
      return trim($this->error_message) == '';
   }
   
   /**
    * Is processing complete?
    * @return boolean is complete
    */
   public function isComplete()
   {
      return strtolower($this->status) == 'finished';
   }
   
   /**
    * Parse JSON into data
    * @param string $json JSON data
    */
   public function parseJson($json)
   {
      $results = json_decode($json);
      
      $this->parseErrorStatus($results);
      if (!$this->success()) {
         return;
      }
      
      $this->url = isset($results->url) ? $results->url : null;
      $this->id = isset($results->id) ? $results->id : null;
      $this->status = isset($results->status) ? $results->status : null;
      $this->outputs = isset($results->outputs) ? $results->outputs : null;
   }
   
   /**
    * Parses error from results
    * @param mixed results
    */
   protected function parseErrorStatus($results)
   {
      if (isset($results->error) && trim($results->error) != '') {
         $this->error_message = $results->error;
      } elseif (is_null($results)) {
         $this->error_message = 'Unspeficied error; no results were returned.';
      }
   }
}
