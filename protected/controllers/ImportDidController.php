<?php 

class ImportDidController extends Controller
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
		 
		
		$inputFileName = 'csv/DIDsEngaGeX.csv';

		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		$didNotAdded = array();
		try
		{
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				$valid = false;
				
				$did = $worksheet->getCell('A'.$row)->getValue();
				
				$company_name = $worksheet->getCell('B'.$row)->getValue();
				$company_name = ucwords(strtolower($company_name));
				
				$area_code = substr($did, 1, 3);
				
				if( in_array( strtolower($company_name), array('state farm', 'farmers insurance', 'safeco', 'independent market')) )
				{
					$valid = true;
				}
				
				$existingModel = CompanyDid::model()->find(array(
					'condition' => 'did = :did AND LOWER(company_name) = :company_name',
					'params' => array(
						':did' => $did,
						':company_name' => strtolower($company_name),
					),
				));
				
				if( $existingModel )
				{
					$valid = false;
				}
				
				if( $valid )
				{
					$model = new CompanyDid;
					
					$model->setAttributes(array(
						'company_name' => $company_name,
						'area_code' => $area_code,
						'did' => $did,
					));
					
					if( !$model->save(false) )
					{
						$didNotAdded[] = $model;
					}
				}	
			}	
		
			$transaction->commit();
			
			echo 'Success';
			echo '<br><br>';
			echo '<pre>';
			
			foreach($didNotAdded as $notAdded)
			{
				print_r($notAdded->attributes);
			}
			
			echo '</pre>';
		}
		catch(Exception $e)
		{
			print_r($model->attributes);
			print_r($e);
			
		}
	}
}

?>