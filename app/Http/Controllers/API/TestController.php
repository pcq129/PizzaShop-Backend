<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
// use Symfony\Component\HttpFoundation\Request;
use Illuminate\Http\Request;


class TestController extends Controller
{
    public function getapi(Request $request){
        $user = User::find($request->id);
        if($user){
            return response()->json([$user], 200);
        }
        else{
       return response()->json([], 404);

        }
    }


    public function login(Request $request){
        $user= User::find();
    }





}
