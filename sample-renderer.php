<?php
/**
 * Not Robot related, but I needed it for my project so though maybe someone else would too.
 * Created by PhpStorm.
 * User: Exlord
 * Date: 5/19/2019
 * Time: 12:23 PM
 */

use Zend\View\Model\ViewModel;

require_once 'includes/bootstrap.php';

$util   = Util::GetInstance();
$data   = \Dominobot\Helper::GetPostParams();
$fields = $data['data']['fields'];

$model    = [
  'x' => 'y'
];
$vm = new ViewModel();
$vm->setTemplate('sample-view');
$vm->setVariables($model);
print $util->renderer->render($vm);

