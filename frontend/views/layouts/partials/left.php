<?php
/**
 * @var $this \backend\components\View
 */

$user = $this->_user();
$activeMenu = Yii::$app->controller->activeMenu ?: Yii::$app->session->get('activeMenu');
?>
<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu tree" data-widget="tree">
            <?php foreach ($this->getMenuItems() as $id => $item): ?>
                <?php $active = $id == $activeMenu ?>
                <li class="treeview <?= $active ? 'menu-open' : '' ?>">
                    <a href="<?= $item['url'] ?>">
                        <i class="fa fa-<?= $item['icon'] ?>"></i> <span><?= $item['label'] ?></span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class='treeview-menu' <?= $active ? 'style="display:block"' : '' ?>>
                        <?php foreach ($item['items'] as $child): $activeChild = $child['id'] == $this->getFullPath() ?>
                            <li class="<?= $activeChild ? 'active' : '' ?>">
                                <a href="<?= $child['url'] ?>">
                                    <i class="fa fa-circle<?= $activeChild ? '' : '-o' ?>"></i>
                                    <span><?= $child['label'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</aside>
