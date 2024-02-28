<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserDataController extends Controller
{
    /**
     * @param Request $request
     * @return UserResource
     */
    public function __invoke(Request $request): UserResource
    {
        $data =  auth()->user()->load('profile');
        return new UserResource($data);
    }
}
