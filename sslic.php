<?php
/*******************************************************************************
 * ****************** SSL certificate installer for Cpanel *********************
 * *****************************************************************************
 *            Copyright (c) 2015-2016 Md. Jahidul Hamid
 * 
 * -----------------------------------------------------------------------------
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 * 
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 * 
 *     * The names of its contributors may be used to endorse or promote 
 *       products derived from this software without specific prior written
 *       permission.
 *       
 * Disclaimer:
 * 
 *     THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 *     AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *     IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 *     ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 *     LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 *     CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *     SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *     INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *     CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *     ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *     POSSIBILITY OF SUCH DAMAGE.
 */

// Log everything during development.
// If you run this on the CLI, set 'display_errors = On' in php.ini.
error_reporting(E_ALL);

$help='
SSL certificate installer for Cpanel

sslic 0.0.2

Usage:

CLI:
    Command:
        php sslic.php domain crt-file key-file CABUNDLE-file/chain-file
    Environment Variables:
        USER:  username
        PASS:  password
        EMAIL: email address
 
HTTP REQUEST:
    params:
        user: username
        pass: password
        dom: domain
        crt: Certificate file
        key: Key file
        chain: CABUNDLE file
 ';

// Define the API call.
$cpanel_host = 'localhost';
$request_uri = "https://$cpanel_host:2083/execute/SSL/install_ssl";

//Get args
foreach($argv as $arg){
    if($arg == '--help'||$arg == '-h'){
        echo $help;
        exit(0);
    }
}

//Check whether it's a CLI session and parse args
$isCLI = ( $argc > 0 );
if($isCLI){
    $tmp = getenv('EMAIL');
    if($tmp != FALSE)   { $GLOBALS['email'] = $tmp;} //optional parameter
    
    $username = getenv('USER'); //taken from the environment variable USER.
    $password = getenv('PASS'); //taken from the environment vairable PASS. (It's safer this way)
    if(!$username){err('username can not be empty!!');}
    if(!$password){err('password can not be empty!!');}
    
    if(isset($argv[1])) { $dom      = $argv[1]; } else { err('$dom missing');   }
    if(isset($argv[2])) { $crt      = $argv[2]; } else { err('$crt missing');   }
    if(isset($argv[3])) { $key      = $argv[3]; } else { err('$key missing');   }
    if(isset($argv[4])) { $chain    = $argv[4]; } else { err('$chain missing'); }
} else {
    if(isset($_REQUEST['email']))   { $GLOBALS['email']    = $_REQUEST['email']; } //optional parameter
    
    if(isset($_REQUEST['user']))    { $username = $_REQUEST['user']; } else { err('user is missing');  }
    if(isset($_REQUEST['pass']))    { $password = $_REQUEST['pass']; } else { err('pass is missing');  }
    if($username == NULL || $username == ''){err('username can not be empty!!');}
    if($password == NULL || $password == ''){err('password can not be empty!!');}
    
    if(isset($_REQUEST['dom']))     { $dom      = $_REQUEST['dom'];  } else { err('dom is missing');   }
    if(isset($_REQUEST['crt']))     { $crt      = $_REQUEST['crt'];  } else { err('crt is missing');   }
    if(isset($_REQUEST['key']))     { $key      = $_REQUEST['key'];  } else { err('key is missing');   }
    if(isset($_REQUEST['chain']))   { $chain    = $_REQUEST['chain'];} else { err('chain is missing'); }
}

//Check for invalid input
if(!isset($dom)||$dom == '' ||$dom == NULL){err('$dom is not valid');}
if(!isset($crt)||$crt == '' ||$crt == NULL||!is_file($crt)){err('$crt is not valid');}
if(!isset($key)||$key == '' ||$key == NULL||!is_file($key)){err('$key is not valid');}
if(!isset($chain)||$chain == '' ||$chain == NULL||!is_file($chain)){err('$chain is not valid');}

// Define the SSL certificate and key files.
$cert_file = realpath($crt);
$key_file = realpath($key);
$chain_file = realpath($chain);
 
// Set up the payload to send to the server.
$payload = array(
    'domain'    => $dom,
    'cert'      => file_get_contents($cert_file),
    'key'       => file_get_contents($key_file),
    'cabundle'  => file_get_contents($chain_file)
);
 
// Set up the CURL request object.
$ch = curl_init( $request_uri );
curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
curl_setopt( $ch, CURLOPT_USERPWD, $username . ':' . $password );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
  
// Set up a POST request with the payload.
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
 
// Make the call, and then terminate the CURL caller object.
$curl_response = curl_exec( $ch );
curl_close( $ch );
 
// Decode and validate output.
$response = json_decode( $curl_response );
if( empty( $response ) ) {
    err("The CURL call did not return valid JSON");
} elseif ( !$response->status ) {
    $msg = json_encode($response);
    err("The CURL call returned valid JSON, but reported errors: $msg");
}
 
// Print and exit.
res(json_encode($response));

// Error printing function
function err($msg) {
    if(isset($GLOBALS['dom'])){ $tmp = $GLOBALS['dom']; $msg = "domain: $tmp\n$msg\n"; }
    else { $msg = "$msg\n"; }
    error_log($msg,0);
    if(isset($GLOBALS['email'])){ mail($GLOBALS['email'],'Failed to install certificate in Cpanel',$msg); }
    exit(1);
}

// Success message printing function
function res($msg){
    if(isset($GLOBALS['dom'])){ $tmp = $GLOBALS['dom']; $msg = "domain: $tmp\n$msg\n"; }
    else { $msg = "$msg\n"; }
    echo $msg;
    if(isset($GLOBALS['email'])){ mail($GLOBALS['email'],'Successfully installed certificate in Cpanel',$msg); }
}

?>
