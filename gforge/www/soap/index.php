<?php

$no_gz_buffer=true;
// 0. Include GForge files for access to GForge system
require_once '../env.inc.php';
require_once $gfwww.'include/squal_pre.php';
require_once $gfcommon.'include/gettext.php';

$uri = 'http://'.$sys_default_domain;
// 1. include client and server
require_once $gfwww.'soap/nusoap.php';
//$debug = true;
// 2. instantiate server object
$server = new soap_server();
//configureWSDL($serviceName,$namespace = false,$endpoint = false,$style='rpc', $transport = 'http://schemas.xmlsoap.org/soap/http');
//$server->configureWSDL('GForgeAPI',$uri);
$server->configureWSDL('GForgeAPI',$uri,false,'rpc','http://schemas.xmlsoap.org/soap/http',$uri);

// add types
$server->wsdl->addComplexType(
	'ArrayOfstring',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')),
	'xsd:string'
);

$server->wsdl->addComplexType(
	'ArrayOfInteger',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:integer[]')),
	'xsd:integer'
);

$server->wsdl->addComplexType(
	'ArrayOflong',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:long[]')),
	'xsd:long'
);

$server->wsdl->addComplexType(
	'ArrayOfint',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:int[]')),
	'xsd:int'
);

// session/authentication
$server->register(
	'login',
	array('userid'=>'xsd:string','passwd'=>'xsd:string'),
	array('loginResponse'=>'xsd:string'),
	$uri,
	$uri.'#login');

$server->register(
	'logout',
	array('session_ser'=>'xsd:string'),
	array('logoutResponse'=>'xsd:string'),
	$uri,
	$uri.'#logout');

//
//	Include Group Functions
//
require_once $gfwww.'soap/common/group.php';

//
//	Include User Functions
//
require_once $gfwww.'soap/common/user.php';

//
//	Include tracker & tracker query Functions
//
require_once $gfwww.'soap/tracker/tracker.php';
require_once $gfwww.'soap/tracker/query.php';

//
//	Include Docman Functions
//
require_once $gfwww.'soap/docman/docman.php';

//
//	Include task manager Functions
//
require_once $gfwww.'soap/pm/pm.php';
require_once $gfwww.'soap/reporting/timeentry.php';

//
//	Include frs Functions
//
require_once $gfwww.'soap/frs/frs.php';

//
//	Include SCM Functions
//
require_once $gfwww.'soap/scm/scm.php';


$wsdl_data = $server->wsdl->serialize();

/*
if ($wsdl == "save") {
   $fp = fopen ("/tmp/SoapAPI1.wsdl", 'w');
   fputs ($fp, $wsdl_data);
   fclose ($fp);
}
*/

if ($wsdl) {
	echo $wsdl_data;
	return;
}

/**
 * continueSession - A utility method to carry on with an already established session
 *
 * @param 	string		The session key
 */
function continue_session($sessionKey) {
	session_continue($sessionKey);
}

// session/authentication
/**
 * login - Logs in a SOAP client
 * 
 * @param	string	userid	The user's unix id
 * @param	string	passwd	The user's passwd in clear text
 *
 * @return	string	the session key
 */
function login($userid, $passwd) {
	global $feedback, $session_ser;
		
	setlocale (LC_TIME, _('en_US'));

	$res = session_login_valid($userid, $passwd);
	
	if (!$res) {
		return new soap_fault('1001', 'user', "Unable to log in with userid of ".$userid, $feedback);
 	}
	
	return $session_ser;
}

/**
 * logout - Logs out a SOAP client
 *
 * @param 	string	sessionkey	The session key
 */
function logout($session_ser) {
	continue_session($session_ser);
	session_logout();
   	return "OK";
}


// 4. call the service method to initiate the transaction and send the response
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

if(isset($log) and $log != ''){
	harness('nusoap_r2_base_server',$server->headers['User-Agent'],$server->methodname,$server->request,$server->response,$server->result);
}

?>
