<?php
/**
 * Project URL https://creativesdk.adobe.com/docs/web/#/articles/imageeditorui/index.html
 * Created by PhpStorm.
 * User: Andrey Averin
 * Date: 05.12.17
 * Time: 15:09
 */

namespace averinbox\adobeeditor;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

class AdobeImageEditor extends InputWidget
{
    /**\
     * @var $adobe_api_key string параметр получаемый на сервисе adobe https://console.adobe.io требуется создать новую интеграцию и получить API Key
     */
    public $adobe_api_key;


    public $options;

    /**
     * @var array   Параметры для базового модуля
     *              Пожалуйста, обратитесь к соответствующей веб-странице плагина типа ChartJs для получения возможных параметров.
     *              https://creativesdk.adobe.com/docs/web/#/articles/imageeditorui/index.html секция Configuration
     */
    public $clientOptions = [];


    /**
     * @var string
     */
    private $_id;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->_id = 'adb_' . $this->getId();
        $this->field->labelOptions['class'] = 'control-label';
        $this->field->labelOptions['for'] = 'input_' . $this->_id;
        $this->field->label('<i style="font-size: 46px; margin-top: -3px; display: block" id="i_logo_fullpath" class="pe-7s-photo" aria-hidden="true"></i>');
        $this->adobe_api_key = $this->setApiKey();
        $this->registerAssets();
    }

    public function run()
    {
        $this->renderInput();
    }

    /**
     * Генерация инпута
     */
    private function renderInput()
    {
            $this->field->label(false);
//        $img_style = 'visibility: visible';
//        if (empty($this->model->logo_fullpath)) {
//            $img_style = 'visibility: hidden !important;';
//        } else {
//
//        }

        $attribute = $this->attribute;
        echo Html::activeFileInput($this->model, $this->attribute, ['id' => $this->field->labelOptions['for'], 'class' => 'adb-file-input']);
        echo Html::img((empty($this->model->$attribute) ? '/img/icon_img.png' : $this->model->$attribute), ['class' => 'adb-image', 'id' => $this->_id]) .
            Html::tag('a','Удалить', ['href' => '#', 'class' => 'adb-delete-btn', 'style' => (!empty($this->model->$attribute) ? 'visibility: visible;' : 'visibility: hidden;'), 'id' => 'delete_' . $this->_id]);

    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function setApiKey()
    {
        if (empty($this->adobe_api_key)) {
            if (isset(\Yii::$app->params['adobeApiKey'])) {
                return \Yii::$app->params['adobeApiKey'];
            }
            throw new \yii\base\InvalidConfigException('Required to set Adobe Api Key param');
        }
        return $this->adobe_api_key;
    }

    /**
     * Установка стилей и скриптов
     */
    private function registerAssets()
    {
        $view = $this->getView();
        AdobeImageEditorAsset::register($view);
        $this->registerJS();
    }

    private function generateJsParams()
    {
        $this->clientOptions['apiKey'] = $this->adobe_api_key;
        return Json::encode($this->clientOptions);
    }

    private function registerJS()
    {
        $params = $this->generateJsParams();
        $deleteUrl = $this->options['deleteUrl'];
        $model = \yii\helpers\Json::encode(['model_name' => $this->model::className(), 'model_id' => (empty($this->model->id) ? '' : $this->model->id)]);

        $js = new JsExpression("
            var csdkImageEditor = new Aviary.Feather($params);
            
            $('#input_$this->_id').on('change', function(e) {
                var input = e.target;
                var reader = new FileReader();
                reader.onload = function (e) {
                     $('#$this->_id').attr('src', e.target.result);
                };
                reader.onloadend = function(e) {
                   csdkImageEditor.launch({
                       image: $this->_id,
                       url: e.target.result
                   });
                   $('#delete_$this->_id').css('visibility', 'visible');
                };
                reader.readAsDataURL(input.files[0]);
            });
            
            $('#$this->_id').on('click', function(e) {
                $('#input_$this->_id').trigger('click');
            });
            
            $('#delete_$this->_id').on('click', function(e) {
                e.preventDefault();
                var elem = this;
                $.ajax({
                    url: '$deleteUrl',
                    type: 'POST',
                    data: {model: $model, field_name: '$this->attribute'}, 
                    success: function(data) {
                       $('#delete_$this->_id').css('visibility', 'hidden');
                        $('#$this->_id').attr('src', '/img/icon_img.png');
                    }
                });
            });
            
        ");
        $view = $this->getView();
        $view->registerJs($js, $view::POS_READY);
    }
}
