<?php
/**
 * Created by PhpStorm.
 * User: Exlord
 * Date: 5/16/2019
 * Time: 3:42 PM
 */

namespace Dominobot;

class KeyboardButton {
  public $text          = null;
  public $callback_data = null;
  public $url           = null;
}

class KeyboardRow extends \ArrayObject implements \JsonSerializable {
  public function addButton($text) {
    $this->append($text);
  }

  /**
   * Specify data which should be serialized to JSON
   * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
   * @return mixed data which can be serialized by <b>json_encode</b>,
   * which is a value of any type other than a resource.
   * @since 5.4.0
   */
  public function jsonSerialize() {
    return $this->getArrayCopy();
  }
}

class InlineKeyboardRow extends \ArrayObject implements \JsonSerializable {
  /**
   * @param $text
   * @param $value
   * @param bool $isUrl
   * @return $this
   */
  public function addButton($text, $value, $isUrl = false) {
    $item     = ['text' => $text,];
    $n        = $isUrl ? 'url' : 'callback_data';
    $item[$n] = $value;

    $this->append($item);

    return $this;
  }

  public function jsonSerialize() {
    return $this->getArrayCopy();
  }
}

class Keyboard extends \ArrayObject implements \JsonSerializable {

  private $isInline = true;

  public function __construct($isInline = true) {
    parent::__construct();
    $this->isInline = $isInline;
  }

  /**
   * @return InlineKeyboardRow|KeyboardRow
   */
  public function addRow() {
    $row = $this->isInline ? new InlineKeyboardRow() : new keyboardRow();
    $this->append($row);
    return $row;
  }

  public function isInline() {
    return $this->isInline;
  }

  public function jsonSerialize() {
    return $this->getArrayCopy();
  }
}

class Message implements \JsonSerializable {
  public  $userId  = null;
  private $text    = null;
  private $file    = null;
  private $options = [];

  public function setText($text) {
    $this->text = $text;
    $this->file = null;
  }

  public function setFile($url, $type) {
    $this->text = null;
    $this->file = [
      "data" => $url,
      "type" => $type
    ];
  }

  public function setKeyboard(Keyboard $keyboard) {
    $n                             = $keyboard->isInline() ? 'inline_keyboard' : 'keyboard';
    $this->options['reply_markup'] = [
      $n => $keyboard
    ];
  }

  /**
   * Specify data which should be serialized to JSON
   * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
   * @return mixed data which can be serialized by <b>json_encode</b>,
   * which is a value of any type other than a resource.
   * @since 5.4.0
   */
  public function jsonSerialize() {
    return get_object_vars($this);
  }
}

class AbstractResponse implements \JsonSerializable {
  public function jsonSerialize() {
    return get_object_vars($this);
  }

  public function send() {
    $response = json_encode($this);
    header('Content-type: application/json');
    if (DEV_ENV)
      file_put_contents('sample_data/response.json', $response);
    print $response;
  }
}

class Response extends AbstractResponse {
  public $userId   = null;
  public $messages = [];
  public $command  = null;

  public function __construct($userId = null) {
    $this->userId = $userId;
  }

  /**
   * @param $messages
   * @return $this
   */
  public function setMessages($messages) {
    $this->messages = $messages;
    return $this;
  }

  /**
   * @param $msg
   * @return $this
   */
  public function addMessage($msg) {
    $this->messages[] = $msg;
    return $this;
  }

  public function setCommand($com) {
    $this->command = $com;
    return $this;
  }
}

class ValidationResponse extends AbstractResponse{
  /**
   * @var bool
   */
  public $status  = true;
  public $message = null;

  /**
   * ValidationResponse constructor.
   * @param bool $status
   * @param null $message
   */
  public function __construct($status, $message = null) {
    $this->status  = $status;
    $this->message = $message;
  }
}

class Helper {
  public static function GetPostParams() {
    $input = file_get_contents('php://input');
    if (DEV_ENV)
      file_put_contents(ROOT . '/sample_data/data.json', $input);
    if ((!$input || !strlen($input)) && DEV_ENV) {
      file_put_contents('sample_data/server.php', var_export($_SERVER, true));
    }
    return json_decode($input, true);
  }

  public static function SortFieldsById($fields) {
    $sorted = [];
    if ($fields) {
      foreach ($fields as $f) {
        $sorted[$f['id']] = $f;
      }
    }

    return $sorted;
  }

  public static function GetPhoneNumber($fieldValue) {
    $mobile = $fieldValue;
    if (is_object($mobile) || is_array($mobile))
      $mobile = $mobile['phone_number'];

    return $mobile;
  }

  public static function ClearPreFix($fieldValue, $prefix) {
    if (!$fieldValue)
      return $fieldValue;

    return str_replace($prefix, '', urldecode($fieldValue));
  }
}
