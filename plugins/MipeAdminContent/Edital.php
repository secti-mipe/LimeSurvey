<?php

class Edital extends LSActiveRecord
{
    public function tableName()
    {
        return 'editais';
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'length', 'max' => 255],
            ['startDate, endDate, description, expectedResults, notes', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'description' => 'Description',
            'expectedResults' => 'Expected Results',
            'notes' => 'Notes',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    protected function beforeSave()
    {
        if ($this->isNewRecord) {
            $this->created = new CDbExpression('NOW()');
        } else {
            $this->modified = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }
}