<?php  

CLASS GOOGLE
{
	public $client_id;
	public $client_secret;
	public $gapikey;

	public function __construct(string $client_id='',string $client_secret='',$gapikey='')
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->gapikey = $gapikey;
    }

    function start($oauthcallback="oauth2callback.php",$scope=""){
    	$client_id = $this->client_id;
		$client_secret = $this->client_secret;
		$redirect_uri = _BASEURL_.$oauthcallback;

		if(!isset($scope) OR strlen($scope) == 0){
			$scope = 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/spreadsheets https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/script.projects https://www.googleapis.com/auth/script.deployments';

		}

		// Construct the authentication URL
		$auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
		    'client_id' => $client_id,
		    'redirect_uri' => $redirect_uri,
		    'response_type' => 'code',
		    'scope' => $scope,
		    'access_type' => 'offline',
		]);


		// Redirect to the authentication URL
		header('Location: ' . $auth_url);
		exit;
    }

    function oAuthVerify($homepage="welcome.php"){
    	$client_id = $this->client_id;
		$client_secret = $this->client_secret;
		$redirect_uri = _BASEURL_."p/google/verify";

		// Exchange the authorization code for an access token
		if (isset($_GET['code'])) {
		    $code = @$_GET['code'];
		    $token_url = 'https://oauth2.googleapis.com/token';

		    $post_params = [
		        'code' => $code,
		        'client_id' => $client_id,
		        'client_secret' => $client_secret,
		        'redirect_uri' => $redirect_uri,
		        'grant_type' => 'authorization_code',
		    ];

		    $curl = curl_init();
		    curl_setopt_array($curl, [
		        CURLOPT_URL => $token_url,
		        CURLOPT_POST => true,
		        CURLOPT_POSTFIELDS => http_build_query($post_params),
		        CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_SSL_VERIFYPEER => false, // For local development, remove in production
		    ]);

		    $response = curl_exec($curl);
		    curl_close($curl);

		    $token_data = json_decode($response, true);

		    // echo $tokenx;
		    // print_r($token_data);

		    // Check if access token received
		    if (isset($token_data['access_token'])) {
		        $access_token = $token_data['access_token'];
		        // Now you have the access token, you can use it to make requests to Google APIs
		        // For example, you can store it in a session or database for later use
		        $_SESSION['access_token'] = $access_token;



		        // Check if refresh token received
	            if (isset($token_data['refresh_token'])) {
	                $refresh_token = $token_data['refresh_token'];
	                // Store the refresh token for future use
	                $_SESSION['refresh_token'] = $refresh_token;
	            }

		        $data = self::getUserData();
		        if($data){
		        	return $data;    
		        }
		        
		        // Redirect to a page where you want to proceed after successful authentication
		        // header('Location: '._BASEURL_.$homepage);
		        exit;
		    } else {
		        // Handle error in getting access token
		        die("Error in getting access token");
		    }
		} else {
		    // Handle missing authorization code
		    die("Authorization code not found");
		}
    }

    function getUserData($fields="emailAddresses,names,photos"){
    	// Access token obtained during OAuth 2.0 authentication process
		$access_token = @$_SESSION['access_token'];
		// Make a request to the Google People API to retrieve user's profile information
		$url = 'https://people.googleapis.com/v1/people/me?personFields='.$fields;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
		    'Authorization: Bearer ' . $access_token
		]);
		$response = curl_exec($ch);
		if ($response === false) {
		    // Handle API request error
		    $error = curl_error($ch);
		    die('API Request Error: ' . $error);
		} else {
		    // Decode the JSON response
		    $user_data = json_decode($response, true);

		if (isset($user_data['emailAddresses'][0]['value'])) {

		$fd = array();
		$fd['name'] = @$user_data['names'][0]['displayName'];
		$fd['photo'] = @$user_data['photos'][0]['url'];
		$fd['email'] = @$user_data['emailAddresses'][0]['value'];

		return $fd;

			}
		 	else {
		        return false;
		    }
		}
		curl_close($ch);

    }

    function glogin($page2="",$homepage="home.php"){
    $db = new DATABASE;

   switch(@$page2){
       case "login":
        self::start('p/google/verify');
       break; 
       case "verify":
        
        $data = self::oAuthVerify();
        if($data){            
        	$password = md5($data['email']."AJ2024");
            $chk = $db->query("SELECT * FROM users WHERE email LIKE '$data[email]' ");
            if(count($chk) > 0){
                unset($chk[0]['password']);
               $_SESSION['user'] = $chk[0];
               // echo "<script>window.location.href='home.php'; </script>";
            }
            else
            {
                $data['password'] = $password;
                $res = $db->insert_row("users",$data);
                $chk = $db->query("SELECT * FROM users WHERE id='$res[id]' ");
                unset($chk[0]['password']);
                $_SESSION['user'] = $chk[0];                
            }

            if($_SESSION['user']){
                $homeurl = _BASEURL_.$homepage;
                echo "<script>window.location.href='$homeurl'; </script>";
            }
        }
        else
        {
            die("Access Denied");
        }

       break;      
       default:
            if(!$_SESSION['user']){
                self::start('p/google/verify');
            }
            else
            {
                $homeurl = _BASEURL_.$homepage;
                echo "<script>window.location.href='$homeurl'; </script>";   
            }
       break;
   	} 

    }


