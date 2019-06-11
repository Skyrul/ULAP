<?php 

class ImportDncController extends Controller
{
	public function actionIndex()
	{
		exit;
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		
		$inputFileName = 'csv/Graton_DNC-3.csv';

		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();

		try
		{
			$ctr = 0;
			
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				$valid = false;
				
				$phone_number = $worksheet->getCell('D'.$row)->getValue();
					
				$existingModel = Dnc::model()->find(array(
					'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
					'params' => array(
						':customer_id' => 2363,
						':phone_number' => $phone_number,
					),
				));

				if( empty($existingModel) )
				{
					$model = new Dnc;
					
					$model->customer_id = 2363;
					$model->company_id = 11;
					$model->skill_id = 44;
					$model->phone_number = $phone_number;

					if( $model->save(false) )
					{
						echo $ctr++;
						echo '<br>';	
					}
				}	
			}	
		
			$transaction->commit();
			
			echo 'Success';
			echo '<br><br>';
		}
		catch(Exception $e)
		{
			print_r($e);
			
		}
	}
}

?>