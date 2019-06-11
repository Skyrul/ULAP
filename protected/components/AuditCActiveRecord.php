<?php
class AuditCActiveRecord extends CActiveRecord
{
	public $oldAttributes;
	public $newAttributes;
	
	protected function afterFind()
	{
		$this->oldAttributes = $this->attributes;
		
		return parent::afterFind();
	}
	
	protected function beforeSave()
	{
		$this->newAttributes = $this->attributes;
		
		return parent::beforeSave();
	}
	
}