<?php

namespace App\Controller;

use Pimcore\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    /**
     * @param Request $request
     * @return Response
    * 
     */
    #[Route("all-users", name:"all_users",methods :["GET"])]
    public function test(Request $request): Response
    {
        
        $allUsers=User::getById(5);
        dd('all',$allUsers->getRoles());
    }    
    
    
}
