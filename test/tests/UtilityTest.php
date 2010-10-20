<?php
/*
 * This file is part of the UploadJuicer API library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
class UtilityTest extends PHPUnit_Framework_TestCase
{
   const QUEUED = <<<EOT
{
  "id":"ccf8c68dcb4e8c6d1a66e14ff67c9663", 
  "url":"http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg", 
  "outputs":[{"resize":"100x100>"}], 
  "status":"queued",
  "error":null
}
EOT;

   const COMPLETE = <<<EOT
{
  "id":"ccf8c68dcb4e8c6d1a66e14ff67c9663", 
  "url":"http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg", 
  "outputs":[{"resize":"100x100>"}], 
  "status":"finished",
  "error":null
}
EOT;

   public function setup()
   {
      $this->api = new UploadJuicerApi('API_KEY');
   }

   
   public function testS3BucketWithoutBucket()
   {
      $this->setExpectedException('RuntimeException');
      $results = $this->api->urlForS3('path/to/file.jpg');
   }
   
   public function testS3BucketName()
   {
      $api = new UploadJuicerApi('API_KEY', 'my-s3-bucket');
      $this->assertEquals('http://my-s3-bucket.s3.amazonaws.com/path/to/file.jpg', $api->urlForS3('path/to/file.jpg'));
   }
   
   public function testS3BucketNameWithStyle()
   {
      $api = new UploadJuicerApi('API_KEY', 'my-s3-bucket');
      $this->assertEquals('http://my-s3-bucket.s3.amazonaws.com/thumbnail/path/to/file.jpg', $api->urlForS3('path/to/file.jpg', 'thumbnail'));
   }
   
   public function testIsCompleteIfQueued()
   {
      $result = new UploadJuicerData();
      $result->parseJson(self::QUEUED);
      
      $this->assertFalse($result->isComplete());
   }
   
   public function testIsCompleteIfFinished()
   {
      $result = new UploadJuicerData();
      $result->parseJson(self::COMPLETE);
      
      $this->assertTrue($result->isComplete());
   }
}