//  Google Sheet Create etc

    public function createGoogleSheet($sheet_name, $fields, $folder_name = null) {
        // Create the Google Sheet
        $access_token = @$_SESSION['access_token'];
        $spreadsheetId = self::createSheet($sheet_name);

        // Add headers to the sheet
        self::addHeadersToSheet($access_token, $spreadsheetId, $fields);

        // Create Apps Script project
        $scriptId = self::createAppsScriptProject($spreadsheetId, $sheet_name);

        return ['spreadsheetId' => $spreadsheetId, 'scriptId' => $scriptId];
    }

	function createSheet($sheet_name) {
	    // Create the Google Sheet
	    $access_token = @$_SESSION['access_token'];
	    $url = 'https://sheets.googleapis.com/v4/spreadsheets';
	    $data = [
	        'properties' => [
	            'title' => $sheet_name
	        ]
	    ];
	    $headers = [
	        'Authorization: Bearer ' . $access_token,
	        'Content-Type: application/json'
	    ];
	    $response = self::sendRequest($url, 'POST', json_encode($data), $headers);
	    $result = json_decode($response, true);

	    return @$result['spreadsheetId'];
	}

	function addHeadersToSheet($spreadsheetId, $fields) {
	    // Add headers (fields) to the first row of the sheet
	    $access_token = @$_SESSION['access_token'];
	    $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/A1";
	    $data = [
	        'range' => 'A1',
	        'majorDimension' => 'ROWS',
	        'values' => [$fields]
	    ];
	    $headers = [
	        'Authorization: Bearer ' . $access_token,
	        'Content-Type: application/json'
	    ];
	    self::sendRequest($url, 'PUT', json_encode($data), $headers);
	}


		function getNewAccessToken() {
		    $token_url = 'https://oauth2.googleapis.com/token';

		    $data = [
		        'client_id' => $this->client_id,
		        'client_secret' => $this->client_secret,
		        'refresh_token' => @$_SESSION['refresh_token'],
		        'grant_type' => 'refresh_token'
		    ];

		    $headers = [
		        'Content-Type: application/x-www-form-urlencoded'
		    ];

		    $ch = curl_init($token_url);
		    curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    $response = curl_exec($ch);
		    curl_close($ch);

		    $token_data = json_decode($response, true);

		    if (isset($token_data['access_token'])) {
		    	$_SESSION['access_token'] = @$token_data['access_token'];
		        return $token_data['access_token'];
		    } else {
		        return null;
		    }
		}

function deleteGoogleSheetById($spreadsheetId) {
    // Access token obtained during OAuth 2.0 authentication process
    $access_token = @$_SESSION['access_token'];
    
    // URL for deleting the spreadsheet from Google Drive
    $url = "https://www.googleapis.com/drive/v3/files/{$spreadsheetId}";
    
    // Headers for the request
    $headers = [
        'Authorization: Bearer ' . $access_token,
    ];
    
    // Make the request to delete the spreadsheet
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}



