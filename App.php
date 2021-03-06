<?php

namespace MABI;

include_once __DIR__ . '/Slim/Slim.php';
include_once __DIR__ . '/Extension.php';

use \Slim\Slim;
use Slim\Exception\Stop;

Slim::registerAutoloader();

/**
 * todo: docs
 */
class App extends Extension {

  /**
   * @var \Slim\Slim;
   */
  protected $slim;

  /**
   * @return \Slim\Slim
   */
  public function getSlim() {
    return $this->slim;
  }

  public function getRequest() {
    return $this->slim->request();
  }

  public function getResponse() {
    return $this->slim->response();
  }

  /**
   * @var App
   */
  protected static $singletonApp = NULL;

  /**
   * todo: docs
   */
  static function getSingletonApp() {
    if (empty(self::$singletonApp)) {
      self::$singletonApp = new App();
    }

    return self::$singletonApp;
  }

  public function __construct() {
    if (file_exists(__DIR__ . '/middleware')) {
      array_push($this->middlewareDirectories, __DIR__ . '/middleware');
    }
    $this->slim = new Slim();
    parent::__construct($this);
  }

  /**
   * Returns a JSON array displaying the error to the client and stops execution
   *
   * Example Error Message Definition:
   * define('ERRORDEF_NO_ACCESS', array('message' => 'No Access', 'code' => 1007, 'httpcode' => 402));
   *
   * @param $message string|array
   * @param $httpStatusCode int|null
   * @param $applicationErrorCode int|null
   *
   * @throws \Slim\Exception\Stop
   * @throws \Exception
   */
  public function returnError($message, $httpStatusCode = NULL, $applicationErrorCode = NULL) {
    if (is_array($message)) {
      if (array_key_exists('message', $message) && array_key_exists('httpcode', $message) &&
        array_key_exists('code', $message)
      ) {
        $message = $message['message'];
        $httpStatusCode = $message['httpcode'];
        $applicationErrorCode = $message['code'];
      }
      else {
        throw new \Exception('Improper error message definition');
      }
    }

    echo json_encode(array(
      'error' => empty($applicationErrorCode) ? array('message' => $message) :
        array('code' => $applicationErrorCode, 'message' => $message)
    ));
    $this->getResponse()->status($httpStatusCode);
    throw new Stop($message);
  }

  public function errorHandler($e) {
    $this->slim->getLog()->error($e);
    $this->getResponse()->status(500);
    echo json_encode(array(
      'error' => array('code' => 1020, 'message' => 'A system error occurred')
    ));
  }

  public function run() {
    foreach ($this->getControllers() as $controller) {
      $controller->loadRoutes($this->slim);
    }

    if (!$this->slim->config('debug')) {
      $this->slim->error(array($this, 'errorHandler'));
    }

    $this->slim->run();
  }

  public function call() {
    foreach ($this->getControllers() as $controller) {
      $controller->loadRoutes($this->slim);
    }

    if (!$this->slim->config('debug')) {
      $this->slim->error(array($this, 'errorHandler'));
    }

    $this->slim->call();
  }

  public function getIOSModel() {
    $iosModel = IOSModelInterpreter::getIOSDataModel();

    foreach ($this->getModelClasses() as $modelClass) {
      $model = call_user_func($modelClass . '::init', $this);
      IOSModelInterpreter::addModel($iosModel, $model);
    }

    return $iosModel->asXML();
  }
}

