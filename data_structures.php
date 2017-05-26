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

	foreach([2,1] as $col)
	{
		if( $widths[$col] == 0 )
		{
			unset( $head[$col] );
			unset( $widths[$col] );
			$head = array_values($head);
			$widths = array_values($widths);
			foreach( $table as $i => $row )
			{
				unset( $table[$i][$col] );
				$table[$i] = array_values($table[$i]);
			}
			$col++;
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

$out = null;
$type = null;
$table = [];
$base = null;
$description = "";
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
		$type = trim($split[1]);
		$base = $split[2] ?? "";
		$description = ucfirst(str_replace('- ', '', $split[3] ?? ""));
		$out = fopen("blueprint/tables/$type.apib","w");
		$str = "#### $type <a name='$type' />\n$description\n";
		fwrite($out, $str);
	}
	elseif( in_array( substr( $line, 0, 2 ), ['- ','+ '] ) )
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

		if( $base != '(enum)' )
		{
			$name = str_replace('`','**', $name);
		}

		$type = str_replace('(','', $type);
		$type = str_replace(')','', $type);
		$split = explode(',', $type, 2);
		$type = $split[0];

		$type_prefix = "";
		if( substr($type,0,6) == 'array[' )
		{
			$type = substr($type,6, -1);
			$type_prefix = "array of ";
		}

		$type_suffix = ($split[1] ?? "");
		if( strlen( $type_suffix ) > 0 )
			$type_suffix = " *".trim($split[1] ?? "")."*";

		if( strtolower($type) != $type )
			$type = "<a href='#$type'>$type</a>";
		$table[] = [ $name, $type_prefix.$type.$type_suffix, $example, $description ];
	}
	elseif( substr( $line, 0, 4 ) == "### " )
	{
	}
	else
	{
		if( $out )
			fwrite($out, trim($line)."\n" );
	}

}

fwrite($out, make_table($table, $base));
fclose($out);
