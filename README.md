Buuum -Typeform
=======================================

[![Packagist](https://poser.pugx.org/buuum/typeform/v/stable)](https://packagist.org/packages/buuum/typeform)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg?maxAge=2592000)](#license)

## Simple and extremely flexible PHP event class

## Getting started

You need PHP >= 5.5 to use Buuum.

- [Install Buuum typeform](#install)

## Install

### System Requirements

You need PHP >= 5.5.0 to use Buuum\Typeform but the latest stable version of PHP is recommended.

### Composer

Buuum is available on Packagist and can be installed using Composer:

```
composer require buuum/typeform
```

### Manually

You may use your own autoloader as long as it follows PSR-0 or PSR-4 standards. Just put src directory contents in your vendor directory.

## Methods

### getForm
```php
$typeform = new Typeform($api_key);
$response = $typeform->getForm($token_form);
```
response
```php
array(3) {
  ["stats"]=> array(3) {
    ["showing"]=> int(1)
    ["total"]=> int(48)
    ["completed"]=> int(27)
  }
  ["questions"]=> array(9) {
    [0]=> array(3) {
      ["id"]=>
      string(20) "list_24762704_choice"
      ["question"]=>
      string(4) "test"
      ["field_id"]=>
      int(24762704)
    }
    ... 
  }
  ["responses"]=>
    array(5) {
      ["completed"]=>
      string(1) "1"
      ["token"]=>
      string(32) "bdc6d79bbf141af6b119f7bffde31bcb"
      ["metadata"]=>
      array(7) {
        ["browser"]=>
        string(7) "default"
        ["platform"]=>
        string(5) "other"
        ["date_land"]=>
        string(19) "2016-11-30 16:30:28"
        ["date_submit"]=>
        string(19) "2016-11-30 16:30:38"
        ["user_agent"]=>
        string(120) "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36"
        ["referer"]=>
        string(59) "https://kuvut1.typeform.com/to/A7moU3?hash=dsfsdf&quiz=3243"
        ["network_id"]=>
        string(10) "11ab630aa4"
      }
      ["hidden"]=>
      array(2) {
        ["hash"]=>
        string(6) "dsfsdf"
        ["quiz"]=>
        string(4) "3243"
      }
      ["answers"]=>
      array(6) {
        ["list_24762704_choice"]=>
        string(1) "1"
        ["yesno_24736784"]=>
        string(1) "0"
        ["list_24817134_choice"]=>
        string(2) "r2"
        ["list_24817136_choice"]=>
        string(2) "r2"
        ["number_24818069"]=>
        string(3) "423"
        ["score"]=>
        string(1) "3"
      }
    }
    
```

### getForms
```php
$typeform = new Typeform($api_key, $url_typeform);
$typeform->getForms();

$typeform->getStats();
$typeform->getQuestions();
$typeform->getResponses();
```

### getAllForms
```php
$typeform = new Typeform($api_key, $url_typeform);
$typeform->getAllForms();

$typeform->getStats();
$typeform->getQuestions();
$typeform->getResponses();
```

### formatDataByExport
```php
$typeform = new Typeform($api_key, $url_typeform);
$typeform->getAllForms();

$data = $typeform->formatDataByExport();

$fp = fopen('excel.csv', 'w');
fputcsv($fp, $data['headers'], "\t");
foreach ($data['rows'] as $row) {
    fputcsv($fp, $row, "\t");
}
fclose($fp);

```

### getNextPage, getPrevPage
```php
$typeform->getNextPage();
$typeform->getPrevPage();
```

## Setters

```php

$typeform->setLimit(10);
$typeform->setApiKey($api_key);
$typeform->setApiUri($api_uri);
$typeform->setFormId($formId);
$typeform->setFormIdFromUrl($url_typeform);
$typeform->setPage($page);
$typeform->setCompleted($completed);
$typeform->setSince($since);
$typeform->setUntil($until);

```

## Work with webhook
webhook.php
```php

try{
    $typeform = new Typeform($api_key);
    $response = $typeform->getPayLoad();
 }catch(Exception $e){
    echo $e->getMessage();
}

```

response

```php
array(
    'hiddens' => array(
        'hash' => ''
     ),
    'score'   => 43,
    'token'   => '4234324234332ewfwer3r2rfe'
);
```


## LICENSE

The MIT License (MIT)

Copyright (c) 2016 alfonsmartinez

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.