<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\SettingService;

class SettingExtension extends AbstractExtension
{
  private $settings;
  
  public function __construct(SettingService $SettingService)
  {
      $this->settings = $SettingService;
  }

  public function getFunctions()
  {
      return [
          new TwigFunction('setting', [$this, 'setting']),
      ];
  }

  public function setting(string $name): string
  {
    return $this->settings->get($name);
  }
  
}