function deleteSheetById($spreadsheetId, $sheetId) {
    // Access token obtained during OAuth 2.0 authentication process
    $access_token = @$_SESSION['access_token'];
    
    // URL for batch update
    $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}:batchUpdate";
    
    // Request body
    $request_body = [
        'requests' => [
            [
                'deleteSheet' => [
                    'sheetId' => $sheetId
                ]
            ]
        ]
    ];
    
    // Headers for the request
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ];
    
    // Make the request to delete the sheet
    $response = self::sendRequest($url, 'POST', json_encode($request_body), $headers);
    die($response);
    // Handle the response
    if ($response === false) {
        return 'Error in deleting sheet';
    } else {
        return 'Sheet deleted successfully';
    }
}




function createFolder($folderName) {
    // Check if the refresh token is set in the session
    if (isset($_SESSION['access_token'])) {
        // Obtain a fresh access token using the refresh token
        $refresh_token = @$_SESSION['refresh_token'];
        $access_token = self::getNewAccessToken($refresh_token);

        if ($access_token !== null) {
            // Data for creating a new folder
            $data = [
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ];

            // Example Google Drive API request to create folder
            $url = 'https://www.googleapis.com/drive/v3/files';
            $headers = [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            ];

            // Make the API request with the fresh access token
            $response = self::sendRequest($url, 'POST', json_encode($data), $headers);
            $responseData = json_decode($response, true);

            // Check if folder creation was successful
            if (isset($responseData['id'])) {
                // Return the folder ID
                return $responseData['id'];
            } else {
                // Handle failure to create folder
                return null;
            }
        } else {
            // Access token retrieval failed
            return null;
        }
    } else {
        // Refresh token not found in session
        return null;
    }
}

// function createAppsScriptProject($spreadsheetId, $scriptName) {
//         // Create Apps Script project
//         $access_token = @$_SESSION['access_token'];
//         $url = 'https://script.googleapis.com/v1/projects';
        
//         $data = [
//             'title' => $scriptName,
//             'parentId' => $spreadsheetId
//         ];
//         $headers = [
//             'Authorization: Bearer ' . $access_token,
//             'Content-Type: application/json'
//         ];
//         $response = self::sendRequest($url, 'POST', json_encode($data), $headers);
//         $result = json_decode($response, true);

//         if(isset($result['code']) && $result['code'] == "403"){
//         	die($result['message']);
//         }

//         // print_r($result);
//         // die('---');

//         return @$result;
//     }

// function createAppsScriptProject($spreadsheetId, $scriptName) {
//     // Access token from session
//     $access_token = @$_SESSION['access_token'];
    
//     // URL for creating an Apps Script project
//     $url = 'https://script.googleapis.com/v1/projects';
    
//     // Data for creating the project
//     $data = [
//         'title' => $scriptName,
//         'parentId' => $spreadsheetId
//     ];
    
//     // Headers for the request
//     $headers = [
//         'Authorization: Bearer ' . $access_token,
//         'Content-Type: application/json'
//     ];
    
//     // Send request to create the project
//     $response = self::sendRequest($url, 'POST', json_encode($data), $headers);
    
//     // Decode the response
//     $result = json_decode($response, true);

//     // Check for errors
//     if (isset($result['error'])) {
//         die($result['error']['message']);
//     }

//     // Extract the project ID from the response
//     $scriptId = $result['scriptId'];

//     // Scripts to be added to the project
//     $scripts = [
//         'doGet' => "function doGet(e) {
//   // Access the active sheet
//   var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  
//   // Get all data from the sheet
//   var data = sheet.getDataRange().getValues();
  
//   // Extract headers
//   var headers = data[0];
  
//   // Create an array to hold JSON objects
//   var jsonData = [];
  
//   // Loop through rows and create JSON objects
//   for (var i = 1; i < data.length; i++) {
//     var row = data[i];
//     var rowData = {};
    
//     for (var j = 0; j < headers.length; j++) {
//       rowData[headers[j]] = row[j];
//     }
    
//     jsonData.push(rowData);
//   }
  
//   // Convert JSON array to string
//   var jsonString = JSON.stringify(jsonData, null, 2);
  
//   // Set response content type
//   var output = ContentService.createTextOutput(jsonString);
//   output.setMimeType(ContentService.MimeType.JSON);
  
