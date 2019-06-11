<?php 

ini_set('memory_limit', '2000M');
set_time_limit(0);

class PossibleNowController extends Controller
{
	
	public function actionGenerateRequestedDncLeadsExportFile()
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		
		//exscrub060617;
		$fileName = 'exdncrequest' . date('mdy');
		
		
		$objPHPExcel = new PHPExcel();
			
		$headers = array(
			'A' => 'Phone',
			'B' => 'MMDDYY',
		);
		
		$ctr = 1;
		
		foreach($headers as $column => $val)
		{		
			$objPHPExcel->getActiveSheet()->SetCellValue($column.$ctr, $val);
			$objPHPExcel->getActiveSheet()->getStyle($column.$ctr)->applyFromArray(array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				),
				'font'  => array(
					'bold' => true,
					'name'  => 'Calibri',
				),
			));
		}
		
		$leadCallHistories = LeadCallHistory::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.disposition = "Do Not Call" 
				AND t.company_id = "9" 
				AND lead.status != "4"
			',
		));
		
		if( $leadCallHistories )
		{
			$ctr = 2;
			
			foreach( $leadCallHistories as $leadCallHistory )
			{
				//7702551020, 03/01/2013
				$dateTime = new DateTime($leadCallHistory->date_created, new DateTimeZone('America/Chicago'));
				$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
				$dateTime = $dateTime->format('m/d/Y');
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $leadCallHistory->lead_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $dateTime);
				
				$ctr++;
			}
			
			// output to browser
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename="'.$fileName.'.csv"'); 
			header('Cache-Control: max-age=0');
			
			// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
			// $objWriter->save('php://output');
			
			// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
			// $objWriter->save(str_replace(__FILE__,'possibleNow/'.$fileName.'.csv',__FILE__));
			
			$objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
			$objWriter->setDelimiter(',');

			$objWriter->setEnclosure('');

			$objWriter->setLineEnding("\r\n");
			$objWriter->setSheetIndex(0);

			$objWriter->save('php://output');
		}
	}
	
	public function actionGenerateAllActiveLeadsExportFile()
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		 	
		//exscrub060617;
		$fileName = 'exscrub' . date('mdy');
		$ctr = 1;
		
		$objPHPExcel = new PHPExcel();
		
		$leads = Lead::model()->findAll(array(
			'with' => array('list', 'customer'),
			'condition' => ' 
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.type = 1 
				AND t.status = 1
				AND t.is_do_not_call = 0
				AND (
					t.recertify_date != "0000-00-00" 
					AND t.recertify_date IS NOT NULL 
					AND NOW() <= t.recertify_date
				)
				AND ( 
					t.home_phone_number IS NOT NULL
					OR t.office_phone_number IS NOT NULL
					OR t.mobile_phone_number IS NOT NULL
				)
				AND customer.company_id = "9"
			',
		));
		
		if( $leads )
		{
			foreach( $leads as $lead )
			{
				//7702551020, 03/01/2013
				$dateTime = new DateTime($leadCallHistory->date_created, new DateTimeZone('America/Chicago'));
				$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
				$dateTime = $dateTime->format('m/d/Y');
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $leadCallHistory->lead_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $dateTime);
				
				$ctr++;
			}
			
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename="'.$fileName.'.csv"'); 
			header('Cache-Control: max-age=0');
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
			$objWriter->save('php://output');
		}
	}
	
}

?>