# Sandwitch Opportunities - Tutorial

 ### MEV Opportunities Detection
 Bitquery's queries can help users in DeFi space by providing them with the potential MEV opportunities. 
 By providing real-time data from Mempool on transaction amount, and other key metrics, Bitquery can help users to identify such money making opportunities.


## Tutorial 
This is a tutorial to build a simple webpage using PHP code that connects to the Bitquery API and retrieves data for sandwitch attack opportunities on the Ethereum network. 
The code then displays the data on a webpage built using PHP and HTML.

## GraphQL query

For this tutorial, we will use [this](https://ide.bitquery.io/Sandwitch-Opportunity_1) query given below. The sandwitch attack observes the DEX Trade transaction in Mempool with a large purchase
amount as it will highly influence the price of the token. 

For this tutorial, the logic is that any transaction with a 'Buy Amount' greater than 
1000 USD would influence the price of the token in the pool, hence we will place two transactions around the target transaction, so that we could
get an instantaneous profit on the same.

```graphql
subscription {
  EVM(mempool: true) {
    DEXTrades(where: {Trade: {Buy: {AmountInUSD: {gt: "1000"}}}}) {
      Trade {
        Buy {
          AmountInUSD
          Buyer
          Currency {
            Name
            Symbol
            SmartContract
          }
        }
        Dex {
          OwnerAddress
          SmartContract
          ProtocolFamily
        }
        Sell {
          AmountInUSD
          Currency {
            Name
            Symbol
            SmartContract
          }
        }
      }
      Block {
        Time
      }
    }
  }
}
```

## Required Libraries
The code uses the following libraries:

HTTP_Request2: A PHP library for interacting with APIs 

## Step by Step Code Implementation

### Installation
Before writing any code, make sure that the required libraries are installed using the following command:
```shell
sudo pear install http_request2
```
### Importing the Required Libraries
The first step in the code is to import the required libraries using the import statement:

```php
<?php
require_once 'HTTP/Request2.php';
?>
```

### Establishing Connection with the Bitquery API
Next, the code connects to the Bitquery API using the HTTP_Request2 library and retrieves data on the latest sandwitch opportunities across all EVM chains (in support) using a GraphQL query. 
The query is passed as a JSON payload to the request() method, along with the necessary headers and API key.

```php
$request = new HTTP_Request2();
$request->setUrl('https://streaming.bitquery.io/graphql');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
   'follow_redirects' => TRUE
));

$request->setHeader(array(
   'Content-Type' => 'application/json',
   'X-API-KEY' => 'BQYVRzw02D5V2rWpWFii1pEbgLWCdx1y',
   'Authorization' => 'Your Bitquery API KEY'
));

$request->setBody('{"query":"subscription {\\n  EVM(mempool: true) {\\n    DEXTrades(where: {Trade: {Buy: {AmountInUSD: {gt: \\"1000\\"}}}}) {\\n      Trade {\\n        Buy {\\n          AmountInUSD\\n          Buyer\\n          Currency {\\n            Name\\n            Symbol\\n            SmartContract\\n          }\\n        }\\n        Dex {\\n          OwnerAddress\\n          SmartContract\\n          ProtocolFamily\\n        }\\n        Sell {\\n          AmountInUSD\\n          Currency {\\n            Name\\n            Symbol\\n            SmartContract\\n          }\\n        }\\n      }\\n      Block {\\n        Time\\n      }\\n    }\\n  }\\n}\\n","variables":"{}"}');

$response = $request->send();
$responseBody = $response->getBody();
$responseData = json_decode($responseBody, true);
$sandwitchOpportunityData = $responseData['data']['EVM']['DEXTrades']
```

The code retrieves an array of necessary data to execute trade from the response data and stores it in the sandwitchOpportunityData variable.

###  Building the webpage
The code then displays the retrieved data in a PHP webpage built using basic HTML. 
The webpage includes a simple table with all the real time data retrieved from the Bitquery API.

```php
echo '<html><body>';
echo '<h1>Sandwitch MEV Opportunities</h1>';
echo '<h2>Possibilities on EVM chains</h2>';
echo '<pre>';
echo formatArrayAsTable($sandwitchOpportunityData);
echo '</pre>';
echo '</body></html>';
```
The formatArrayAsTable() method is used to render the array as a table.


### Adding a Table

#### Adding table headers

This function will be used to get the column headings for the table.

```php
function getTableHeaders($data) {
   $headers = [];
   foreach ($data as $row) {
      foreach ($row as $key => $value) {
         if (!in_array($key, $headers)) {
            $headers[] = $key;
         }
      }
   }
   return $headers;
}
```

#### Helper function

This function will be used to recursively flattens the nested arrays and objects into a single-level array with concatenated keys.

```php
function flattenArray($array, $prefix = '') {
   $result = [];
   foreach ($array as $key => $value) {
      $newKey = $prefix ? "{$prefix}_{$key}" : $key;
      if (is_array($value)) {
         $result = array_merge($result, flattenArray($value, $newKey));
      } else {
         $result[$newKey] = $value;
      }
   }
   return $result;
}
```

Finally this code snippet takes up the latest DEX trades with large purchase amount from the EVM chains, and displays them in a table using simple HTML.

```php
function formatArrayAsTable($data) {
   $flattenedData = array_map('flattenArray', $data);
   $html = '<table border="1" cellpadding="5" cellspacing="0">';
   $html .= '<thead><tr>';
   $columns = getTableHeaders($flattenedData);
   foreach ($columns as $column) {
      $html .= '<th>' . htmlspecialchars($column) . '</th>';
   }
   $html .= '</tr></thead>';
   $html .= '<tbody>';
   foreach ($flattenedData as $row) {
      $html .= '<tr>';
      foreach ($columns as $column) {
         $html .= '<td>' . htmlspecialchars($row[$column] ?? '') . '</td>';
      }
      $html .= '</tr>';
   }
   $html .= '</tbody></table>';
   return $html;
}
```

### Important Note

Make sure that all the above code snippets lies within the php tag shown below.

```php
<?php
// Add all your code here 
?>
```

If you want to build up query from scratch you are welcome or you can use the [premade examples](https://ide.bitquery.io/explore/All%20queries) as well.
