<?php

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 300);

class CompanyController extends Controller
{
	public function actionIndex($customer_id = null)
	{
		$customer = Customer::model()->findByPk($customer_id);
		$company = $customer->company;
		
		$companyFiles = CompanyFile::model()->findAll(array(
			'condition' => 'company_id = :company_id AND status=1',
			'params' => array(
				':company_id' => $company->id,
			),
			'order' => 'date_created DESC',
		));
		
		$this->render('index', array(
			'customer' => $customer,
			'company' => $company,
			'companyFiles' => $companyFiles,
		));
	}
}