<?php

function make_table($table, $base)
{
	if( empty( $table ) )
		return "";

	$head = ['Field','Type','Example','Description'];
	if( $base == '(enum)' )
	{
		$head[0] = "Value";
	}

	$str = '';
	$widths = [0,0,0,0];
	foreach( $table as $row )
	{
		foreach( $row as $j => $col )
		{
			$widths[$j] = max( $widths[$j] ?? 0, strlen( trim($col) ) );
		}
	}

	for( $j = 1; $j <= 2; $j++ )
	{
		if( $widths[1] == 0 )
		{
			unset( $head[1] );
			unset( $widths[1] );
			$head = array_values($head);
			$widths = array_values($widths);
			foreach( $table as $i => $row )
			{
				unset( $table[$i][1] );
				$table[$i] = array_values($table[$i]);
			}
		}
	}

	foreach( $head as $j => $col )
	{
		$widths[$j] = max( 1+($widths[$j] ?? 0), strlen( $col )+1 );
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
			$str .= "| ".str_pad(trim($col)." ", $widths[$j] ?? 0);
		}
		$str .= "|\n";
	}
	$str .= "\n";

	return $str;
}

$f = file("blueprint/data_structures.apib");

$type = null;
$table = [];
$base = null;
foreach( $f as $line )
{
	if( substr($line,0,3) == '## ' )
	{
		if( $type !== null )
		{
			fwrite($out, make_table($table, $base));
			fclose( $out );
			$table = [];
		}
		$split = explode(' ', $line, 4);
		$type = $split[1];
		$base = $split[2] ?? "";
		$description = ucfirst(str_replace('- ', '', $split[3] ?? ""));
		$out = fopen("blueprint/tables/$type.apib","w");
		$str = "# $type <a name='$type' />\n$description\n";
		fwrite($out, $str);
	}
	if( in_array( substr( $line, 0, 2 ), ['- ','+ '] ) )
	{
		$split = explode(' - ', substr(trim($line),2));
		$name_type = $split[0];
		$description = $split[1] ?? "";
		$example = "";
		$split = explode(' ', $name_type, 2);
		if( count( $split ) == 2 )
		{
			$name = $split[0];
			$type = $split[1];
			if( substr($name,-1) == ':' )
			{
				$name = substr($name, 0, -1);
				$split = explode('(', $type, 2);
				$type = '('.$split[1];
				$example = trim($split[0]);
			}
		}
		else
		{
			$name = $split[0];
			$type = "";
		}

		$type = str_replace('(','', $type);
		$type = str_replace(')','', $type);
		$split = explode(',', $type, 2);
		$type = $split[0];
		if( strtolower($type) != $type )
		{
			$type = "<a href='#$type'>$type</a>";
		}
		$type .= $split[1] ?? "";
		$table[] = [ $name, $type, $example, $description ];
	}

}

fclose($out);
