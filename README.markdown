# UploadJuicer API

A PHP 5 interface to the [UploadJuicer API](http://www.uploadjuicer.com/)

## Examples

Send a file:

    include_once 'upload-juicer-api/init.php';
    
    $api = new UploadJuicerApi('YOUR_API_KEY');
    $rc = $api->submit('http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg', array(
       array('resize' => '100x100>'),
    ));
    
    echo $rc->id; // "ccf8c68dcb4e8c6d1a66e14ff67c9663"
    echo $rc->status; // "queued"
    
Send a file with notifications:

    include_once 'upload-juicer-api/init.php';
    
    $api = new UploadJuicerApi('YOUR_API_KEY');
    $rc = $api->submitWithNotification('http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg', 'http://example.com/callback_url/", array(
       array('resize' => '100x100>'),
    ));
    
    echo $rc->id; // "ccf8c68dcb4e8c6d1a66e14ff67c9663"
    echo $rc->status; // "queued"
    
Check status of file:

    include_once 'upload-juicer-api/init.php';
    
    $api = new UploadJuicerApi('YOUR_API_KEY');
    $rc = $api->info('ccf8c68dcb4e8c6d1a66e14ff67c9663');
    
    echo $rc->id; // "ccf8c68dcb4e8c6d1a66e14ff67c9663"
    echo $rc->status; // "finished"
    
Send file using S3:

    include_once 'upload-juicer-api/init.php';
    
    $api = new UploadJuicerApi('YOUR_API_KEY', 'YOUR_S3_BUCKET_NAME');
    $rc = $api->submit('http://farm3.static.flickr.com/2084/2222523486_5e1894e314.jpg', array(
       array(
          'resize' => '100x100>'
          'url' => $api->urlForS3('/path/to/file.jpg'),  // "http://YOUR_S3_BUCKET_NAME.s3.amazonaws.com/path/to/file.jpg"
       ),
    ));
    
    echo $rc->id; // "ccf8c68dcb4e8c6d1a66e14ff67c9663"
    echo $rc->status; // "queued"

## Running the tests

You'll need [PHPUnit 3.4+](http://www.phpunit.de/) installed to run the test suite.

* Open `test/phpunit.xml.dis`t and modify as needed.
* Rename to `phpunit.xml`
* Run `phpunit` from within `/test` directory.

Copyright (c) 2010 Matthew Vince, released under the MIT license