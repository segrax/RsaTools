<?php

if( $argc < 2 ) {
	echo "Missing parameters\n";
	exit;
}

$file = $argv[1];
$base64_content = file_get_contents($file);

if( strpos($base64_content, "-----BEGIN RSA PRIVATE KEY-----") === false ) {
	echo "Bad Input\n";
	exit;
}

$base64_content = str_replace("-----BEGIN RSA PRIVATE KEY-----\n", "", $base64_content );
$base64_content = str_replace("-----END RSA PRIVATE KEY-----\n", "", $base64_content );


$content = base64_decode($base64_content);

//
//
//
function GetShort( &$pStream ) {
	$result = @array_shift(unpack("S", $pStream));
	$pStream = substr($pStream, 2);

	return $result;	
}

function PutShort( &$pStream, $pValue ) {
	$pStream .= pack("S", $pValue);
	
	return $pStream;
}

function GetByte( &$pStream ) {
	$result = @array_shift(unpack("C", $pStream));
	$pStream = substr($pStream, 1);

	return $result;	
}

function PutByte( &$pStream, $pValue ) {
	$pStream .= pack("C", $pValue);
	
	return $pStream;	
}

function GetInteger( &$pStream ) {
	
	$integer = array();
	 
	switch( ($ByteCount = GetByte($pStream)))  {
		case 0x81:
			$ByteCount = GetByte($pStream);
			break;
			
		case 0x82:
			$ByteCount = GetShort($pStream);
			break;
			
		default:
			break;
	}

	
	while( $ByteCount-- > 0) {
		
		$integer[] = GetByte($pStream);
	}
	
	if( $integer[0] == 0x00 ) {
		array_shift($integer);
	}

	return $integer;
}

function PutInteger( &$pStream, $pValue ) {
	
	$pStream = PutByte( $pStream, 0x02 );
	
	if( count( $pValue ) > 0xFF ) {
		
		$pStream = PutByte( $pStream, 0x82 );
		
		PutShort( $pStream, count( $pValue ) );
		
	} else {
		if( count($pValue) >= 0x80 )
			$pStream = PutByte( $pStream, 0x81 );
		
		PutByte( $pStream,count( $pValue ));
		
	}
	
	while( count($pValue) > 0) {
		$Byte = array_shift($pValue);
		PutByte( $pStream,  $Byte );
	}
	
	return $pStream;
}
//
//
//

function LoadRSA( $content, &$Elements ) {

	$marker = GetShort($content);
	
	if($marker != 0x8230) {
		echo "Not little endian\n";
		exit;
	}

	$length = GetShort($content);
	
	GetByte($content);
	$version = GetByte($content);
	if($version != 0x01) {
		echo "Unknown version\n";
		exit;
	}

	GetByte($content);

	$Elements = array();

	while(strlen($content) > 0) {
		
		$type = GetByte($content);
		switch($type) {
			
			case 0x02: 
				$Elements[] = GetInteger( $content );break;
				break;
				
		}

	}
}

function SaveRSA( &$Elements ) {
	$content = '';
	
	PutShort($content, 0x8230);
	PutByte($content, 0x02);
	PutByte($content, 0x5E);
	PutByte($content, 0x02);
	PutByte($content, 0x01);
	PutByte($content, 0x00);
	
	foreach($Elements as $Key => $Element) {
		
		if(  $Key == 'n' )
			while( count($Element) < 0x81 )
				array_unshift( $Element, 0 );
		
		if(  $Key == 'd' )
			while( count($Element) < 0x80 )
				array_unshift( $Element, 0 );
			
		if(  $Key == 'p' || $Key == 'q' || $Key == 'dp' || $Key == 'iqmp' )
			while( count($Element) < 0x41 )
				array_unshift( $Element, 0 );
		
		if( $Key == 'dq' )
			while( count($Element) < 0x40 )
				array_unshift( $Element, 0 );
			
		PutInteger( $content, $Element );
	}
	
	echo "-----BEGIN RSA PRIVATE KEY-----\n";
	$base = str_split(base64_encode($content), 64);

	foreach($base as $out) {
		echo $out . "\n";
	}
	echo "-----END RSA PRIVATE KEY-----\n";

	return $content;
}

$Elements = array();

// Order of elements in the key
$ElementOrder = array('n','e','d','p','q','dp','dq','iqmp');

// Order to output the elements of the key
$OutputOrder = array('p','q','d','dp','dq');

$mask = array(	"00f00f0000f0000f000f00000f00f0000000f00000f0000f0000f00f00000f00f00f0000000f0000f00000000f00000f0f00f00000f00f00f000000f0f00f000",
				"0000f000f00f00f000f0000f000000f0000f00000f000f000000000f00000000000000000000f00f0000f000f00000f000f000f0f000f000000f0000f00f0f00",
				"0f00000f0000f0000f0f0000f000f0000f0000000f00f000000000000000f00f00000000000f0000f00f0000f000f00f00f000f000000f000000f000000000000f00000f0000f000000f00f00000f0000000000f00000f000f0000000f000f00f000f0000f00000f0f00000f0000f0000f000f00000f000000f00000f0000000",
				"00000f0000f0000f0000000f00000f00f000f000f0f000f000f0000000f000f00f0000f0f00f000f00000f0f00f000000000f00f00f000f00f00f0f000f0f00f",
				"0f0000f000f0000f000000f0000000f0f0f0000f00000f000f0000f00f00f00f000f00f00f0000000000f0000f0000f0000f00f0000f0f000f000f0000000f00");

// Load the key
LoadRSA( $content, $Elements );

// Append the array keys
$Elements = array_combine($ElementOrder, $Elements);

foreach($OutputOrder as $index_o => $OutputKey) {
	
	$current = $mask[$index_o];
	$maskIndex = 0;
	
	// Loop each byte in the part
	foreach( $Elements[$OutputKey] as &$Byte ) {
		
		for( $byte_index = 0; $byte_index < 2; ++$byte_index ) {
				
			if( substr( $current, $maskIndex, 1 ) == "0")
				if( $byte_index == 0 )
					$Byte &= 0x0F;
				else
					$Byte &= 0xF0;
			
			++$maskIndex;
		}
	}
	unset($Byte);
}

if( $argc>2 && $argv[2] == 'dump_corrupt_pem') {
	SaveRSA( $Elements );
	exit;
}

$public_mod = array_shift($Elements);

foreach( $public_mod as $Byte ) {
	echo  str_pad(dechex( $Byte ), 2, "0", STR_PAD_LEFT);
}
echo "\n";

// Loop each part of the key
foreach($OutputOrder as $index_o => $OutputKey) {
	
	$current = $mask[$index_o];
	$maskIndex = 0;
	
	// Loop each byte in the part
	foreach( $Elements[$OutputKey] as $Byte ) {
		$Byte = str_pad(dechex( $Byte ), 2, "0", STR_PAD_LEFT);
		echo $Byte;
	}
	echo "\n";
}

foreach($mask as $Output) {
	echo $Output . "\n";
}


// Loop each key element and output the mask for it
/*
foreach($OutputOrder as $OutputKey) {
	
	// skip the public modulus
	if( $OutputKey == 'n' )
		continue;
	
	$skip = false;
	$count = 0;
	foreach( $Elements[$OutputKey] as $Byte ) {
		if(++$count != 4 ) 
			$skip = true;
		else {
			$count = 0;
		}
		switch( $count ) {
			case 0:
				echo "00";
				break;
			case 1:
				echo "f0";
				break;
			case 2:
				echo "00";
				break;
			case 3:
				echo "0f";
				break;	
		}
	}
	echo "\n";
}
*/