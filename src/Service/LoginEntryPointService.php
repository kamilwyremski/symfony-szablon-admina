<?php

namespace App\Service;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginEntryPointService implements AuthenticationEntryPointInterface
{
    protected $router;

    public function __construct($router)
    {
      $this->router = $router;
    }

    /*
     * This method receives the current Request object and the exception by which the exception 
     * listener was triggered. 
     * 
     * The method should return a Response object
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $session = $request->getSession();

        $session->getFlashBag()->add('danger', 'Musisz być zalogowany aby zobaczyć tą stronę');

        return new RedirectResponse($this->router->generate('app_login'));
    }
}