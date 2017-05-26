<?php

function make_table($table)
{
	$head = ['Field','Type','Comments'];

	$str = '';
	$widths = [];
	foreach( $table as $row )
	{
		foreach( $row as $j => $col )
		{
			$widths[$j] = max( $widths[$j] ?? 0, strlen( $col )+1 );
		}
	}

	foreach( $head as $j => $col )
	{
		$str .= "| ".str_pad( $col, $widths[$j] ?? 0 );
	}
	$str .= "|\n";

	$str .= "|:";
	$first = 1;
	foreach( $head as $j => $col )
	{
		$str .= str_pad( "|", $first+($widths[$j] ?? 0), "-", STR_PAD_LEFT );
		$first = 2;
	}
	$str .= "\n";

	foreach( $table as $row )
	{
		foreach( $row as $j => $col )
		{
			$str .= "| ".str_pad("$col ", $widths[$j] ?? 0);
		}
		$str .= "|\n";
	}
	$str .= "\n";

	return $str;
}

$f = file("blueprint/data_structures.apib");

$type = null;
$table = [];
foreach( $f as $line )
{
	if( substr($line,0,3) == '## ' )
	{
		if( $type !== null )
		{
			fwrite($out, make_table($table));
			fclose( $out );
			$table = [];
		}
		$split = explode(' ', $line, 4);
		$type = $split[1];
		$description = $split[3] ?? "";
		$out = fopen("blueprint/tables/$type.apib","w");
		$str = "# $type\n$description\n";
		fwrite($out, $str);
	}
	if( in_array( substr( $line, 0, 2 ), ['- ','+ '] ) )
	{
		$split = explode(' - ', substr(trim($line),2));
		$name_type = $split[0];
		$description = $split[1] ?? "";
		$split = explode(' ', $name_type, 2);
		if( count( $split ) == 2 )
		{
			$name = $split[0];
			$type = $split[1];
		}
		else
		{
			$name = $split[0];
			$type = "";
		}
		$table[] = [ $name, $type, $description ];
	}

}

fclose($out);
