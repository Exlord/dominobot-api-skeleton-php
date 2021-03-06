<?php
/**
 * Created by PhpStorm.
 * User: Exlord
 * Date: 5/19/2019
 * Time: 12:23 PM
 */

use Dominobot\Keyboard;
use Dominobot\Message;
use Dominobot\Response;

require_once 'includes/bootstrap.php';

$util   = Util::GetInstance();
$data   = \Dominobot\Helper::GetPostParams();
$fields = $data['data']['fields'];

$keyboard = new Keyboard(true);
$message  = new Message();

$keyboard->addRow()->addButton('I am a inline Button', 'https://dominobot.ir', true);
$message->setText('Hello Bot!');
$message->setKeyboard($keyboard);

(new Response())->setMessages([$message])->send();
