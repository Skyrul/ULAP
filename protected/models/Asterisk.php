<?php

class Asterisk
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

		$asm->connect("64.251.13.2:5038", "ulap", "1234");
		
		return $asm;
	}
	
	public function call($params=array())
	{
		$result = false;
		
		$asm = $this->connect(); 
		
		if( $asm != false )
		{
			function parseEvent($ecode, $data, $server, $port)
			{
				// echo 'CALL <pre>';
					// echo 'ecode: '; print_r($ecode);
					// echo '<br>';
					// echo 'data: '; print_r($data);
				// echo '</pre>';
				// echo '<br>';
				// echo '<br>';
				// echo '<hr />';
			
				if( $ecode == 'newchannel' )
				{
					$channel = new AsteriskChannel;
					
					$existingChannel = AsteriskChannel::model()->find(array(
						'condition' => 'unique_id = :unique_id',
						'params' => array(
							':unique_id' => $data['Linkedid'],
						),
					));
					
					if( $existingChannel )
					{
						$channel = $existingChannel;
					}
					
					$channel->setAttributes(array(
						'channel' => $data['Channel'],
						'unique_id' => $data['Linkedid'],
						'caller_id' => $data['CallerIDName'],
						'status' => 2 //ongoing
					));
					
					$channel->save(false);
				}
				
				if( $ecode == 'varset' && $data['Variable'] == 'callhistoryID' )
				{
					$channel = new AsteriskChannel;
					
					$existingChannel = AsteriskChannel::model()->find(array(
						'condition' => 'unique_id = :unique_id',
						'params' => array(
							':unique_id' => $data['Linkedid'],
						),
					));
					
					if( $existingChannel )
					{
						$channel = $existingChannel;
					}
					
					$channel->setAttributes(array(
						'call_history_id' => $data['Value'],
						'channel' => $data['Channel'],
						'unique_id' => $data['Linkedid'],
						'caller_id' => $data['CallerIDName'],
						'status' => 2 //ongoing
					));
					
					$channel->save(false);
				}
			}
			
			
			$result = true;

			// $channel = 'SIP/outboundcall/918019001203';
			// $channel = 'SIP/outboundcall/918005158734';

			$context = 'default';
			$priority = 1;
			$application = null;
			$data = null;
			$timeout = null;
			$variable = null;
			$account = null;
			$async = null;
			$actionid = null;
			
			$asm->add_event_handler('*', 'parseEvent'); 

			/*
				- Description: Generates an outgoing call to a Extension/Context/Priority
				- Application/Data
				- Variables: (Names marked with * are required)
				- Channel: Channel name to call
				- Exten: Extension to use (requires 'Context' and 'Priority')
				- Context: Context to use (requires 'Exten' and 'Priority')
				- Priority: Priority to use (requires 'Exten' and 'Context')
				- Application: Application to use
				- Data: Data to use (requires 'Application')
				- Timeout: How long to wait for call to be answered (in ms)
				- CallerID: Caller ID to be set on the outgoing channel
				- Variable: Channel variable to set, multiple Variable: headers are allowed
				- Account: Account code
				- Async: Set to 'true' for fast origination
			*/
			
			$asm->send_request('Originate', array(
				'Channel' => 'Local/' . $params['agent_extension'], 
				'Exten' => $params['lead_phone_number'], 
				'Context' => $context,
				'Priority' => $priority,
				'CallerID' => $params['caller_id'], 
				'Variable' => 'callhistoryID='.$params['call_history_id'],
				// 'Async' => 'true',
 			));
			
			// $asm->Originate($channel, $exten, $context, $priority, $application, $data, $timeout, $callerid, $variable, $account, $async, $actionid);
			
			// $asm->send_request('Originate', array(
				// 'Channel' => "SIP/outboundcall/$phoneNumber", //to
				// 'Context' => 'default',
				// 'Priority' => 1,
				// 'Callerid' => $callerId, 
				// 'Exten' => $extension, //from
			// ));		
	
			$asm->disconnect();
		}

		return $result;
	}
	
	public function hangup($channel)
	{
		$result = false;
		
		$asm = $this->connect(); 
		
		if( $asm != false )
		{
			function parseEvent($ecode, $data, $server, $port)
			{
				// echo '<pre>';
					// echo 'ecode: '; print_r($ecode);
					// echo '<br>';
					// echo 'data: '; print_r($data);
				// echo '</pre>';
				// echo '<br>';
				// echo '<br>';
				// echo '<hr />';
			}
			
			
			$result = true;
			
			$asm->add_event_handler('*', 'parseEvent'); 
			
			$asm->Events('ON');

			$asm->send_request('Hangup', array(
				'Channel' => $channel, 
			));
			
			$asm->Events('OFF');			
	
			$asm->disconnect();
		}
	}

	public function getCallStatus($existingChannel)
	{
		$asm = $this->connect(); 
		
		if( $asm != false )
		{
			function parseEvent($ecode, $data, $server, $port)
			{
				// echo '<pre>';
					// echo 'ecode: '; print_r($ecode);
					// echo '<br>';
					// echo 'data: '; print_r($data);
				// echo '</pre>';
				// echo '<br>';
				// echo '<br>';
				// echo '<hr />';
			}
			
			$asm->add_event_handler('*', 'parseEvent'); 
			
			$asm->Events('ON');

			$response = $asm->send_request('Status', array(
				'Channel' => $existingChannel->channel, 
				'ActionID' => $existingChannel->call_history_id, 
			));

			if( $response['Response'] == 'Error' && $response['Message'] == 'No such channel' )
			{
				$leadCallHistory = LeadCallHistory::model()->find(array(
					'condition' => 'id = :id',
					'params' => array(
						':id' => $existingChannel->call_history_id,
					),
				));
				
				if( $leadCallHistory )
				{
					if( $leadCallHistory->end_call_time == null )
					{
						$leadCallHistory->end_call_time = date('Y-m-d H:i:s');
						$leadCallHistory->save(false);
					}
				}

				$existingChannel->status = 1;
				$existingChannel->save(false);
			}
			
			$asm->Events('OFF');			
	
			$asm->disconnect();
		}
				
		return $existingChannel->status;
	}
}

?>