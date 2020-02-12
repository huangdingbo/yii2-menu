<?php

namespace dsj\menu\controllers;


use dsj\components\controllers\WebController;
use dsj\menu\models\Menu;
use dsj\menu\models\MenuSearch;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class MenuController extends WebController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->setPagination(false);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Menu model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {

        $model = new Menu();
        $model->pid = Yii::$app->request->get('pid');
        if ($model->load(Yii::$app->request->post())) {
            //最大id
            $maxID = (Menu::find()->select('max(id) as max')->asArray()->one())['max'];
            $model->sort = $maxID;
            if ($model->save()){
                $this->redirectParent(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'item' => $model->getMenuItemById($model->pid),
        ]);
    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->redirectParent(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if ((new Menu())->getChildById($id)){
            Yii::$app->session->setFlash('danger','该标题下存在子集，删除失败!!!');
        }else{
            Yii::$app->session->setFlash('success','删除成功!!!');
            $this->findModel($id)->delete();
        }

        return $this->redirect(['index']);
    }

    public function actionStatus($id,$status){

        if ((new Menu())->getChildById($id)){
            Yii::$app->session->setFlash('warning','该标题下存在子集，不能在此节点上操作!!!');
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);
        $model->status = $status == '1' ? '0' : '1';
        if ($model->save()){
            Yii::$app->session->setFlash('success','操作成功!!!');
        }else{
            Yii::$app->session->setFlash('danger','操作失败');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionList(){
        Yii::$app->response->format = Response::FORMAT_JSON;
       $data = Menu::find()->asArray()->all();
//        if (Yii::$app->user->identity->username != 'root'){
//            foreach ($data as $key=>$item){
//                if ($item['id'] == '16' || $item['pid'] == '16'){
//                    unset($data[$key]);
//                }
//            }
//        }
//       $list = $this->getMenu($data);
       $list = $this->generateTree($this->dealData($data));

       $list[] = [
           'id' => '0',
           'text' => '菜单栏',
           'icon' => '',
           'isHeader' => true
       ];

       ArrayHelper::multisort($list,'id');
//       if (Yii::$app->user->identity->username != 'root'){
//            foreach ($list as $key=>$item){
//                if ($item['id'] == '16'){
//                    unset($list[$key]);
//                }
//            }
//        }

       return ['code' => '200','list' => array_values($list)];
    }

    private function getMenu(Array $array,$pid = 0,$level = 0){
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];
        foreach ($array as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['pid'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                $this->getMenu($array, $value['id'], $level+1);
            }
        }
        return $list;
    }

    private function generateTree($array){
        //第一步 构造数据
        $items = array();
        foreach($array as $value){
            $items[$value['id']] = $value;
        }
        //第二部 遍历数据 生成树状结构
        $tree = array();
        foreach($items as $key => $value){
            if(isset($items[$value['pid']])){
                $items[$value['pid']]['children'][] = &$items[$key];
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }

    private function dealData($data){
        $baseArr = [];
        foreach ($data as $key => $item){
            $params = [];
            if ($item['params']){
                $params = Json::decode($item['params']);
            }

            $baseArr[] = [
                'id' => $item['id'],
                'pid' => $item['pid'],
                'text' => $item['title'],
                'url' => !empty($item['route']) ? Url::to(array_merge([$item['route']],$params)) : '#',
                'targetType' => 'iframe-tab',
                'icon' => !empty($item['icon']) ? $item['icon'] : 'fa fa-circle-o',
                'urlType' => 'abosulte'
            ];
        }

        return $baseArr;
    }

}
