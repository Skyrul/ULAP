<?php

/**
 * This is the model class for table "{{account_pto_form}}".
 *
 * The followings are the available columns in table '{{account_pto_form}}':
 * @property integer $id
 * @property integer $account_id
 * @property string $date_of_request_start
 * @property integer $is_full_shift
 * @property integer $off_hour_from
 * @property integer $off_min_from
 * @property string $off_md_from
 * @property integer $off_hour_to
 * @property integer $off_min_to
 * @property integer $off_md_to
 * @property integer $is_make_time_up
 * @property string $date_of_make_time_up_start
 * @property string $date_of_make_time_up_end
 * @property integer $make_time_up_hour_from
 * @property integer $make_time_up_min_from
 * @property integer $make_time_up_md_from
 * @property integer $make_time_up_hour_to
 * @property integer $make_time_up_min_to
 * @property integer $make_time_up_md_to
 * @property string $reason_for_request
 * @property integer $is_pto
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class AccountPtoForm extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{account_pto_form}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, date_of_request_start, date_of_request_end, is_full_shift, is_make_time_up, is_pto', 'required'),
			array('account_id, is_full_shift, off_hour_from, off_min_from, off_hour_to, off_min_to, is_make_time_up, make_time_up_hour_from, make_time_up_min_from, make_time_up_hour_to, make_time_up_min_to, is_pto, status, is_deleted', 'numerical', 'integerOnly'=>true),
			array('off_hour_from, off_min_from, off_hour_to, off_min_to, off_md_to, off_md_from, make_time_up_hour_from, make_time_up_min_from, make_time_up_md_from, make_time_up_hour_to, make_time_up_min_to, make_time_up_md_to', 'length', 'max'=>2),
			array('computed_off_hour', 'length', 'max'=>5),
			array('reason_for_request, date_created, date_updated', 'safe'),
			array('date_of_request_start, date_of_request_end, date_of_make_time_up_start, date_of_make_time_up_end', 'safe'),
			array('is_full_shift', 'validateIsFullShift'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, date_of_request_start, date_of_request_end, is_full_shift, off_hour_from, off_min_from, off_md_from, off_hour_to, off_min_to, off_md_to, is_make_time_up, date_of_make_time_up_start, make_time_up_hour_from, make_time_up_min_from, make_time_up_md_from, make_time_up_hour_to, make_time_up_min_to, make_time_up_md_to, reason_for_request, is_pto, status, date_created, date_updated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'account_id' => 'Account',
			'date_of_request_start' => 'Date Request Start',
			'date_of_request_end' => 'Date Request End',
			'is_full_shift' => 'Requesting Time Off for Full Shift?',
			'off_hour_from' => 'Off Hour From',
			'off_min_from' => 'Off Min From',
			'off_md_from' => 'Off Md From',
			'off_hour_to' => 'Off Hour To',
			'off_min_to' => 'Off Min To',
			'off_md_to' => 'Off Md To',
			'computed_off_hour' => 'Computed Off Hour',
			'is_make_time_up' => 'Are you requesting to make this time up?',
			'date_of_make_time_up_start' => 'Date Of Make Time Up Start',
			'date_of_make_time_up_end' => 'Date Of Make Time Up End',
			'make_time_up_hour_from' => 'Make Time Up Hour From',
			'make_time_up_min_from' => 'Make Time Up Min From',
			'make_time_up_md_from' => 'Make Time Up Md From',
			'make_time_up_hour_to' => 'Make Time Up Hour To',
			'make_time_up_min_to' => 'Make Time Up Min To',
			'make_time_up_md_to' => 'Make Time Up Md To',
			'reason_for_request' => 'Reason For Request',
			'is_pto' => 'Are you requesting to use PTO?',
			'status' => 'Status',
			'is_deleted' => 'Is deleted',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('date_of_request_start',$this->date_of_request_start,true);
		$criteria->compare('date_of_request_end',$this->date_of_request_end,true);
		$criteria->compare('is_full_shift',$this->is_full_shift);
		$criteria->compare('off_hour_from',$this->off_hour_from);
		$criteria->compare('off_min_from',$this->off_min_from);
		$criteria->compare('off_md_from',$this->off_md_from,true);
		$criteria->compare('off_hour_to',$this->off_hour_to);
		$criteria->compare('off_min_to',$this->off_min_to);
		$criteria->compare('off_md_to',$this->off_md_to);
		$criteria->compare('is_make_time_up',$this->is_make_time_up);
		$criteria->compare('date_of_make_time_up_start',$this->date_of_make_time_up_start,true);
		$criteria->compare('date_of_make_time_up_end',$this->date_of_make_time_up_end,true);
		$criteria->compare('make_time_up_hour_from',$this->make_time_up_hour_from);
		$criteria->compare('make_time_up_min_from',$this->make_time_up_min_from);
		$criteria->compare('make_time_up_md_from',$this->make_time_up_md_from);
		$criteria->compare('make_time_up_hour_to',$this->make_time_up_hour_to);
		$criteria->compare('make_time_up_min_to',$this->make_time_up_min_to);
		$criteria->compare('make_time_up_md_to',$this->make_time_up_md_to);
		$criteria->compare('reason_for_request',$this->reason_for_request,true);
		$criteria->compare('is_pto',$this->is_pto);
		$criteria->compare('status',$this->status);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AccountPtoForm the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function validateIsFullShift()
	{
		if($this->is_full_shift != 1)
		{
			if(
				empty($this->off_hour_from) ||
				empty($this->off_min_from) ||
				empty($this->off_md_from) ||
				
				empty($this->off_hour_to) ||
				empty($this->off_min_to) ||
				empty($this->off_md_to) 
			)
			{
				$this->addError('is_full_shift','Complete time range for Time Off - How many hours');
			}
		}
	}
	
	public function afterFind()
	{
		
		
		
		return parent::afterFind();
	}

	public function statusName()
	{
		if($this->status == 1)
			return 'Approved';
		
		if($this->status == 2)
			return 'For Approval';
		
		if($this->status == 3)
			return 'Denied';
	}

	public function requestDateWithTime()
	{
		$time = '';
		
		if($this->date_of_request_start != '' && $this->date_of_request_start != '0000-00-00')
			$time .= date("m/d/Y", strtotime($this->date_of_request_start));
		else
		{
			$time .= 'Date Start: No date';
		}
		
		if($this->date_of_request_end != '' && $this->date_of_request_end != '0000-00-00')
			$time .= '-'.date("m/d/Y", strtotime($this->date_of_request_end));
		else
		{
			$time .= '-Date End: No date';
		}
		
		if($this->is_full_shift == 2)
		{
			if(empty($this->off_hour_from))
			{
				$offHour = '00';
			}
			else
			{
				$offHour = $this->off_hour_from;
			}
			
			if(empty($this->off_min_from))
			{
				$offMin = '00';
			}
			else
			{
				$offMin = $this->off_min_from;
			}
			
			
			if(empty($this->off_hour_to))
			{
				$offHourTo = '00';
			}
			else
			{
				$offHourTo = $this->off_hour_to;
			}
			
			if(empty($this->off_min_to))
			{
				$offMinTo = '00';
			}
			else
			{
				$offMinTo = $this->off_min_to;
			}
			
			
			$time .= ' '.$offHour.':'. $offMin .' '.$this->off_md_from;
			$time .= ' - '.$offHourTo.':'.  $offMinTo.' '.$this->off_md_to;
		}
		
		return $time;
	}
	
	public function makeItUpWithTime()
	{
		
		$time = '';
		
		if($this->is_make_time_up == 1)
		{
			if($this->date_of_make_time_up_start != '' && $this->date_of_make_time_up_start != '0000-00-00')
				$time .= date("m/d/Y", strtotime($this->date_of_make_time_up_start));
			else
			{
				$time .= 'Start Date: No date';
			}
			
			if($this->date_of_make_time_up_end != '' && $this->date_of_make_time_up_end != '0000-00-00')
				$time .= '-'.date("m/d/Y", strtotime($this->date_of_make_time_up_end));
			else
			{
				$time .= '-End Date: No date';
			}
			
			$time .= ' '.$this->make_time_up_hour_from.':'.  $this->make_time_up_min_from.' '.$this->make_time_up_md_from;
			$time .= ' - '.$this->make_time_up_hour_to.':'.  $this->make_time_up_min_to.' '.$this->make_time_up_md_to;
		}
		else
		{
			$time = 'None';
		}
		return $time;
	}
	
	public static function hoursList()
	{
		$hour = array();
		for($x = 1; $x<=12; $x++)
		{
			$hour[$x]  = $x;
		}
		
		return $hour;
	}

	public static function YesNoName($val)
	{
		if($val == 1)
			return 'Yes';
		
		if($val == 2)
			return 'No';
		
		if($val === null)
		{
			return 'No answer';
		}
		
	}
}