//   return output;
// }
// ",

//      'doPost' => "function doPost(e) {
//   try {
//     var data = JSON.parse(e.postData.contents);
//     var ss = SpreadsheetApp.openById('".$spreadsheetId."'); // Replace with your Google Sheet ID
//     var sheet = ss.getSheetByName('".$scriptName."'); // Replace with your sheet name

//     // Get all data in the sheet
//     var range = sheet.getDataRange();
//     var values = range.getValues();

//     // Check if the ID is already present in the sheet
//     var rowIndex = -1;
//     for (var i = 1; i < values.length; i++) {
//       if (values[i][0] == data.id) {
//         rowIndex = i + 1; // Adding 1 because array index is 0-based, and row index in Sheets is 1-based
//         break;
//       }
//     }

//     if (rowIndex !== -1) {
//       // If ID is present, update the existing row
//       sheet.getRange(rowIndex, 2, 1, values[0].length - 1).setValues([[data.category, data.variants, data.qty, data.productname, data.mrp, data.godown]]);
//       return ContentService.createTextOutput('Data updated successfully');
//     } else {
//       // If ID is not present, add a new row
//       sheet.appendRow([data.id, data.category, data.variants, data.qty, data.productname, data.mrp, data.godown]);
//       return ContentService.createTextOutput('Data added successfully');
//     }
//   } catch (error) {
//     return ContentService.createTextOutput('Error: ' + error.message);
//   }
// }"
//     ];

//     // URL for updating the project content
//     $updateUrl = "https://script.googleapis.com/v1/projects/{$scriptId}/content";

//     // Loop through each script and update the project content
//     foreach ($scripts as $functionName => $scriptContent) {
//         $scriptData = [
//             'scriptId' => $scriptId,
//             'files' => [
//                 [
//                     'name' => "{$functionName}.gs",
//                     'type' => 'SERVER_JS',
//                     'source' => $scriptContent
//                 ]
//             ]
//         ];

//         // Send request to update the project content
//        $rexx =  self::sendRequest($updateUrl, 'PUT', json_encode($scriptData), $headers);
//     }

//     $rexx2 = self::deployAppsScriptAsWebApp($scriptId);

//     var_dump($rexx);
//     // var_dump($rexx2);
//     die('-- Deploy AppScript and Test Code etc --');

//     // // Add CORS headers to the response
//     // header('Access-Control-Allow-Origin: *');
//     // header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
//     // header('Access-Control-Allow-Headers: Content-Type, Authorization');

//     // Return the result
//     return $result;
// }


function createAppsScriptProject($spreadsheetId, $scriptName) {
    // Access token from session
    $access_token = @$_SESSION['access_token'];
    
    // URL for creating an Apps Script project
    $url = 'https://script.googleapis.com/v1/projects';
    
    // Data for creating the project
    $data = [
        'title' => $scriptName,
        'parentId' => $spreadsheetId
    ];
    
    // Headers for the request
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];
    
    // Send request to create the project
    $response = self::sendRequest($url, 'POST', json_encode($data), $headers);
    
    // Decode the response
    $result = json_decode($response, true);

    // Check for errors
    if (isset($result['error'])) {
        die($result['error']['message']);
    }

    // Extract the project ID from the response
    $scriptId = $result['scriptId'];

    // Scripts to be added to the project
    $scripts = [
        'doGet' => "function doGet(e) {
  // Access the active sheet
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  
  // Get all data from the sheet
  var data = sheet.getDataRange().getValues();
  
  // Extract headers
  var headers = data[0];
  
  // Create an array to hold JSON objects
  var jsonData = [];
  
  // Loop through rows and create JSON objects
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    var rowData = {};
    
    for (var j = 0; j < headers.length; j++) {
      rowData[headers[j]] = row[j];
    }
    
    jsonData.push(rowData);
  }
  
  // Convert JSON array to string
  var jsonString = JSON.stringify(jsonData, null, 2);
  
  // Set response content type
  var output = ContentService.createTextOutput(jsonString);
  output.setMimeType(ContentService.MimeType.JSON);
  
  return output;
}
",

     'doPost' => "function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var ss = SpreadsheetApp.openById('".$spreadsheetId."'); // Replace with your Google Sheet ID
    var sheet = ss.getSheetByName('".$scriptName."'); // Replace with your sheet name

    // Get all data in the sheet
    var range = sheet.getDataRange();
    var values = range.getValues();

    // Check if the ID is already present in the sheet
    var rowIndex = -1;
    for (var i = 1; i < values.length; i++) {
      if (values[i][0] == data.id) {
        rowIndex = i + 1; // Adding 1 because array index is 0-based, and row index in Sheets is 1-based
        break;
      }
    }

    if (rowIndex !== -1) {
      // If ID is present, update the existing row
      sheet.getRange(rowIndex, 2, 1, values[0].length - 1).setValues([[data.category, data.variants, data.qty, data.productname, data.mrp, data.godown]]);
      return ContentService.createTextOutput('Data updated successfully');
    } else {
      // If ID is not present, add a new row
      sheet.appendRow([data.id, data.category, data.variants, data.qty, data.productname, data.mrp, data.godown]);
      return ContentService.createTextOutput('Data added successfully');
    }
  } catch (error) {
    return ContentService.createTextOutput('Error: ' + error.message);
  }
}"
    ];



