<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function getProfile(Request $request){
        $data = UserResource::make($request->user());
        return response()->success($data, 'profile details', 200);
    }

    public function updateProfile(Request $request){

    }

    public function setPfp(Request $request){
        // to be implemented in the next version, i hope
    }
    public function deleteAcount(Request $request){

    }
}
