<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use OneSignal;

class AuthController extends ApiController
{
    public function testOauth (Request $request){
        $user = Auth::user();
        return $this->sendResponse($user, "Usuarios recuperados correctamente");
    }


    public function test (Request $request){
        OneSignal::sendNotificationToAll("Some Message", $url = null, $data = null, $buttons = null, $schedule = null);
        return $this->sendResponse([
            'status' => "OK"
        ], "Usuarios recuperados correctamente");
    }


    public function register (Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }

        $input = $request->all();
        $input["password"] = bcrypt($request->get("password"));
        $user = User::create($input);
        $token = $user->createToken("MyApp")->accessToken;

        $data = [
            'token' => $token,
            'user' => $user
        ];
        return $this->sendResponse($data, "Usuarios recuperados correctamente");
    }
}