// Manifest file content
$manifestContent = <<<'MANIFEST'
{
  "timeZone": "America/New_York",
  "dependencies": {
  },
  "exceptionLogging": "STACKDRIVER"
}
MANIFEST;

// URL for updating the project content to add the manifest file
$manifestUpdateUrl = "https://script.googleapis.com/v1/projects/{$scriptId}/content";

// Data for updating the project content to add the manifest file
$manifestData = [
    'files' => [
        [
            'name' => "appsscript",
            'type' => 'JSON',
            'source' => $manifestContent
        ]
    ]
];

// Send request to update the project content to add the manifest file
$response2 = self::sendRequest($manifestUpdateUrl, 'PUT', json_encode($manifestData), $headers);

// Check for errors in adding the manifest file
$manifestResult = json_decode($response2, true);

if (isset($manifestResult['error'])) {
    die($manifestResult['error']['message']);
}


    // Create .gs files
	$rex = array();
	if(count($scripts) > 0){
		foreach($scripts as $sk=>$sv){
			$rex[] = self::createGSFileWithManifest($scriptId, $sk, $sv);
		}
	}

    // After updating the project content, deploy the Apps Script as a web app
	$rexx2 = self::deployProjectAsAnonymous($scriptId);	


// echo "<pre>";
// print_r($rex);
// echo "--//\\--";
// var_dump($rexx2);

// // var_dump($rexx);
// die('---');

return $result;
}

function createGSFileWithManifest($scriptId, $functionName, $functionContent) {
    // Access token from session
    $access_token = @$_SESSION['access_token'];

    // URL for updating the project content
    $updateUrl = "https://script.googleapis.com/v1/projects/{$scriptId}/content";

    // Headers for the request
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];

    // Manifest file content
    $manifestContent = <<<'MANIFEST'
    {
      "timeZone": "America/New_York",
      "dependencies": {
      },
      "exceptionLogging": "STACKDRIVER"
    }
    MANIFEST;

    // Data for creating the GS file and manifest file
    $filesData = [
        'files' => [
            [
                'name' => "{$functionName}.gs",
                'type' => 'SERVER_JS',
                'source' => $functionContent
            ],
            [
                'name' => "appsscript",
                'type' => 'JSON',
                'source' => $manifestContent
            ]
        ]
    ];

    // Send request to update the project content with the GS file and manifest file
    $response = self::sendRequest($updateUrl, 'PUT', json_encode($filesData), $headers);

    // Decode the response
    $result = json_decode($response, true);

    // Check for errors
    if (isset($result['error'])) {
        return $result['error']['message'];
    }

    return "GS file '{$functionName}.gs' and manifest file 'appsscript' created successfully.";
}


