<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\Request;
use App\Service\SettingService;
use ReCaptcha\ReCaptcha;

class CaptchaUtil
{
  private $settings;

  public function __construct(SettingService $settings)
  {
    $this->settings = $settings;
  }

  public function check(Request $request){

    if($this->settings->get('recaptcha_secret_key')){

      $recaptcha = new ReCaptcha($this->settings->get('recaptcha_secret_key'));
      $resp = $recaptcha->verify($request->request->get('recaptcha_response'), $request->getClientIp());

      if ($resp->isSuccess()) {
        return true;
      }else{
        return false;
      }
    }else{
      return true;
    }
  }
}