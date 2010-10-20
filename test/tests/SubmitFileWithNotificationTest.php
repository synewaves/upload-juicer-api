<?php
/*
 * This file is part of the UploadJuicer API library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
class SubmitFileWithNotificationTest extends PHPUnit_Framework_TestCase
{
   const SUCCESS = <<<EOT
{
  "id":"ccf8c68dcb4e8c6d1a66e14ff67c9663", 
  "url":"http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg", 
  "outputs":[{"resize":"100x100>"}], 
  "status":"queued",
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
      $this->file = "/path/to/file.jpg";
      $this->notification_path = "/path/to/notification";
      $this->outputs = array(
         array(
            'resize' => '100x100>',
         ),
      );
   }
   
   public function testEmptyArguments()
   {
      $this->setExpectedException('InvalidArgumentException');
      $results = $this->api->submitWithNotification('', '');
   }
   
   public function testEmptyOutputs()
   {
      $this->setExpectedException('InvalidArgumentException');
      $results = $this->api->submitWithNotification($this->file, $this->notification_path);
   }
   
   public function testEmptyNotification()
   {
      $this->setExpectedException('InvalidArgumentException');
      $results = $this->api->submitWithNotification($this->file, '');
   }
   
   public function testHandleFailureRequest()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::ERROR));
      
      $results = $api->submitWithNotification($this->file, $this->notification_path, $this->outputs);
      $this->assertFalse($results->success());
      $this->assertEquals('Invalid parameters', $results->error_message);
   }
   
   public function testRequestException()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new RuntimeException));
      
      $results = $api->submitWithNotification($this->file, $this->notification_path, $this->outputs);
      $this->assertFalse($results->success());
      $this->assertEquals(500, $results->error_code);
      $this->assertEquals('General exception', $results->error_message);
   }
   
   public function testEmptyServiceResponse()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(''));
      
      $results = $api->submitWithNotification($this->file, $this->notification_path, $this->outputs);
      $this->assertFalse($results->success());
      $this->assertEquals('Unspeficied error; no results were returned.', $results->error_message);
   }
   
   public function testRawXml()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::SUCCESS));
      
      $this->assertEquals(self::SUCCESS, $api->submitWithNotification($this->file, $this->notification_path, $this->outputs, true));
   }
   
   public function testSuccess()
   {
      $api = $this->getMock('UploadJuicerApi', array('callWebService'), array('API_KEY'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::SUCCESS));
      
      $response = $api->submitWithNotification($this->file, $this->notification_path, $this->outputs);
      $this->assertTrue($response->success());
      $this->assertEquals('ccf8c68dcb4e8c6d1a66e14ff67c9663', $response->id);
      $this->assertEquals('http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg', $response->url);
      $this->assertEquals('queued', $response->status);
   }
}
