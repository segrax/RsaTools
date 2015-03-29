<?php


$file = stream_get_contents(fopen("php://stdin", "r"));
$Elements = explode("\n", $file);
array_pop($Elements);

if(array_shift($Elements) !== "=== FOUND IT ===")  {
	echo "Key Not Found\n";
	exit;
}

function PutShort( &$pStream, $pValue ) {
	$pStream .= pack("S", $pValue);
	
	return $pStream;
}

function PutShortBE( &$pStream, $pValue ) {
	$pStream .= pack("n", $pValue);
	
	return $pStream;
}

function PutByte( &$pStream, $pValue ) {
	$pStream .= pack("C", $pValue);
	
	return $pStream;	
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

function SaveRSA( &$Elements ) {
	$content = '';
	
	$OutputOrder = array('n','e','d','p','q','dp','dq');
	
	PutShort($content, 0x8230);

	PutByte($content2, 0x02);
	PutByte($content2, 0x01);
	PutByte($content2, 0x00);
	

	foreach($OutputOrder as $indx => $Key) {

		$Bytes = str_split( $Elements[$Key], 2 );
		$Final = array();
		foreach( $Bytes as $Byte ) {
			
			$Final[] = hexdec( $Byte );
		}
		
		if( $Final[0] > 0x9F )
			array_unshift( $Final, 0 );

		PutInteger( $content2, $Final );
	}
	PutByte($content2, 0x02);
	PutByte($content2, 0x00);	
	
	
	$length = mb_strlen($content2);
	PutShortBE($content, $length);	// length minus header
	$content .= $content2;
	
	echo "-----BEGIN RSA PRIVATE KEY-----\n";
	$base = str_split(base64_encode($content), 64);

	foreach($base as $out) {
		echo $out . "\n";
	}
	echo "-----END RSA PRIVATE KEY-----\n";

	return $content;
}

$InputOrder = array('n','p','q','d','dp','dq');

// Append the array keys
$Elements = array_combine($InputOrder, $Elements);
$Elements['e'] = "010001";
$Elements['iqmp'] = "";

SaveRSA( $Elements );
