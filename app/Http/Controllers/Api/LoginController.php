<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Auth;
use App\Repositories\UserRepository;
use App\Http\Resources\user\SessionUserResource;
use App\Models\SessionUser;

class LoginController extends Controller
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function login(LoginRequest $request)
    {

        if(Auth::attempt($request->filter())){
            $checkTokenExit = $this->userRepository->get();
            if(empty($checkTokenExit)){
                $userSession = new SessionUserResource($this->userRepository->login($request->filter()));
                return $userSession;
            }else{
                $userSession = $checkTokenExit;
                return $userSession;
            } 
            return response()->json([
                'code' => 200,
                'data' => $userSession
            ], 200);
        }else{
            return response()->json([
                'code' => 500,
                'message' => 'Tài khoản hoặc mật khẩu không đúng'
            ], 500);
        }
    }
    public function refreshToken(Request $request)
    {
        $token = $request->header('token');
        $checkTokenIsValid = SessionUser::where('token', $token)->first();
        if(!empty($checkTokenIsValid)){
             if($checkTokenIsValid->token_expried < time()){
               new SessionUserResource($this->userRepository->updateToken($checkTokenIsValid->id));
               $dataSession = SessionUser::find($checkTokenIsValid->id);
               return response()->json([
                   'code' => 200,
                   'data' => $dataSession,
                   'message' => 'Refresh token sucess'
               ], 200);
            }
        }
    }

    public function deleteToken(Request $request)
    {
        $token = $request->header('token');
        $checkTokenIsValid = SessionUser::where('token', $token)->first();
        if(!empty($checkTokenIsValid)){
               new SessionUserResource($this->userRepository->deleteToken($checkTokenIsValid->id));
               return response()->json([
                   'code' => 200,
                   'message' => 'Delete token sucess'
               ], 200);
        }
    }
}
