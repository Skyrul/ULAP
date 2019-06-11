<?php

class CellphoneScrubApi
{
	public function process($phoneNumber)
	{
		$apiFields = array(
			'CO_CODE' => '109629',
			'PASS' => '1860So!!',
			'TYPE' => 'api_atn',
		); 
		
		$apiFields['F'] = $phoneNumber;
		$apiParams = http_build_query($apiFields, '', '&');
		$apiData[] = 'https://data.searchbug.com/api/search.aspx?' . $apiParams;
		
		if( $apiData )
		{
			foreach( $this->multiCurlRequest($apiData) as $apiResult )
			{
				if (strpos($apiResult, "'") === false) 
				{
					$xml = simplexml_load_string($apiResult);
					
					// echo '<pre>';
						// print_r($xml);
					// echo '</pre>';
					
					if( !isset($xml->Results->Error) )
					{		
						if( $xml === false ) 
						{
							// echo "Failed loading XML: ";
							
							// foreach( libxml_get_errors() as $error ) 
							// {
								// echo "<br>", $error->message;
								// exit;
							// }
						} 
						else 
						{
							if( isset($xml->PhoneNumber) && $xml->PhoneNumber->LandOrCell == 'CELLULAR' )
							{
								return true;
							}
						}
					}
				}
			}
		}
		
		return false;
	}
	
	public function multiCurlRequest($data, $options = array()) 
	{
		// array of curl handles
		$curly = array();
		
		// data to be returned
		$result = array();

		// multi handle
		$mh = curl_multi_init();

		// loop through $data and create curl handles
		// then add them to the multi-handle
		foreach ($data as $id => $d) 
		{
			$curly[$id] = curl_init();

			$url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
			curl_setopt($curly[$id], CURLOPT_URL, $url);
			curl_setopt($curly[$id], CURLOPT_HEADER, 0);
			curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

			// post?
			if (is_array($d)) 
			{
				if (!empty($d['post'])) 
				{
					curl_setopt($curly[$id], CURLOPT_POST, 1);
					curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
				}
			}

			// extra options?
			if (!empty($options)) 
			{
				curl_setopt_array($curly[$id], $options);
			}

			curl_multi_add_handle($mh, $curly[$id]);
		}

		// execute the handles
		$running = null;
		do { 
			curl_multi_exec($mh, $running); 
		} while($running > 0);

		// get content and remove handles
		foreach($curly as $id => $c) 
		{
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
		}

		// all done
		curl_multi_close($mh);
	 
		return $result;
	}
}

	
?>