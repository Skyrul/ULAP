<?php 

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

ini_set('memory_limit', '512M');
set_time_limit(0);

class GratonSecurityController extends Controller
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
		if( !in_array($_SERVER['REMOTE_ADDR'], array('216.21.163.236', '66.7.114.18')) )
		{
			echo 'You are not allowed to access this page.';
			exit;
		}
			
		$models = GratonSecurityNumber::model()->findAll(array(
			'condition' => 'name="" OR name IS NULL',
			'limit' => 50
		));
		
		if( isset($_POST['ajax']) )
		{
			$html = '';
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$html .= '<tr>';
					
						$html .= '<td class="center">'.$model->number.'</td>';
						
						if( !empty($model->name) )
						{
							$html .= '<td class="center">'.$model->name.'</td>';
						}
						else
						{
							$html .= '<td class="center">';
							
								$html .= '
									<div class="input-group" style="width:70%; margin-left:15%">
										<input type="text" class="form-control name-input-txt" id="'.$model->id.'" value="'.$model->name.'">
									
										<span class="input-group-btn">
											<button class="btn btn-sm btn-primary name-input-save-btn" type="button">
												Save
											</button>
										</span>
									</div>
								';
								
							$html .= '</td>';
						}
						
					$html .= '</tr>';
				}
			}
			else
			{
				echo '<tr><td colspan="2">No activation codes found.</td></tr>';
			}
			
			echo json_encode(array(
				'status' => 'success',
				'html' => $html
			));
			
			Yii::app()->end();
		}
		else
		{
			$this->layout='main-no-navbar';
			
			$this->render('index', array(
				'models' => $models
			));
		}
	}
	
	public function actionUpdate()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'name' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) && isset($_POST['value']) )
		{
			$model = GratonSecurityNumber::model()->findByPk($_POST['id']);
			
			if( $model && empty($model->name) )
			{
				if( !empty($_POST['value']) )
				{
					$model->name = $_POST['value'];
					
					if( $model->save(false) )
					{
						$html .= '<td class="center">'.$model->number.'</td>';
						$html .= '<td class="center">'.$model->name.'</td>';
						
						$result['status'] = 'success';
						$result['message'] = 'Activation code: '.$model->number.' was given to ' . $model->name;
						$result['html'] = $html;
					}
				}
				else
				{
					$result['message'] = 'Name is required.';
				}
			}
			else
			{
				$result['message'] = 'Number is already taken.';
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionImport()
	{
		exit;
		
		$fileExists = file_exists('gratonSecurityFiles/Book1.xlsx');
		$inputFileName = 'gratonSecurityFiles/Book1.xlsx'; 
	
		//import from fileupload-
		if( $fileExists )
		{
			// unregister Yii's autoloader
			spl_autoload_unregister(array('YiiBase', 'autoload'));
		
			$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
			include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

			spl_autoload_register(array('YiiBase', 'autoload'));
			 
			$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			
			$worksheet = $objPHPExcel->getActiveSheet();

			// $highestRow         = $worksheet->getHighestRow(); // e.g. 10
			$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$nrColumns = ord($highestColumn) - 64;
			
			$maxCell = $worksheet->getHighestRowAndColumn();
			$excelData = $worksheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);
			$excelData = array_map('array_filter', $excelData);
			$excelData = array_filter($excelData);
			
			$highestRow = count($excelData);
			
			$ctr = 0;
			
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				$number = $worksheet->getCell('A'.$row)->getValue();
				
				$model = new GratonSecurityNumber;
				
				$model->number = $number;
				
				if( $model->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
			
			echo '<br><br>ctr: ' . $ctr;
		}
	}
	
	public function actionExport()
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		// This requires Yii's autoloader
		
		$objPHPExcel = new PHPExcel();
		
		$models = GratonSecurityNumber::model()->findAll(array(
			'condition' => 'name != "" OR name IS NOT NULL',
			'order' => 'date_updated DESC',
		));
		
		$filename = 'Graton Credit Monitoring - Used Codes';
		
		$ctr = 1;
		
		$headers = array(
			'A' => 'Code',
			'B' => 'Name',
			'C' => 'Date/Time',
		);
		
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
		
		if( $models )
		{
			$ctr = 2;
			
			foreach( $models as $model )
			{
				$date = new DateTime($model->date_updated, new DateTimeZone('America/Chicago'));
				$date->setTimezone(new DateTimeZone('America/Denver'));

				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->number);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->name);
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $date->format('m/d/Y g:i A'));
			
				$ctr++;
			}
		}
		
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	}
	
	public function actionView()
	{
		$models = GratonSecurityNumber::model()->findAll(array(
			'condition' => 'name != "" OR name IS NOT NULL',
			'order' => 'date_updated DESC',
		));
	
		$this->layout='main-no-navbar';
		
		$this->render('view', array(
			'models' => $models
		));
	}
}
?>