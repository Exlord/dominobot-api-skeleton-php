<?php
/**
 * Created by PhpStorm.
 * User: Exlord
 * Date: 5/20/2019
 * Time: 12:31 PM
 *
 * Wordpress Gravity form helper
 */

use Zend\Http\Client;
use Zend\Http\Request;

class GravityForm {

  private $util;
  private $apiEndpoint;
  private $consumerKey;
  private $consumerSecret;

  public $formId;
  public $form        = null;
  public $fields      = null;
  public $fieldsById  = null;
  public $values      = [];
  public $rawResponse = null;

  public function __construct($apiEndpoint, $consumerKey, $consumerSecret, $formId) {
    $this->util           = Util::GetInstance();
    $this->apiEndpoint    = $apiEndpoint;
    $this->formId         = $formId;
    $this->consumerSecret = $consumerSecret;
    $this->consumerKey    = $consumerKey;

    $this->getForm();
  }

  public function getForm() {
    if ($this->form)
      return $this->form;

    $cacheName = 'form';
    if ($this->util->hasCacheItem($cacheName))
      return $this->util->getCacheItem($cacheName);

    $request = new Request();
    $request->setMethod(Request::METHOD_GET);
    $request->setUri($this->apiEndpoint . 'forms/' . $this->formId);
    $request->getHeaders()->addHeaders([
      'Authorization' => 'Basic ' . base64_encode($this->consumerKey . ':' . $this->consumerSecret),
      'Content-Type'  => 'application/json',
      "User-Agent"    => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0",
    ]);


    $client = new Client();
    $client->setOptions([
      'timeout' => 10000,
    ]);
    $response   = $client->send($request);
    $this->form = json_decode($response->getBody(), true);
    if (DEV_ENV)
      $this->util->setCacheItem($cacheName, $this->form);

    return $this->form;
  }

  public function getFields() {
    if ($this->fields)
      return $this->fields;

    $cacheName = 'fields';
    if ($this->util->hasCacheItem($cacheName))
      return $this->util->getCacheItem($cacheName);

    $form         = $this->getForm();
    $this->fields = $form['fields'];

    if (DEV_ENV)
      $this->util->setCacheItem($cacheName, $this->fields);

    return $this->fields;
  }

  public function getFieldsById() {
    if ($this->fieldsById)
      return $this->fieldsById;

    $cacheName = 'fieldsById';
    if ($this->util->hasCacheItem($cacheName))
      return $this->util->getCacheItem($cacheName);

    $fields = $this->getFields();

    foreach ($fields as $field) {
      $this->fieldsById[$field['id']] = $field;
    }

    if (DEV_ENV)
      $this->util->setCacheItem($cacheName, $this->fieldsById);

    return $this->fieldsById;
  }

  public function submit() {
    $request = new Request();
    $request->setMethod(Request::METHOD_POST);
    $request->setUri($this->apiEndpoint . 'forms/' . $this->formId . '/submissions');
    $request->getHeaders()->addHeaders([
      'Authorization' => 'Basic ' . base64_encode($this->consumerKey . ':' . $this->consumerSecret),
      'Content-Type'  => 'application/json',
      "User-Agent"    => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0",
    ]);
    $data = json_encode($this->values);
    $request->setContent($data);

    $client = new Client();
    $client->setOptions([
      'timeout' => 10000,
    ]);
    $response          = $client->send($request);
    $response          = $response->getBody();
    $this->rawResponse = $response;
    $response          = json_decode($response, true);

    return $response;
  }

  public function calculateFieldValue($field) {
    if (!is_array($field)) {
      $fields = $this->getFieldsById();
      $field  = $fields[$field];
    }

    $formula = $field['calculationFormula'];
    preg_match_all('/\{[^\}]*\}/', $formula, $output_array);
    $allFieldsHaveValue = true;
    foreach ($output_array[0] as $row) {
      preg_match('/[0-9]+/', $row, $id);
      $id = $id[0];
      if (isset($this->values['input_' . $id])) {
        $fieldValue = $this->values['input_' . $id];
        $formula    = str_replace($row, $fieldValue, $formula);
      } else
        $allFieldsHaveValue = false;
    }

    if ($allFieldsHaveValue)
      return $this->values['input_' . $field['id']] = eval('return ' . $formula . ';');

    return null;
  }

  public function getFirstVisibleField($targetFields) {
    $fields = $this->getFieldsById();
    foreach ($targetFields as $f) {
      if ($this->isFieldVisible($fields[$f]))
        return $f;
    }

    return false;
  }

  public function isFieldVisible($field) {
    $conditions = [];
    $isVisible  = true;
    $logic      = $field['conditionalLogic'];
    if (is_array($logic)) {
      $actionType = $logic['actionType'];//show|hide
      $logicType  = $logic['logicType'];//all|any
      $rules      = $logic['rules'];

      foreach ($rules as $rule) {
        $fieldId      = $rule['fieldId'];
        $fieldValue   = @$this->values['input_' . $fieldId];
        $conditions[] = $this->_rule($fieldValue, $rule['operator'], $rule['value']);
      }

      if ($logicType == 'all')
        $isVisible = !in_array(false, $conditions);
      else
        $isVisible = in_array(true, $conditions);

      if ($actionType == 'hide')
        $isVisible = !$isVisible;
    }

    return $isVisible;
  }

  private function _rule($fieldValue, $operator, $value) {
    $left  = strtolower($fieldValue);
    $right = strtolower($value);
    switch ($operator) {
      case 'is':
        return $left == $right;
        break;
      case 'isnot':
        return $left != $right;
        break;
      case '>':
        return $left > $right;
        break;
      case '<':
        return $left < $right;
        break;
      case 'contains':
        return strpos($left, $right) !== false;
        break;
      case 'starts_with':
        return startsWith($left, $right);
        break;
      case 'ends_with':
        return endsWith($left, $right);
        break;
    }

    return true;
  }


}


