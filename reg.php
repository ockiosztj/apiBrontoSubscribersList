<?php

// *********************************** 
// REQUIRED INFORMATION IMPORTANT
// ***********************************

// ****** Bronto API *******
$Bronto_Apikeys = 'ENTER HERE YOUR API KEY';

// ****** List ID *******
$listID = 'ENTER HERE YOUR LIST ID FROM BRONTO';

// *********************************** 
// END REQUIRED INFORMATION IMPORTANT
// ***********************************

error_reporting(0);

// https://help.bronto.com/bmp/help-source/reference/r_api_soap_addorupdatecontacts.html

$Email = (isset($_REQUEST['email'])) ? $_REQUEST['email'] : NULL; // Catch the email address

if ($Email != NULL) {
    ini_set("soap.wsdl_cache_enabled", "0");
    date_default_timezone_set('America/New_York');

    try {
        $client = new SoapClient('https://api.bronto.com/v4?wsdl', array('trace' => 1, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS));

        $token = $Bronto_Apikeys;

        $sessionId = $client->login(array('apiToken' => $token))->return;
    
        $session_header = new SoapHeader("http://api.bronto.com/v4", 'sessionHeader', array('sessionId' => $sessionId));
        $client->__setSoapHeaders(array($session_header));

        $contacts = array(
            'email' => $Email,
            'listIds' => $listID
        );

        print "Adding contact with the following attributes\n";
        $write_result = $client->addOrUpdateContacts(array($contacts)
                            )->return;
        
        if ($write_result->errors) {
            $response = [
                'status'  => 'Error',
                'message' => 'There was a problem adding or updating the contact: '.json_encode($write_result->results),
                'code'    => 404
            ];
        } elseif ($write_result->results[0]->isNew == true) {
            $response = [
                'status'  => 'success',
                'message' => 'The contact has been added.  Contact Id: ' . $write_result->results[0]->id,
                'code'    => 200
            ];
        } else {
            $response = [
                'status'  => 'success',
                'message' => 'The contact\'s information has been updated.  Contact Id: ' . $write_result->results[0]->id,
                'code'    => 200
            ];
        }
    } catch (\Throwable $th) {
        $response = [
            'status'  => 'error',
            'message' => 'SOAPClient failed',
            'code'    => 404
        ];
    }
} else {
    $response = [
        'status'  => 'error',
        'message' => 'Email invalido o null.',
        'code'    => 404
    ];
}

echo json_encode($response);