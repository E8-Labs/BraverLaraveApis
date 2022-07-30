<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auth\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 
use Mail; 
use Hash;

class AuthForgotPasswordController extends Controller
{
    //

    //
     /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    // use SendsPasswordResetEmails;

    /**
       * Write code on Method
       *
       * @return response()
       */
      public function showForgetPasswordForm()
      {
         return view('auth.passwords.forgetPassword');
      }
  
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitForgetPasswordForm(Request $request)
      {
    //       $request->validate([
    //           'email' => 'required|email|exists:user',
    //       ]);
      // return "Email sending";
          $token = Str::random(64);
            DB::table('password_resets')->where('email', $request->email)->delete();
          DB::table('password_resets')->insert([
              'email' => $request->email, 
              'token' => $token, 
              'created_at' => Carbon::now()
            ]);
      $user = User::where('email', $request->email)->first();
          Mail::send('Mail.forgetPassword', ['code' => $token, 'name'=> $user->name], function($message) use($request){
              $message->to($request->email);
              $message->subject('Reset Password');
          });
          
          return response()->json(
            [
                "message" => "Email sent",
                "status" => "1",
                'data' => null,
            ],
            200);
  
        //   return back()->with('message', 'We have e-mailed your password reset link!');
      }
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function showResetPasswordForm($token) { 
         return view('auth.passwords.forgetPasswordLink', ['token' => $token]);
      }
  
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitResetPasswordForm(Request $request)
      {
          $request->validate([
              'email' => 'required|email|exists:user',
              'password' => 'required|string|min:6|confirmed',
              'password_confirmation' => 'required'
          ]);
  
          $updatePassword = DB::table('password_resets')
                              ->where([
                                'email' => $request->email, 
                                'token' => $request->token
                              ])
                              ->first();
  
          if(!$updatePassword){
              return back()->withInput()->with('error', 'Invalid token!');
          }
  
          $user = User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);
 
          DB::table('password_resets')->where(['email'=> $request->email])->delete();
            return view('auth.passwords.forgetPasswordLink', ['token' => $request->token]);
          // return redirect('/login')->with('message', 'Your password has been changed!');
      }
      
      function resetPasswordAdmin(Request $request){
          $password = $request->password;
          $user = User::where('email', 'phg@gmail.com')->first();
          $user->password = Hash::make($password);
          $saved = $user->save();
          if($saved){
              return response()->json(['status' => true,
                    'message'=> 'Password updated',
                    'data' => null, 
                ]); 
          }
          else{
              return response()->json(['status' => false,
                    'message'=> 'Password not updated',
                    'data' => null, 
                ]); 
          }
      }
}
