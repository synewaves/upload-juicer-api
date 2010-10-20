<?php
/*
 * This file is part of the UploadJuicer API library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
class InfoTest extends PHPUnit_Framework_TestCase
{
   const SUCCESS = <<<EOT
{
  "id":"ccf8c68dcb4e8c6d1a66e14ff67c9663", 
  "url":"http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg", 
  "outputs":[{"resize":"100x100>"}], 
  "status":"finished",
  "error":null
}
EOT;

   const ERROR = <<<EOT
{
   "error": "Invalid parameters"
}
EOT;

   public function setup()
   {
      $this->api = new UploadJuicerApi('API_KEY');
      $this->id = "ccf8c68dcb4e8c6d1a66e14ff67c9663";
   }
   
   public function testEmptyArguments()
   {
      $this->setExpectedException('InvalidArgumentException');
      $results = $this->api->info('');
   }
   
   public function testHandleFailureRequest()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::ERROR));
      
      $results = $api->info($this->id);
      $this->assertFalse($results->success());
      $this->assertEquals('Invalid parameters', $results->error_message);
   }
   
   public function testRequestException()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new RuntimeException));
      
      $results = $api->info($this->id);
      $this->assertFalse($results->success());
      $this->assertEquals(500, $results->error_code);
      $this->assertEquals('General exception', $results->error_message);
   }
   
   public function testEmptyServiceResponse()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(''));
      
      $results = $api->info($this->id);
      $this->assertFalse($results->success());
      $this->assertEquals('Unspeficied error; no results were returned.', $results->error_message);
   }
   
   public function testRawXml()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::SUCCESS));
      
      $this->assertEquals(self::SUCCESS, $api->info($this->id, true));
   }
   
   public function testSuccess()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::SUCCESS));
      
      $response = $api->info($this->id);
      $this->assertTrue($response->success());
      $this->assertEquals('ccf8c68dcb4e8c6d1a66e14ff67c9663', $response->id);
      $this->assertEquals('http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg', $response->url);
      $this->assertEquals('finished', $response->status);
   }
}
