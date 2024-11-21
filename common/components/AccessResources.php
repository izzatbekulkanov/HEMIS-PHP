<?php

namespace common\components;

use backend\components\Rule;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * Login form
 */
class AccessResources
{
    public static function parsePermissions($force = false)
    {
        $permissions = [];
        Yii::beginProfile('Parse permissions', __METHOD__);
        foreach (self::getClasses() as $class) {
            $controller = new \ReflectionClass($class);

            $controllerId = Inflector::camel2id(mb_substr($controller->getShortName(), 0, -10), '-', true);
            $methods = array_filter($controller->getMethods(\ReflectionMethod::IS_PUBLIC), function (\ReflectionMethod $m) use ($controller) {
                return stripos($m->getName(), 'action') === 0 && $m->getDeclaringClass()->getName() === $controller->getName();
            });
            /** @var \ReflectionMethod $method */
            foreach ($methods as $method) {
                if ($method->getShortName() === 'actions') {
                    continue;
                }
                $action = Inflector::camel2id(mb_substr($method->getShortName(), 6));

                if ($annotations = self::getResourceAnnotations($method->getDocComment())) {
                    foreach ($annotations as $resource) {
                        $permissions[] = [
                            'group' => ucfirst($controllerId),
                            'name' => Inflector::camel2words(str_replace('/', ' ', $resource)),
                            'path' => $resource,
                            'skip' => false,
                            'login' => false,
                        ];
                    }
                }
                if ($annotations = self::getSkipResourceAnnotations($method->getDocComment())) {
                    foreach ($annotations as $resource) {
                        $permissions[] = [
                            'group' => ucfirst($controllerId),
                            'name' => Inflector::camel2words(str_replace('/', ' ', $resource)),
                            'path' => $resource,
                            'skip' => true,
                            'login' => false,
                        ];
                    }
                }

                $name = self::getActionTitle($method, $controllerId);
                $permissions[] = [
                    'group' => ucfirst($controllerId),
                    'name' => $name,
                    'path' => $controllerId . '/' . $action,
                    'skip' => mb_strpos($method->getDocComment(), '@skipAccess') !== false,
                    'login' => mb_strpos($method->getDocComment(), '@loginRequired') !== false,
                ];
            }
        }
        $result = array_unique($permissions, SORT_REGULAR);

        $updated = 0;
        $shouldBeDeleted = ArrayHelper::map(AdminResource::find()->all(), 'path', 'id');

        foreach ($result as $permission) {
            if ($exist = AdminResource::findOne(['path' => $permission['path']])) {
                if ($exist->updateAttributes($permission)) {
                    $updated++;
                }
            } else {
                $permission['active'] = true;
                $new = new AdminResource($permission);
                if ($new->save()) {
                    $updated++;
                }
            }
            unset($shouldBeDeleted[$permission['path']]);
        }

        if (count($shouldBeDeleted))
            AdminResource::deleteAll(['id' => array_values($shouldBeDeleted)]);

        if ($config = Json::decode(file_get_contents(Yii::getAlias('@common/data/admin-roles.json')), true)) {

            foreach ($config as $i => $roleData) {
                $role = AdminRole::findOne(['code' => $roleData['code']]);
                if ($role == null) {
                    $role = new AdminRole([
                        'status' => AdminRole::STATUS_ENABLE,
                        'code' => $roleData['code'],
                        'position' => isset($roleData['position']) ? $roleData['position'] : $i,
                        'name' => array_pop($roleData['name'])
                    ]);
                }
                foreach ($roleData['name'] as $lang => $name) {
                    $role->setTranslation('name', $name, $lang);
                }
                if ($role->save()) {
                    foreach ($roleData['resources'] as $path) {
                        if ($resource = AdminResource::findOne(['path' => trim($path)])) {
                            if (!$role->hasResource($resource)) {
                                $role->link('resources', $resource);
                            }
                        }
                    }
                }
            }
        }

        Yii::debug(sprintf("Parsed %d permission", \count($result)), __METHOD__);
        Yii::endProfile('Parse permissions', __METHOD__);

        return $updated;
    }

    public static function getResourceAnnotations($doc)
    {
        /*
         * @todo parse from code like `canAccessToResources(*)`
         */
        preg_match_all('#@resource\s[a-z0-9-_/]+\s#s', $doc, $annotations);

        if (count($annotations[0])) {
            array_walk($annotations[0], function (&$item, $key) {
                $item = trim(substr(trim($item), 9));
            });

            return $annotations[0];
        }

        return false;
    }

    public static function getSkipResourceAnnotations($doc)
    {
        /*
         * @todo parse from code like `canAccessToResources(*)`
         */
        preg_match_all('#@skipResource\s[a-z0-9-_/]+\s#s', $doc, $annotations);

        if (count($annotations[0])) {
            array_walk($annotations[0], function (&$item, $key) {
                $item = trim(substr(trim($item), 13));
            });

            return $annotations[0];
        }

        return false;
    }

    public static function getClasses()
    {
        $classes = [];
        foreach (glob(Yii::getAlias('@backend/controllers/*Controller.php')) as $file) {
            if (basename($file, '.php') !== 'AjaxController') {
                $classes[] = 'backend\\controllers\\' . basename($file, '.php');
            }
        }
        return $classes;
    }

    private static function getActionTitle(\ReflectionMethod $action, ?string $controller)
    {
        $docLines = preg_split('~\R~u', $action->getDocComment());
        if (isset($docLines[1]) && mb_strpos($docLines[1], '@') === false) {
            return trim($docLines[1], "\t *");
        }

        return Inflector::camel2words(Inflector::id2camel($controller) . Inflector::id2camel(mb_substr($action->getShortName(), 6)));
    }
}
