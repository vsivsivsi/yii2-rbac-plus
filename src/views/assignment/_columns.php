<?php

use yii\helpers\Url;
use kartik\grid\GridView;

$columns = [
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => Yii::$app->getModule('rbac')->userModelIdField,
        'contentOptions' => [
            'class' => 'text-center col-md-1',
        ]
    ],
    [
        'attribute' => 'org',
        'header' => 'Організація',
        'value' => function($model){
            return $model->organization->org_name;
        },
        'contentOptions' =>  ['class' => 'text-center col-md-2'],
        'filterType'=>GridView::FILTER_SELECT2,
        'filter'=>\yii\helpers\ArrayHelper::map(\app\models\Organization::find(true)->orderBy(['org_name' => SORT_ASC])->asArray()->all(), 'id', 'org_name'),
        'filterWidgetOptions'=>[
            'pluginOptions'=>['allowClear'=>true],
        ],
        'filterInputOptions'=>['placeholder' => Yii::t('app', 'Choose')],
    ],
    [
        'label' => Yii::t('app', 'Full Name'),
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'fullName',
        'value' => function($data){
            $userModel = Yii::$app->getModule('rbac')->userModelClassName;
            return $userModel::getFullStaticName($data->id);
        },
        'contentOptions' => [
            'class' => 'text-center col-md-5',
        ],
        'filter' => \yii\helpers\Html::activeInput('text', $searchModel, 'fullName', ['class' => 'form-control'])
    ],
    [
        'label' => Yii::t('app', 'Roles'),
        'attribute' => 'filterRole',
        'content' => function($model) {
            $authManager = Yii::$app->authManager;
            $idField = Yii::$app->getModule('rbac')->userModelIdField;
            $roles = [];
            foreach ($authManager->getRolesByUser($model->{$idField}) as $role) {
               $roles[] = $role->name; 
            }   
            if(count($roles)==0){
                return Yii::t("yii","(not set)");
            }else{
                return implode(",", $roles);
            }
            
        },
        'contentOptions' => [
            'class' => 'text-center col-md-4',
        ],
        'filterType'=>GridView::FILTER_SELECT2,
        'filter' => \yii\helpers\ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'name'),
        'filterWidgetOptions'=>[
            'pluginOptions'=>['allowClear'=>true],
        ],
        'filterInputOptions'=>['placeholder' => Yii::t("rbac","Choose role")],
    ],
];


$extraColums = \Yii::$app->getModule('rbac')->userModelExtraDataColumls;
if ($extraColums !== null) {
    // If extra colums exist merge and return 
    $columns = array_merge($columns, $extraColums);
}
$columns[] = [
    'class' => 'kartik\grid\ActionColumn',
    'template' => '{update}',
    'header' => Yii::t('rbac', 'Edit'),
    'dropdown' => false,
    'vAlign' => 'middle',
    'urlCreator' => function($action, $model, $key, $index) {
        return Url::to(['assignment', 'id' => $key]);
    },
            'updateOptions' => ['role' => 'modal-remote', 'title' => Yii::t('rbac', 'Update'), 'data-toggle' => 'tooltip'],
];
return $columns;