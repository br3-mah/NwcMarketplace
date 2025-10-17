<?php

namespace App\Http\Controllers\Auth\User;

use App\{
	Models\User,
	Models\Notification,
	Classes\GeniusMailer,
	Models\Generalsetting,
	Http\Controllers\Controller
};
use Illuminate\Http\Request;
use Auth;
use Validator;

class RegisterController extends Controller
{

    public function register(Request $request)
    {

    	$gs = Generalsetting::findOrFail(1);

    	
    	if($gs->is_capcha == 1)
        {
            $rules = [
                'g-recaptcha-response' => 'required|captcha'
            ];
            $customs = [
                'g-recaptcha-response.required' => "Please verify that you are not a robot.",
                'g-recaptcha-response.captcha' => "Captcha error! try again later or contact site admin..",
            ];
            $validator = Validator::make($request->all(), $rules, $customs);
            if ($validator->fails()) {
              return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
        }

        //--- Validation Section

        $rules = [
                'fname'    => 'required|string|max:191',
                'lname'    => 'nullable|string|max:191',
		        'email'    => 'required|email|unique:users',
		        'password' => 'required|confirmed'
                ];
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

	        $user = new User;
	        $input = $request->except('name');

            $firstName = trim((string) $request->input('fname', ''));
            $lastName = trim((string) $request->input('lname', ''));
            $fullName = trim((string) $request->input('name', ''));

            if ($fullName === '') {
                $fullName = trim($firstName . ' ' . $lastName);
            }

            if ($firstName === '' && $fullName !== '') {
                $parts = preg_split('/\s+/', $fullName, 2, PREG_SPLIT_NO_EMPTY);
                $firstName = $parts[0] ?? '';
                if ($lastName === '' && isset($parts[1])) {
                    $lastName = $parts[1];
                }
            }

            $input['fname'] = $firstName !== '' ? $firstName : null;
            $input['lname'] = $lastName !== '' ? $lastName : null;
            $input['name'] = $fullName !== '' ? $fullName : null;

            $fullNameForHash = $input['name'] ?? '';
            if ($fullNameForHash === '') {
                $fullNameForHash = (string) $request->email;
            }

	        $input['password'] = bcrypt($request['password']);
	        $token = md5(time().$fullNameForHash.$request->email);
	        $input['verification_link'] = $token;
	        $input['affilate_code'] = md5($fullNameForHash.$request->email);

	          if(!empty($request->vendor))
	          {
					//--- Validation Section
					$rules = [
						'shop_name' => 'unique:users',
						'shop_number'  => 'max:10'
							];
					$customs = [
						'shop_name.unique' => __('This Shop Name has already been taken.'),
						'shop_number.max'  => __('Shop Number Must Be Less Then 10 Digit.')
					];

					$validator = Validator::make($request->all(), $rules, $customs);
					if ($validator->fails()) {
					return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
					}
					$input['is_vendor'] = 1;

			  }
			  
			$user->fill($input)->save();
	        if($gs->is_verification_email == 1)
	        {
	        $to = $request->email;
	        $subject = 'Verify your email address.';
	        $msg = "Dear Customer,<br>We noticed that you need to verify your email address.<br>Simply click the link below to verify. <a href=".url('user/register/verify/'.$token).">".url('user/register/verify/'.$token)."</a>";
	        //Sending Email To Customer

	        $data = [
	            'to' => $to,
	            'subject' => $subject,
	            'body' => $msg,
	        ];

	        $mailer = new GeniusMailer();
	        $mailer->sendCustomMail($data);
	        

          	return response()->json('We need to verify your email address. We have sent an email to '.$to.' to verify your email address. Please click link in that email to continue.');
	        }
	        else {

            $user->email_verified = 'Yes';
            $user->update();
	        $notification = new Notification;
	        $notification->user_id = $user->id;
			$notification->save();
			
			// Welcome Email For User

			$data = [
				'to' => $user->email,
				'type' => "new_registration",
				'cname' => $user->name,
				'oamount' => "",
				'aname' => "",
				'aemail' => "",
				'onumber' => "",
			];
			$mailer = new GeniusMailer();
			$mailer->sendAutoMail($data);    


            Auth::login($user); 
          	return response()->json(1);
	        }

    }

    public function token($token)
    {
        $gs = Generalsetting::findOrFail(1);

        if($gs->is_verification_email == 1)
	    {    	
			$user = User::where('verification_link','=',$token)->first();
			if(isset($user))
			{
				$user->email_verified = 'Yes';
				$user->update();
				$notification = new Notification;
				$notification->user_id = $user->id;
				$notification->save();

				// Welcome Email For User

				$data = [
					'to' => $user->email,
					'type' => "new_registration",
					'cname' => $user->name,
					'oamount' => "",
					'aname' => "",
					'aemail' => "",
					'onumber' => "",
				];
				$mailer = new GeniusMailer();
				$mailer->sendAutoMail($data); 


				Auth::login($user); 
				return redirect()->route('user-dashboard')->with('success',__('Email Verified Successfully'));
			}
    	}
    	else {
    		return redirect()->back();	
    	}
    }
}
