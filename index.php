<?php
require_once 'HTTP/Request2.php';
$request = new HTTP_Request2();
$request->setUrl('https://streaming.bitquery.io/graphql');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
   'follow_redirects' => TRUE
));

$request->setHeader(array(
   'Content-Type' => 'application/json',
   'X-API-KEY' => 'BQYVRzw02D5V2rWpWFii1pEbgLWCdx1y',
   'Authorization' => 'Your Bitquery oAuth Token'
));

$request->setBody('{"query":"subscription {\\n  EVM(mempool: true) {\\n    DEXTrades(where: {Trade: {Buy: {AmountInUSD: {gt: \\"1000\\"}}}}) {\\n      Trade {\\n        Buy {\\n          AmountInUSD\\n          Buyer\\n          Currency {\\n            Name\\n            Symbol\\n            SmartContract\\n          }\\n        }\\n        Dex {\\n          OwnerAddress\\n          SmartContract\\n          ProtocolFamily\\n        }\\n        Sell {\\n          AmountInUSD\\n          Currency {\\n            Name\\n            Symbol\\n            SmartContract\\n          }\\n        }\\n      }\\n      Block {\\n        Time\\n      }\\n    }\\n  }\\n}\\n","variables":"{}"}');

try {
   $response = $request->send();
   if ($response->getStatus() == 200) {
      $responseBody = $response->getBody();
      $responseData = json_decode($responseBody, true);
      
      if (isset($responseData['data']['EVM'])) {
        echo '<html><body>';
        echo '<h1>Sandwitch MEV Opportunities</h1>';
        echo '<h2>Possibilities on EVM chains</h2>';
        echo '<pre>';
        echo formatArrayAsTable($responseData['data']['EVM']['DEXTrades']);
        echo '</pre>';
        echo '</body></html>';
     } else {
        echo 'EVM property not found in the response.';
     }
   }
   else {
      print 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
      $response->getReasonPhrase();
   }
}
catch(HTTP_Request2_Exception $e) {
   print 'Error: ' . $e->getMessage();
}

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
?>