function deployProjectAsAnonymous($scriptId) {
    // Access token from session
    $access_token = @$_SESSION['access_token'];

    // URL for creating a deployment
    $url = "https://script.googleapis.com/v1/projects/{$scriptId}/deployments";

    // Data for creating a deployment
    $data = [
        'versionNumber' => 1,
        'entryPoints' => [
            [
                'entryPointType' => 'WEB_APP',
                'webApp' => [
                    'url' => 'https://script.google.com/macros/s/AKfycbzg3LWUhA1LfX5f3jBwIrXGKvffxZN2iCY6pB8lzBqxdQIeWKQ/exec',
                    'entryPointConfig' => [
                        'access' => 'ANYONE_ANONYMOUS',
                        'executeAs' => 'USER_ACCESSING'
                    ]
                ]
            ]
        ]
    ];

    // Headers for the request
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];

    // Send request to create a deployment
    $response = self::sendRequest($url, 'POST', json_encode($data), $headers);

    // Decode the response
    $result = json_decode($response, true);

    // Check for errors
    if (isset($result['error'])) {
        return $result['error']['message'];
    }

    return "Project deployed successfully as anonymous.";
}









// function createStorage($sheetname, $internalsheet, $fields) {
//     // Check if $fields is a string and convert it to an array if necessary
//     if (is_string($fields)) {
//         $fields = explode(',', $fields);
//     }

//     // Check if $fields is an array
//     if (is_array($fields)) {
//         // Check if the refresh token is set in the session
//         if (isset($_SESSION['access_token'])) {
//             // Obtain a fresh access token using the refresh token
//             $refresh_token = @$_SESSION['refresh_token'];
//             $access_token = self::getNewAccessToken($refresh_token);

//             if ($access_token !== null) {
//                 // Data for creating a new spreadsheet
//                 $data = [
//                     'properties' => [
//                         'title' => $sheetname
//                     ],
//                     'sheets' => [
//                         [
//                             'properties' => [
//                                 'title' => $internalsheet,
//                                 'gridProperties' => [
//                                     'rowCount' => 1,
//                                     'columnCount' => count($fields)
//                                 ]
//                             ],
//                             'data' => [
//                                 [
//                                     'rowData' => [
//                                         ['values' => array_map(function($field) {
//                                             return ['userEnteredValue' => ['stringValue' => $field]];
//                                         }, $fields)]
//                                     ]
//                                 ]
//                             ]
//                         ]
//                     ]
//                 ];

//                 // If folder name is provided, create the folder
//                 if (!empty($folder)) {
//                     $folderId = self::createFolder($folder);
//                     if ($folderId !== null) {
//                         $data['properties']['parents'] = [$folderId];
//                     } else {
//                         return 'Failed to create folder';
//                     }
//                 }

//                 // Example Google Sheets API request
//                 $url = 'https://sheets.googleapis.com/v4/spreadsheets';
//                 $headers = [
//                     'Authorization: Bearer ' . $access_token,
//                     'Content-Type: application/json'
//                 ];

//                 // Make the API request with the fresh access token
//                 $response = self::sendRequest($url, 'POST', json_encode($data), $headers);
//                 return $response;
//             } else {
//                 // Access token retrieval failed
//                 return 'Failed to obtain fresh access token';
//             }
//         } else {
//             // Refresh token not found in session
//             return 'Refresh token not found in session';
//         }
//     } else {
//         // Handle case where $fields is not an array or string
//         return 'Invalid format for $fields. It should be an array or a comma-separated string.';
//     }
// }


function shareWithUser($spreadsheetId, $userEmail, $role) {
	$access_token = @$_SESSION['access_token'];
    // URL for sharing the spreadsheet
    $url = "https://www.googleapis.com/drive/v3/files/{$spreadsheetId}/permissions";

    // Data for sharing the spreadsheet
    $data = [
        'role' => $role, // 'reader' or 'writer' role
        'type' => 'user',
        'emailAddress' => $userEmail,
    ];

    // Headers for the request
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ];

    // Make the request to share the spreadsheet
    $response = self::sendRequest($url, 'POST', json_encode($data), $headers);

    // Handle the response (you may want to check if the sharing was successful)
    // For example:
    $responseData = json_decode($response, true);
    if (isset($responseData['id'])) {
        // echo "Spreadsheet shared successfully with {$userEmail} as {$role}.";
        return true;
    } else {
        // echo "Failed to share spreadsheet with {$userEmail}.";
        return false;
    }
}



