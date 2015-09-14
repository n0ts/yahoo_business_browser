yahoo_business_browser
===================================

About
-----
Login and scraping your business.yahoo.co.jp pages.
Insprired Class of login your yahoo.co.jp pages https://gist.github.com/comeonly/3032161.


Requirements
-----
PHP 5.4
HTTP_Request2 (pear install HTTP_Request2)


Installation
----
```
$ composer install
```

Sample
-----
```
require 'yahoo_business_browser.php';

$id = '<your id>';
$pass = '<your password>;
$ybb = new YahooBusinessBrowser($id, $pass);
if (empty($ybb->cookies)) {
    die('Could not login');
}

$content = $ybb->getGetBody(YahooBusinessBrowser::PARTNER_TOP_PAGE.'/DeliveryReport/index');
if ($content) {
    print_r($ybb->body);
}
```


Authors
-------------------
Author:: Naoya Nakazawa (<me@n0ts.org>)
