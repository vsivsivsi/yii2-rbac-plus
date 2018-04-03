<?php

namespace johnitvn\rbacplus\models;

use Yii;
use yii\data\ActiveDataProvider;
use johnitvn\rbacplus\Module;

/**
 * @author John Martin <john.itvn@gmail.com>
 * @since 1.0.0
 * 
 */
class AssignmentSearch extends \yii\base\Model {

    /**
     * @var Module $rbacModule
     */
    protected $rbacModule;

    /**
     *
     * @var mixed $id
     */
    public $id;

    /**
     *
     * @var string $login
     */
    public $login;
    public $fullName;
    public $filterRole;
    public $org;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        $this->rbacModule = Yii::$app->getModule('rbac');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'login', 'fullName', 'filterRole', 'org'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'login' => $this->rbacModule->userModelLoginFieldLabel,
            'Full Name' => Yii::t('rbac', 'Full Name'),
            'org' => 'Організація',
        ];
    }

    /**
     * Create data provider for Assignment model.    
     */
    public function search() {
        $query = call_user_func($this->rbacModule->userModelClassName . "::find");
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $params = Yii::$app->request->getQueryParams();
        $query->orderBy(['org_id' => SORT_ASC]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([$this->rbacModule->userModelIdField => $this->id]);
        $query->andFilterWhere(['org_id' => $this->org]);
        $query->andFilterWhere(['like', $this->rbacModule->userModelLoginField, $this->login]);

        if (!empty($this->fullName)){
            $query->andWhere("first_name LIKE '%" . $this->fullName . "%' OR second_name LIKE '%" . $this->fullName . "%' OR last_name LIKE '%" . $this->fullName . "%'");
        }

        if (!empty($this->filterRole)){
            $idsByRole = Yii::$app->authManager->getUserIdsByRole($this->filterRole);
            $query->andWhere(['id' => $idsByRole]);
        }

        return $dataProvider;
    }

}
