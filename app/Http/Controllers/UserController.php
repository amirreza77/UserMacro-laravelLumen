<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
    public $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function store(Request $request)
    {

        /****************
         * IMAGE UPLOAD *
         ****************/
        $this->validate($request, [
            'username'  => 'unique:mycms_user',
            'email'     => 'required|email|unique:mycms_user',
            'password'  => 'required',
            'cellphone' => 'required',
        ]);
        $fileName = "";
        if ($request->file('avatar') != null) {
            if ($request->file('avatar')->isValid()) {
                $fileName        = $request->file('avatar')->getClientOriginalName();
                $fileName        = time() . '-' . trim($fileName);
                $destinationPath = __DIR__ . '/../public/images/';
                $request->file('avatar')->move($destinationPath, $fileName);
                // Add file name to data Base
            }
        }
        /************************
         * ADD DATA TO DATABASE *
         ************************/
        $password = $request['password'];
        $password = Hash::make($password);
        DB::table('mycms_user')->insert([
            'name'      => $request['name'],
            'username'  => $request['username'],
            'email'     => $request['email'],
            'password'  => $password,
            'cellphone' => $request['cellphone'],
            'address'   => $request['address'],
            'avatar'    => trim($fileName),
            'register'  => date("Y-m-d H:i:s"),
        ]);

        $response = array(
            'status' => '200',
            'msg'    => 'ثبت نام شما انجام شد و در انتظار تایید شما میباشد',
        );
        return $response;
    }
    public function validateByCell(Request $request)
    {
        $this->validate(
            $request,
            ['cellphone' => 'required|exists:mycms_user'],
            ['cellphone.exists' => 'لطفا ابتدا ثبت نام نمایید']
        );

        $UserInTable = DB::table('mycms_user')->where('cellphone', $request['cellphone'])->first();

        if ($UserInTable != null) {
            if ($UserInTable->valid == 1) {
                DB::table('mycms_login')->where('userid', $UserInTable->id)->delete();
                 DB::table('mycms_user')->where('id', $UserInTable->id )->update([
                'valid' => '0',
            ]);
            }
            $verificationCode = rand(0, 9999);
            DB::table('mycms_user')->where('cellphone', $request['cellphone'])->update([
                'vcode' => $verificationCode,
            ]);

            $this->sendSms($request['cellphone'], $verificationCode);
            return array(
                'status' => '200',
                'msg'    => 'کد اعتبار سنجی شما ارسال شد',
            );
        }
    }
    public function validateCode(Request $request)
    {

        $this->validate(
            $request,
            [
                'cellphone' => 'required|exists:mycms_user',
                'vcode'     => 'required|digits:4',
            ],
            [
                'cellphone.exists' => 'لطفا ابتدا ثبت نام نمایید',
                'vcode.digits'     => 'طول کد اعتبار سنجی معتبر نیست',
            ]
        );
        $UserInTable = DB::table('mycms_user')->where('cellphone', $request['cellphone'])->first();

        $vCodeInTable = $UserInTable->vcode;
        if ($vCodeInTable != $request['vcode']) {
            return array(
                'status' => '400',
                'msg'    => 'کد اعتبار سنجی به درستی وارد نشده است',
            );
        } else {
            DB::table('mycms_user')->where('cellphone', $request['cellphone'])->update([
                'valid' => '1',
            ]);
            $token = md5($UserInTable->cellphone . $UserInTable->password . rand(0000, 999));
            $check = DB::table('mycms_login')->where('userid', $UserInTable->id)->first();
            if ($check != null) {
                DB::table('mycms_login')->where('userid', $UserInTable->id)->update([
                    'token'     => $token,
                    'lasttoken' => date("Y-m-d H:i:s"),
                ]);
            } else {
                DB::table('mycms_login')->insert([
                    'userid'    => $UserInTable->id,
                    'token'     => $token,
                    'lasttoken' => date("Y-m-d H:i:s"),
                ]);
            }

            return array(
                'status' => '200',
                'msg'    => 'کد اعتبار سنجی معتبر است',
                'token'  => $token,
            );
        }
    }
    public function sendSms($cell, $verificationCode)
    {

        if (preg_match("~^0\d+$~", $cell)) {
            $cell = substr($cell, 1);
        };
        $data = array(
            'username' => env('SMS_USERNAME'),
            'password' => env('SMS_PASSWORD'),
            'to'       => $cell,
            'from'     => env('SMS_LINE'),
            "text"     => "کد اعتبار سنجی شما " . ' : ' . $verificationCode,
        );
        $post_data = http_build_query($data);
        $handle    = curl_init('https://rest.payamak-panel.com/api/SendSMS/SendSMS');
        curl_setopt($handle, CURLOPT_HTTPHEADER, array(
            'content-type' => 'application/x-www-form-urlencoded',
        ));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        $response = curl_exec($handle);
        //  var_dump($response);
    }
    public function userLogin(Request $request)
    {
        $token = $request->get('token');
        $this->validate(
            $request,
            [
                'token' => 'required',
            ]
        );

        $check = DB::table('mycms_login')->where('token', $token)->first();
        if ($check) {
            $lastLogin    = $check->lasttoken;
            $rightnow     = date("Y-m-d H:i:s");
            $diftime      = strtotime($rightnow) - strtotime($lastLogin);
            $diffrentTime = round($diftime / 60);
            if ($diffrentTime < env('EXPIRE_TOKEN_TIME')) {
                DB::table('mycms_login')->where('token', $token)->update([
                    'lasttoken' => date("Y-m-d H:i:s"),
                ]);
            } else {
                return array(
                    'status' => '400',
                    'msg'    => 'توکن ارسالی معتبر  نیست دوباره توکن ایجاد نمایید',
                );
            }
            return array(
                'status' => '200',
                'msg'    => 'توکن ارسالی معتبر  است',
            );
        } else {
            return array(
                'status' => '401',
                'msg'    => 'توکن ارسالی معتبر  نیست',
            );
        }
    }
    public function show(Request $request)
    {

        $userAuth = $this->userLogin($request);
        if ($userAuth['status'] == '200') {
            $UserLogin = DB::table('mycms_login')->where('token', $request['token'])->first();

            $UserDetails = DB::table('mycms_user')->where('id', $UserLogin->userid)->first();
            return array($UserDetails);
        }
    }
    public function edit(request $request)
    {
        $userAuth = $this->userLogin($request);

        if ($userAuth['status'] == '200') {
            $fileName = "";

            if ($request->file('avatar') != null) {
                if ($request->file('avatar')->isValid()) {
                    $fileName        = $request->file('avatar')->getClientOriginalName();
                    $fileName        = time() . '-' . trim($fileName);
                    $destinationPath = __DIR__ . '../../../../public/images/';
                    $request->file('avatar')->move($destinationPath, $fileName);

                    // Add file name to data Base
                }
            }
            $password  = $request['password'];
            $password  = Hash::make($password);
            $UserLogin = DB::table('mycms_login')->where('token', $request['token'])->first();
            DB::table('mycms_user')->where('id', $UserLogin->userid)->update([
                'name'     => $request['name'],
                'username' => $request['username'],
                'email'    => $request['email'],
                'password' => $password,
                'address'  => $request['address'],
                'avatar'   => trim($fileName),
            ]);
            return array(
                'status' => '200',
                'msg'    => 'مشخصات تغییر یافت ',
            );
        }
    }
    public function verifyEmailByUserAndPass(request $request)
    {
      
        $this->validate(
            $request,
            [
                'username' => 'required|exists:mycms_user',
                'password' => 'required',
            ],
            [
                'cellphone.exists' => 'لطفا ابتدا ثبت نام نمایید',
            ]
        );
        $username    = $request['username'];
        $UserInTable = DB::table('mycms_user')->where('username', $username)->first();
        if ($UserInTable != null) {
            DB::table('mycms_login')->where('userid', $UserInTable->id)->delete();
            if (Hash::check($request['password'], $UserInTable->password)) {             
                   DB::table('mycms_user')->where('id', $UserInTable->id )->update([
                'valid' => '0',
            ]);
                $token = md5($UserInTable->cellphone . $UserInTable->password . rand(0000, 999));
                $check = DB::table('mycms_login')->where('userid', $UserInTable->id)->first();
                if ($check != null) {
                    DB::table('mycms_login')->where('userid', $UserInTable->id)->update([
                        'token'     => $token,
                        'lasttoken' => date("Y-m-d H:i:s"),
                    ]);
                } else {
                    DB::table('mycms_login')->insert([
                        'userid'    => $UserInTable->id,
                        'token'     => $token,
                        'lasttoken' => date("Y-m-d H:i:s"),
                    ]);
                }

                $this->mail($UserInTable->name, $UserInTable->email, $token);
                return array(
                    'status' => '200',
                    'msg'    => 'ایمیل اعتبار سنجی ارسال شد',
                  
                );
            }else{
             return array(
                    'status' => '400',
                    'msg'    => 'رمز ورودی اشتباه است',
             );
             }
        }
    }
    public function verifyEmail(request $request)
    {

         $this->validate(
            $request,
            [
                'token' => 'required|exists:mycms_login',
            
            ],
            [
                'token.exists' => 'لینک اشتباه است',
            ]
        );
        $token    = $request['token'];
        $UserInLoginTable = DB::table('mycms_login')->where('token', $token)->first();
        $UserInTable = DB::table('mycms_user')->where('id', $UserInLoginTable->userid)->first();
        if ($UserInTable != null) {
            if ($UserInTable->valid == 0) {
                DB::table('mycms_user')->where('id', $UserInTable->id )->update([
                'valid' => '1',
            ]);
              return array(
                    'status' => '200',
                    'msg'    => 'اعتبار سنجی ایمیل شما با موفقیت انجام شد ',
                    'token' => $token 
                );
            } else {
                return array(
                    'status' => '400',
                    'msg'    => 'شما پیش تر اعتبار سنجی شده اید',
                );
            }
        }  
    }

    public function mail($to_name, $to_email, $token)
    {
       
 $url  = url('/user/verifyemail?token='.$token);

        $data = array('name' => $to_name, 'token' => $token , 'url'=>$url);

        Mail::send('mail', $data, function ($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('تاییدیه ایمیل');
        });
    }
}