function createStorage($sheetname, $internalsheet, $fields, $viewerEmails = [], $editorEmails = []) {
    // Check if $fields is a string and convert it to an array if necessary
    if (is_string($fields)) {
        $fields = explode(',', $fields);
    }

    // Check if $fields is an array
    if (is_array($fields)) {
        // Check if the refresh token is set in the session
        if (isset($_SESSION['access_token'])) {
            // Obtain a fresh access token using the refresh token
            $refresh_token = @$_SESSION['refresh_token'];
            $access_token = self::getNewAccessToken($refresh_token);

            if($access_token !== null) {
                // Data for creating a new spreadsheet
                $data = [
                    'properties' => [
                        'title' => $sheetname
                    ],
                    'sheets' => [
                        [
                            'properties' => [
                                'title' => $internalsheet,
                                'gridProperties' => [
                                    'rowCount' => 1,
                                    'columnCount' => count($fields)
                                ]
                            ],
                            'data' => [
                                [
                                    'rowData' => [
                                        ['values' => array_map(function($field) {
                                            return ['userEnteredValue' => ['stringValue' => $field]];
                                        }, $fields)]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                // Example Google Sheets API request
                $url = 'https://sheets.googleapis.com/v4/spreadsheets';
                $headers = [
                    'Authorization: Bearer ' . $access_token,
                    'Content-Type: application/json'
                ];

                // Make the API request with the fresh access token
                $response = self::sendRequest($url, 'POST', json_encode($data), $headers);
                $responseData = json_decode($response, true);

                // Check if spreadsheet creation was successful
                if (isset($responseData['spreadsheetId'])) {
                    $spreadsheetId = $responseData['spreadsheetId'];
                    
                    if(!is_array($viewerEmails)){
                    	$viewerEmails = explode(",",$viewerEmails);
                    }

                    // Share the spreadsheet with viewers
                    if(count($viewerEmails) > 0){
                    foreach ($viewerEmails as $viewerEmail) {
                        self::shareWithUser($spreadsheetId, $viewerEmail, 'reader');
                    }
                    }

                    // Share the spreadsheet with editors
                    if(!is_array($editorEmails)){
                    	$editorEmails = explode(",",$editorEmails);
                    }
                    if(count($editorEmails) > 0){
                    foreach ($editorEmails as $editorEmail) {
                        self::shareWithUser($spreadsheetId, $editorEmail, 'writer');
                    }
                    }

                    // Return the spreadsheet ID
                    // return $spreadsheetId;

                    // App Script Code
                    $appscript = self::createAppsScriptProject($spreadsheetId, $internalsheet);
                    $responseData['scriptId'] = @$appscript['scriptId'];
                    //================


                    return json_encode($responseData);
                } else {
                    // Handle failure to create spreadsheet
                    return 'Failed to create spreadsheet';
                }
            } else {
                // Access token retrieval failed
                return 'Failed to obtain fresh access token';
            }
        } else {
            // Refresh token not found in session
            return 'Refresh token not found in session';
        }
    } else {
        // Handle case where $fields is not an array or string
        return 'Invalid format for $fields. It should be an array or a comma-separated string.';
    }
}




// Function to send HTTP request
function sendRequest($url, $method, $data = null, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}




}


/*  <script>
        // OAuth 2.0 configuration
        const clientId = '282527403470-unjui04ov14kpffuktljb9us38c6a1k2.apps.googleusercontent.com';
        const redirectUri = '<?php echo _BASEURL_; ?>'+'p/google/verify';
        const scope = 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/spreadsheets https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';

        // Function to handle login with Google
        function loginWithGoogle() {
            const authUrl = 'https://accounts.google.com/o/oauth2/auth?' +
                'client_id=' + encodeURIComponent(clientId) +
                '&redirect_uri=' + encodeURIComponent(redirectUri) +
                '&response_type=code' +
                '&scope=' + encodeURIComponent(scope) +
                '&access_type=offline';

            // Redirect user to Google OAuth consent screen
            window.location.href = authUrl;
        }

        // Attach click event listener to the login button
        document.getElementById('loginBtn').addEventListener('click', loginWithGoogle);
    </script>
*/
 
?>