<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class FilterForm extends Model
{

    public $_curriculum;
    public $_education_year;
    public $_semester;
    public $_group;
    public $_faculty;
    public $_specialty;
    public $_semester_type;
    public $_education_type;
    public $_education_form;
    public $_education_lang;
    public $_category;
    public $_gender;
    public $_department;
    public $_social_category;
    public $download;
    //public $by_student = array('10'=>);
    public function rules()
    {
        return [
            [['_curriculum', '_education_year', '_group', '_faculty', '_semester_type', '_category', '_department', '_specialty'], 'integer'],
            [['_semester', '_education_type', '_semester_type', '_education_form', '_education_lang', 'download', '_social_category'], 'string', 'max' => 64],
            [['start_date', 'finish_date'], 'string'],
        ];
    }
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
            '_education_year' => __('Education Year'),
            '_semester' => __('Semester'),
            '_group' => __('Group'),
            '_faculty' => __('Faculty'),
            '_specialty' => __('Specialty'),
            '_semester_type' => __('Semestr Type'),
            '_education_type' => __('Education Type'),
            '_education_form' => __('Education Form'),
            '_education_lang' => __('Education Lang'),
            '_social_category' => __('Social Category'),
            '_category' => __('Category'),
            '_gender' => __('Gender'),
            'download' => __('Eksport'),
        ]);
    }

    public static function byStudentGeneral(){
        return array('11'=>__('By All Student'), '12'=> __('By Social Category'));
    }

    public static function byStudent(){
        return array('11'=>__('By Level'), '12'=> __('By Specialty'), '13'=> __('By Nation'),'14'=> __('By Region'),);
    }

    public static function byTeacher(){
        return array('11'=>__('By Academic Degree'), '12'=> __('By Academic Rank'), '13'=> __('By Post'),/*'14'=> __('By Age'),*/'15'=> __('By WorkType'),);
    }

   /* public function search($params)
    {
        $this->load($params);

       // $query = self::find();
        $dataProvider = new ActiveDataProvider([
     //       'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_education_year',
                    '_semester',
                    '_subject',
                    '_group',
                    '_training_type',
                    '_lesson_pair',
                    'position',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        return $dataProvider;
    }*/
	
		

}