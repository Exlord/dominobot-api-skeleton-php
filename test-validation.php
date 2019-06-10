<?php
/**
 * Created by PhpStorm.
 * User: Exlord
 * Date: 5/19/2019
 * Time: 12:23 PM
 */

use Dominobot\ValidationResponse;

require_once 'includes/bootstrap.php';

$util   = Util::GetInstance();
$data   = \Dominobot\Helper::GetPostParams();
$fields = $data['data']['fields'];


$isValid = $fields[1] === 'X';
$message = $isValid ? '' : 'معتبر نیست!';
(new ValidationResponse($isValid, $message))->send();
