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


Usage
-----
```
  $id = "<your id>";
  $pass = "<your password>";
  ybb = new YahooBusinessBrowser($id, $pass);
  ybb->getGetBody('url');
```

Authors
-------------------
Author:: Naoya Nakazawa (<me@n0ts.org>)
