<?php

// Enhance data 

//----------------------------------------------------------------------------------------
function get($url, $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type 
		);		
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

//----------------------------------------------------------------------------------------

$filename = '../ala.tsv';

$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		"\t" 
		);
		
	$go = is_array($row);
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;		
			
			echo join("\t", $row) . "\n";
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					$obj->{$headings[$k]} = $v;
				}
			}
			
			if (isset($obj->doi))
			{
				$url = 'https://doi.org/' . $obj->doi;
				$text = get($url, 'text/bibliography; style=apa');
				if ($text != '')
				{
					$obj->citation = $text;
				}
			}
			
			//print_r($obj);	
			
			// rewrite
			$output_row = array();
			foreach ($headings as $key)
			{
				if (isset($obj->{$key}))
				{
					$output_row[] = $obj->{$key};
				}
				else
				{
					$output_row[] = '';
				}
			}
			
			//print_r($output_row);
			echo join("\t", $output_row);

		}
	}	
	$row_count++;
}
?>
