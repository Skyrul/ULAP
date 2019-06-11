<?php 

ini_set('memory_limit', '5000M');
set_time_limit(0);

class CronCustomerBillingCheckerController extends Controller
{
	
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		Yii::import('application.vendor.*');
		require ('anet_php_sdk/AuthorizeNet.php');
		
		define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
		define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
		define("AUTHORIZENET_SANDBOX", false);
		
		$models = CustomerBilling::model()->findAll(array(
			'condition' => '
				anet_transactionStatus = "generalError" 
				AND anet_responseCode = 0 
				AND anet_responseReasonCode = 0
				AND anet_responseReasonDescription = "Unknown"
			',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$request  = new AuthorizeNetTD;
				$response_TransactionDetails = $request->getTransactionDetails($model->anet_transId);
				
				if($response_TransactionDetails->xml->messages->resultCode == 'Ok')
				{
					$transaction_Details = $response_TransactionDetails->xml->transaction;
					$order = $transaction_Details->order;
					$anetCustomer = $transaction_Details->customer;
					$billTo = $transaction_Details->billTo;
					
					$model->setAttributes(array(
						'anet_transId' => $transaction_Details->transId,
						'anet_invoiceNumber' => $order->invoiceNumber,
						'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
						'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
						'anet_transactionType' => $transaction_Details->transactionType,
						'anet_transactionStatus' =>$transaction_Details->transactionStatus,
						'anet_responseCode' => $transaction_Details->responseCode,
						'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
						'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
						'anet_authCode'=> $transaction_Details->authCode,
						'anet_AVSResponse'=> $transaction_Details->AVSResponse,
						'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
						'anet_authAmount'=> $transaction_Details->authAmount,
						'anet_settleAmount'=> $transaction_Details->settleAmount,
						'anet_taxExempt'=> $transaction_Details->taxExempt,
						'anet_customer_Email'=> $anetCustomer->email,
						'anet_billTo_firstName'=> $billTo->firstName,
						'anet_billTo_lastName'=> $billTo->lastName,
						'anet_billTo_address'=> $billTo->address,
						'anet_billTo_city'=> $billTo->city,
						'anet_billTo_state'=> $billTo->state,
						'anet_billTo_zip'=> $billTo->zip,
						'anet_recurringBilling' => $transaction_Details->recurringBilling,
						'anet_product' => $transaction_Details->product,
						'anet_marketType' => $transaction_Details->marketType
					));
					
					if($model->save(false))
					{
						echo $model->id . ' - Transaction Updated';
						echo '<br>';
					}
				}
			}
		}
		
		echo '<br><br> end...';
	}
	
}

?>