<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Service;

class SettingService
{
  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
    $this->list = [];
  }

  public function getList(): array
  {
    $settings = [];
    $set = $this->em->getRepository("App:Setting")->findAll();
    foreach($set as $s){
      $settings[$s->getName()] = $s->getValue();
    }
    return $settings;
  }

  public function list(): array
  {
    if(empty($this->list)){
      $this->list = $this->getList();
    }
    return $this->list;
  }

  public function get(string $name): string
  {
    if(empty($this->list)){
      $this->list = $this->getList();
    }
    if(isset($this->list[$name])){
      return $this->list[$name];
    }
    return '';
  }

  public function exist(string $name): bool
  {
    if(empty($this->list)){
      $this->list = $this->getList();
    }
    if(isset($this->list[$name])){
      return true;
    }
    return false;
  }
}
