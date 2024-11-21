<?php

namespace common\components;

use common\models\Category;
use common\models\system\SystemMessage;
use tigrov\pgsql\QueryBuilder;
use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Command;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class Config extends Component
{
    const LANGUAGE_DEFAULT = self::LANGUAGE_UZBEK;

    const LANGUAGE_UZBEK = 'uz-UZ';
    const LANGUAGE_CYRILLIC = 'oz-UZ';
    const LANGUAGE_RUSSIAN = 'ru-RU';
    const LANGUAGE_ENGLISH = 'en-US';
    const LANGUAGE_KARAKALPAK = 'kk-UZ';
    const LANGUAGE_TAJIK = 'tg-TG';
    const LANGUAGE_KAZAKH = 'kz-KZ';
    const LANGUAGE_KYRGYZ = 'kg-KG';
    const LANGUAGE_TURKMEN = 'tm-TM';
    const LANGUAGE_UZBEK_CODE = 'uz';
    const LANGUAGE_CYRILLIC_CODE = 'oz';
    const LANGUAGE_RUSSIAN_CODE = 'ru';
    const LANGUAGE_ENGLISH_CODE = 'en';
    const LANGUAGE_KARAKALPAK_CODE = 'kk';
    const LANGUAGE_TAJIK_CODE = 'tg';
    const LANGUAGE_KAZAKH_CODE = 'kz';
    const LANGUAGE_KYRGYZ_CODE = 'kg';
    const LANGUAGE_TURKMEN_CODE = 'tm';

    const PASSWORD_FAKE_VALUE = '**************';
    public static $_sharedPaths;
    protected static $_configurations = [];

    public static function isLatinCyrill()
    {
        $languages = self::getLanguageOptions();
        return isset($languages[self::LANGUAGE_CYRILLIC]) && isset($languages[self::LANGUAGE_UZBEK]);
    }

    public static function getAllLanguagesWithLabels()
    {
        return [
            self::LANGUAGE_UZBEK => __("O'zbekcha"),
            self::LANGUAGE_CYRILLIC => __("Ўзбекча"),
            self::LANGUAGE_RUSSIAN => __('Русский'),
            self::LANGUAGE_ENGLISH => __('English'),
            self::LANGUAGE_KARAKALPAK => __("Қарақалпақша"),
            self::LANGUAGE_TAJIK => __('Тоҷикӣ'),
            self::LANGUAGE_KAZAKH => __('Қазақча'),
            self::LANGUAGE_TURKMEN => __('Türkmence'),
            self::LANGUAGE_KYRGYZ => __('Кыргызча'),
        ];
    }

    public static function getShortLanguageCodes()
    {
        return [
            self::LANGUAGE_UZBEK => self::LANGUAGE_UZBEK_CODE,
            self::LANGUAGE_CYRILLIC => self::LANGUAGE_CYRILLIC_CODE,
            self::LANGUAGE_RUSSIAN => self::LANGUAGE_RUSSIAN_CODE,
            self::LANGUAGE_ENGLISH => self::LANGUAGE_ENGLISH_CODE,
            self::LANGUAGE_KARAKALPAK => self::LANGUAGE_KARAKALPAK_CODE,
            self::LANGUAGE_TAJIK => self::LANGUAGE_TAJIK_CODE,
            self::LANGUAGE_KAZAKH => self::LANGUAGE_KAZAKH_CODE,
            self::LANGUAGE_TURKMEN => self::LANGUAGE_TURKMEN_CODE,
            self::LANGUAGE_KYRGYZ => self::LANGUAGE_KYRGYZ_CODE,
        ];
    }

    public static function getLanguageOptions()
    {
        return self::getAllLanguagesWithLabels();
    }

    public static function getLanguages()
    {
        $languages = [];
        if (Yii::$app->urlManager instanceof \codemix\localeurls\UrlManager)
            $languages = Yii::$app->urlManager->languages;

        return $languages;
    }

    public static function getLanguageCode($locale = false)
    {
        $locale = $locale ?: Yii::$app->language;
        $languages = self::getShortLanguageCodes();

        return isset($languages[$locale]) ? $languages[$locale] : self::getLanguageCode(self::LANGUAGE_DEFAULT);
    }

    public static function getLanguageLabel($locale = false)
    {
        $languagesWithLocale = self::getLanguageOptions();
        $locale = $locale ?: Yii::$app->language;
        return isset($languagesWithLocale[$locale]) ? $languagesWithLocale[$locale] : self::getLanguageLabel(Config::LANGUAGE_DEFAULT);
    }

    public static function getAsArray($path, $default = null)
    {
        if (array_key_exists($path, static::$_configurations)) {
            $values = explode("\n", static::$_configurations[$path]);
            array_walk($values, function (&$item, $index) {
                $item = trim($item);
            });
            return array_filter($values);
        }

        return $default;
    }

    public static function getBackupProvider($limit = 20)
    {
        $dir = Yii::getAlias('@backups') . DS;
        $data = [];
        foreach (glob($dir . '*.bak.*') as $file) {
            $data[] = [
                'name' => basename($file),
                'size' => Yii::$app->formatter->asSize(filesize($file)),
                'time' => Yii::$app->formatter->asDatetime(filemtime($file)),
                'date' => intval(filemtime($file)),
            ];
        }

        return new ArrayDataProvider([
            'allModels' => $data,
            'sort' => [
                'defaultOrder' => [
                    'date' => SORT_DESC
                ],
                'attributes' => [
                    'name',
                    'size',
                    'time',
                    'date'
                ],
            ],
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);
    }

    public static function isLanguageEnable($lang)
    {
        return boolval(self::get("system_language_$lang")) || $lang == self::LANGUAGE_UZBEK || $lang == self::LANGUAGE_RUSSIAN || $lang == self::LANGUAGE_CYRILLIC;
    }

    public function init()
    {
        self::getSharedPaths();
        self::$_configurations = self::getConfigs();

        parent::init();
    }

    public static function getSharedPaths()
    {
        if (!self::$_sharedPaths) {
            $paths = self::getAllConfiguration();
            $result = [];
            foreach ($paths as $items) {
                foreach ($items as $item) {
                    $result[$item['path']] = $item['type'];
                }
            }

            self::$_sharedPaths = $result;
        }
        return self::$_sharedPaths;
    }

    const CONFIG_SYS_DEV_TOOLBAR_ENABLE = 'system/developer/toolbar';
    const CONFIG_SYS_DEV_TOOLBAR_IP = 'system/developer/ip';
    const CONFIG_SYS_DEV_EMAILS = 'system/developer/emails';
    const CONFIG_SYS_RECAPTCHA_ENABLE = 'system/recaptcha/enable';
    const CONFIG_SYS_RECAPTCHA_KEY = 'system/recaptcha/key';
    const CONFIG_SYS_RECAPTCHA_SECRET = 'system/recaptcha/secret';
    const CONFIG_SYS_HEMIS_LOGIN = 'system/hemis/login';
    const CONFIG_SYS_HEMIS_PASSWORD = 'system/hemis/password';
    const CONFIG_SYS_HEMIS_TOKEN = 'system/hemis/token';
    const CONFIG_SYS_HEMIS_REFRESH_TOKEN = 'system/hemis/refresh_token';
    const CONFIG_SYS_HEMIS_CLASSIFIERS_HASH = 'system/hemis/classifiers_hash';
    const CONFIG_CRON_LOGS = 'system/cron/logs';
    const CONFIG_COMMON_UPLOAD_MAX = 'system/common/upload_max_size';
    const CONFIG_COMMON_CONTRACT_CALCULATION = 'system/common/contract_calculation';
    const CONFIG_COMMON_ATTENDANCE_CONTROL = 'system/common/attendance_control';
    const CONFIG_COMMON_PERFORMANCE_CONTROL = 'system/common/performance_control';
    const CONFIG_SYS_UI_LOGO = 'system/ui/logo';

    public static function getCronLog($id)
    {
        $data = @json_decode(self::get(self::CONFIG_CRON_LOGS), true);

        return @$data[$id];
    }

    public static function getUploadMaxSize()
    {
        $size = intval(self::get(self::CONFIG_COMMON_UPLOAD_MAX));
        if ($size === 0) $size = 5;//Default 5MB

        return $size * 1024 * 1024;
    }

    public static function setCronLog($id, $value)
    {
        $data = @json_decode(self::get(self::CONFIG_CRON_LOGS), true);
        if (!is_array($data)) $data = [];
        $data[$id] = $value;

        self::set(self::CONFIG_CRON_LOGS, json_encode($data));
    }

    public static function getAllConfiguration()
    {
        $langs = [];

        foreach (self::getLanguageOptions() as $id => $languageOption) {
            $langs[] = [
                'label' => __('Enable {language}', ['language' => $languageOption]),
                'path' => 'system_language_' . $id,
                'type' => 'boolean',
                'disabled' => $id == self::LANGUAGE_RUSSIAN || $id == self::LANGUAGE_UZBEK || $id == self::LANGUAGE_CYRILLIC,
                'checked' => $id == self::LANGUAGE_RUSSIAN || $id == self::LANGUAGE_UZBEK || $id == self::LANGUAGE_CYRILLIC,
            ];
        }
        return [
            __('System Language') => $langs,
            __('Common Settings') => [
                [
                    'label' => __('System Logo'),
                    'path' => self::CONFIG_SYS_UI_LOGO,
                    'type' => 'image',
                    'help' => __('Upload university logo with size up to 512x512px'),
                ],
                [
                    'label' => __('Upload Max Size'),
                    'path' => self::CONFIG_COMMON_UPLOAD_MAX,
                    'type' => 'number',
                    'help' => __('Enter upload max size in MB'),
                ],
                [
                    'label' => __('Contract Price Calculation'),
                    'path' => self::CONFIG_COMMON_CONTRACT_CALCULATION,
                    'type' => 'options',
                    'options' => [
                        '11' => __('By Coefficient'),
                        '12' => __('By Sum')
                    ],
                    'help' => __('Shartnoma summasini hisoblash usuli'),
                ],
                [
                    'label' => __('Attendance Control Setting'),
                    'path' => self::CONFIG_COMMON_ATTENDANCE_CONTROL,
                    'type' => 'boolean',
                    'help' => __('Attendance Control Setting'),
                ],
                [
                    'label' => __('Performance Control Setting'),
                    'path' => self::CONFIG_COMMON_PERFORMANCE_CONTROL,
                    'type' => 'boolean',
                    'help' => __('Performance Control Setting'),
                ],
            ],
            __('System Developer') => [
                [
                    'label' => __('Enable Developer Toolbar'),
                    'path' => self::CONFIG_SYS_DEV_TOOLBAR_ENABLE,
                    'type' => 'boolean',
                    'help' => __('Shows developer toolbar to debug YII application'),
                ],
                [
                    'label' => __('Developer IP'),
                    'path' => self::CONFIG_SYS_DEV_TOOLBAR_IP,
                    'type' => 'text',
                    'help' => __('Enter comma separated IP addresses'),
                ],
                [
                    'label' => __('Email Alerts'),
                    'path' => self::CONFIG_SYS_DEV_EMAILS,
                    'type' => 'textarea',
                    'help' => __('Enter each email on new line'),
                ],
            ],
            __('HEMIS Integration') => [
                [
                    'label' => __('Login'),
                    'path' => self::CONFIG_SYS_HEMIS_LOGIN,
                    'type' => 'text',
                    'help' => __('University Login to authenticate for HEMIS'),
                ],
                [
                    'label' => __('Password'),
                    'path' => self::CONFIG_SYS_HEMIS_PASSWORD,
                    'type' => 'password',
                    'help' => __('University Password to authenticate for HEMIS'),
                ],
            ],
        ];
    }

    public static function getConfigs()
    {
        $rows = [];

        try {
            $rows = (new Query())
                ->select(['path', 'value'])
                ->from('e_system_config')
                ->all();
        } catch (\Exception $e) {
            return $rows;
        }


        $result = [];

        array_walk($rows, function ($item) use (&$result) {
            if (isset(self::$_sharedPaths[$item['path']])) {
                $result[$item['path']] = $item['value'];
                if (self::$_sharedPaths[$item['path']] == 'password') {
                    $result[$item['path']] = self::getEncrypted($item['value']);
                } else if (self::$_sharedPaths[$item['path']] == 'image') {
                    $result[$item['path']] = @json_decode($item['value'], true);
                }
            } else {
                $result[$item['path']] = $item['value'];
            }
        });

        return $result;
    }


    public static function batchUpdate($configuration = [])
    {
        if (count($configuration)) {
            foreach ($configuration as $path => $value) {
                if (isset(self::$_sharedPaths[$path])) {
                    Config::set($path, $value);
                }
            }

            Config::set('system_language_uz-UZ', 1);
            self::afterConfigChange();

            return true;
        }
    }


    public static function getEncrypted($value, $default = null)
    {
        return Yii::$app->security->decryptByKey(base64_decode($value), Yii::$app->params['system.encryptKey']);
    }

    public static function processValue($path, $value)
    {
        if (isset(self::$_sharedPaths[$path])) {
            if (self::$_sharedPaths[$path] === 'password') {
                if ($value == self::PASSWORD_FAKE_VALUE) {
                    $value = self::get($path);
                }
                return base64_encode(Yii::$app->getSecurity()->encryptByKey($value, Yii::$app->params['system.encryptKey']));
            } else if (self::$_sharedPaths[$path] === 'image') {
                $value = @json_encode($value);
            }
        }

        return $value;
    }


    public static function get($path, $default = null)
    {
        if (array_key_exists($path, static::$_configurations)) return static::$_configurations[$path];

        return $default;
    }


    public static function set($path, $value)
    {
        $value = self::processValue($path, $value);

        $params = [];
        $sql = (new QueryBuilder(Yii::$app->db))->upsert('e_system_config', ['path' => $path, 'value' => $value], true, $params);
        Yii::$app->getDb()->createCommand($sql, $params)->execute();

        self::$_configurations[$path] = $value;
    }

    public static function afterConfigChange()
    {
        self::$_configurations = self::getConfigs();

        $debug = '';
        $ips = '';
        if (self::get(self::CONFIG_SYS_DEV_TOOLBAR_ENABLE) && false) {
            $debug = "'debug'";
            $ips = explode(',', self::get(self::CONFIG_SYS_DEV_TOOLBAR_IP));
            foreach ($ips as $i => $ip) {
                $ip = trim($ip);
                $ips[$i] = "'$ip'";
            }
            $ips = implode(', ', $ips);
        }

        $config = "<?php
return [
    'bootstrap' => [$debug],
    'params'    => ['system.enableConfig'=>true],
    'modules'   => [
        'debug' => [
            'class'           => 'yii\\debug\\Module',
            'enableDebugLogs' => false,
            'allowedIPs'      => [$ips],
            'panels' => [
                'queue'   => [
                    'class' => 'yii\\queue\\debug\\Panel',
                ],
                'httpclient' => [
                    'class' => 'yii\\httpclient\\debug\\HttpClientPanel',
                ],
            ],
        ],
    ]
];
        ";


        file_put_contents(Yii::getAlias('@common' . DS . 'config' . DS . 'main-local.php'), $config);
    }

    public static function isHemisAuthenticationRequired()
    {
        if (HEMIS_INTEGRATION) {
            if (self::get(self::CONFIG_SYS_HEMIS_LOGIN) && self::get(self::CONFIG_SYS_HEMIS_PASSWORD) && self::get(self::CONFIG_SYS_HEMIS_TOKEN)) {
                return false;
            }

            return true;
        }

        return false;
    }
}