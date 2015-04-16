<?php
namespace AuthActions\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Component\CookieComponent;

class AuthUtilsComponent extends Component
{
    public function addRemeberMeCookie()
    {
        return;
    }

    public function deleteRemebermeCookie()
    {
        return;
    }

    private function __setupCookie() {
        //$this->Cookie->write('name', 'Larry');
    }
} 