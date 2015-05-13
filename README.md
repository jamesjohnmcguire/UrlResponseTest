# UrlResponseTest
A light PHP framework for automated testing of HTTP response codes and errors in content.

Prerequisites: You site must support cURL.

Setup:
Copy anywhere you like on your site.  For codeigniter, this fits nicely into the libraries folder.
Move the accounts.csv and pages.csv to your document root, as they will be expected here.
Edit the csv files to match real users and pages on your site.
Edit the config file to match your login urls.

To use:
require_once APPPATH.'libraries/UrlResponseTest.php';
UrlResponseTest::test();

This will print out test results on web page.

Todo:
Add in a web crawler to automate page retrievals to be tested.
