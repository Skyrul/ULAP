<?php

class AsteriskWebphone
{
	public function connect()
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register extension
		$extPath = Yii::getPathOfAlias('ext.phpagi'); 
		require_once($extPath . '/phpagi-asmanager.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		
		$asm = new AGI_AsteriskManager;

		$asm->connect("107.182.238.147:5038", "ulap", "1234");
		
		return $asm;
	}
	
	public function getStatus()
	{
		$result = false;
		
		$asm = $this->connect(); 
		
		if( $asm != false )
		{
			$result = true;
			
			function parseEvent($ecode, $data, $server, $port)
			{
				if( $ecode == 'status' )
				{
					// echo 'CALL <pre>';
						// echo 'ecode: '; print_r($ecode);
						// echo '<br>';
						// echo 'data: '; print_r($data);
						// echo '<br>';
						// echo 'channel: ' . $channel;
					// echo '</pre>';
					// echo '<br>';
					// echo '<br>';
					// echo '<hr />';
					
					$explodedChannel = explode('-', $data['Channel']);
					$channel = $explodedChannel[0];
					
					$existingChannel = AsteriskChannel::model()->find(array(
						'condition' => '
							channel = :channel
							AND call_history_id IS NOT NULL 
							AND unique_id IS NULL
						',
						'params' => array(
							':channel' => $channel
						),
						'order' => 'date_created DESC',
					));
					
					if( $existingChannel )
					{
						$existingChannel->setAttributes(array(
							'channel' => $data['Channel'],
							'unique_id' => $data['Linkedid'],
							'caller_id' => $data['CallerIDName'],
							'status' => 2 //ongoing
						));

						$existingChannel->save(false);
					}
				}
			}
			
			$asm->add_event_handler('*', 'parseEvent'); 
			
			$asm->send_request('Status', array());

			$asm->disconnect();
		}

		return $result;
	}
	
}

?>