<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\User;
use App\Model\emailverification;
use App\Model\favourite;
use App\Model\resetPassword;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\company\companyCollection;

class UserController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email', 
            'password' => 'required', 
            'c_password' => 'required|same:password', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $milliseconds = round(microtime(true) * 1000);
        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        try{
            $user = User::create($input);
            $data['userId'] = $user->id;
            $data['verification'] = $milliseconds;
            $data['status'] = "false";
            $mydata = emailverification::create($data);
        }catch(QueryException  $error){
            return response()->json(['error' => "Some error occered please try again after some time."], 500);
        }
        mail($input['email'],"login confirmation mail","Hi, ".$input['name'].", Please verify your email address by clicking this link <a href='http://www.myDomine/confirmation/".$milliseconds."'>click here</a>");
        $success['token'] =  $user->createToken('MyApp')-> accessToken; 
        $success['userData'] = $user;
        $success['status'] = 200;
        // $success['userData'] = $userdata;
        return response()->json(['success'=>$success]); 
    }

    public function login(Request $data){
        if(Auth::attempt(['email' => $data['email'], 'password' => $data['password']])){
            $user = Auth::user();
            $verify = DB::table('emailverifications')->where('userId',$user->id)->get(); 
            if(sizeOf($verify) && $verify[0]->status == "true"){
	            $success['userData'] = $user;
	            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
	            $success['status'] = 200;
	            return response()->json(['success' => $success]); 
	        }else{
                $success['message'] = 'verification pending.';
                $success['status'] = 409;
	        	return response()->json(['success'=> $success]);  	
	        }
        }else{ 
            return response()->json(['error'=>'Unauthorised.'], 401); 
        } 
    }

    public function resetPassword(Request $request){
        $user = DB::table('users')->where('email', $request->email)->get();
        if(sizeOf($user)){
            $digits = 4;
            $randnum = rand(pow(10, $digits-1), pow(10, $digits)-1);
            $input['email'] = $request->email;
            $input['code'] = $randnum;
            $data = resetPassword::create($input);
            if($data){
                mail($input['email'],"login confirmation mail","Hi, ".$user[0]->name.", Please find your reset password otp is ".$randnum);
                $result['message'] = "Reset password initiated please, fill otp and rest the password.";
                $result['id'] = $data->id;
                $result['status'] = 200;
                return $result; 
            }else{
                $result['message'] = "Some problem occered please try again later.";
                $result['status'] = 500;
                return $result;  
            }
        }else{
            $result['message'] = "User not found.";
            $result['status'] = 404;
            return $result;
        }
    }

    public function completeReset(Request $request){
        $validator = Validator::make($request->all(), [ 
            'password' => 'required', 
            'c_password' => 'required|same:password', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $allinput = $request->all();
        $input['password'] = bcrypt($allinput['password']);
        $data = DB::table('reset_passwords')->where('id', $allinput['id'])->get();
        if($data[0]->code == $allinput['otp']){            
            if(DB::table('users')->where('email', $data[0]->email)->update($input)){
                DB::table('reset_passwords')->where('id', $allinput['id'])->delete();
                $result['message'] = "password reset success.";
                $result['status'] = 200;
                return $result;
            }else{
                $result['message'] = "some problem occered please try again.";
                $result['status'] = 500;
                return $result;
            }
        }else{
            $result['message'] = "Otp did not changed.";
            $result['status'] = 409;
            return $result;
        }
    }

    public function verifyEmail(Request $request){
    	$verify = DB::table('emailverifications')->where('verification',$request->code)->get();
    	if(sizeOf($verify)){
    		if($verify[0]->status == "true"){
    			$result['message'] = "The email id already verified.";
    			$result['status'] = 200;
    			return $result;
    		}else{
    			$input['status'] = "true";
	    		if(DB::table('emailverifications')->where('verification',$request->code)->update($input)){
	    			$result['message'] = "Email id verification success.";
	    			$result['status'] = 200;
	    			return $result;
	    		}else{
	    			$result['message'] = "Some problem occrred please try again later.";
	    			$result['status'] = 500;
	    			return $result;
	    		}
    		}
    	}else{
    		$result['message'] = "We are not able to find the verification code.";
    		$result['status'] = 404;
    		return $result;
    	}
    	return $request;
    }

    public function getCompanys(){
    	$companies = DB::table('companies')->get();
    	if(sizeOf($companies)){
    		$result['message'] = "success";
    		$result['company'] = $companies;
    		$result['status'] = 200;
    	}else{
    		$result['message'] = "no companies found.";
    		$result['company'] = $companies;
    		$result['status'] = 404;
    	}
    	return $result;
    }

    public function sortandsearchCompany(Request $request){
    	// asc , desc
    	$request = $request->all();
    	$query = DB::table('companies');
    	if($request['query']){
    		$value = $request['query'];
    		$query->where('name', 'LIKE', '%'.$value.'%');
    	}
    	if( $request['sortBy']){
    		$query->orderBy('name', $request['sortBy']);	
    	}
    	$companies = $query->get();	
    	if(sizeOf($companies)){
    		$result['message'] = "success";
    		$result['company'] = $companies;
    		$result['status'] = 200;
    	}else{
    		$result['message'] = "no companies found.";
    		$result['company'] = $companies;
    		$result['status'] = 404;
    	}
    	return $result;
    }

    public function addFav(Request $request){
    	$user = Auth::user(); 
    	$input['userId'] = $user->id;
    	$input['companyId'] = $request->id;
    	$fav = DB::table('favourites')->where('userId',$user->id)->where('companyId',$request->id)->get();
    	if(sizeOf($fav)){
    		$result['message'] = "company is already is in your favourite list.";
    		$result['status']  = 401;
    		return $result;
    	}else{
    		try{
    			$addFav = favourite::create($input);
    			if($addFav){
    				$result['message'] = "The company is added to your favourite list.";
    				$result['status'] = 200;
    			}else{
    				$result['message'] = "Some error occered please try again after some time.";
    				$result['status'] = 500;
    			}
    			return $result;
    		}catch(QueryException  $error){
	    		return response()->json(['error' => "Some error occered please try again after some time."], 500);
	    	}
    	}
    }

    public function removeFav(Request $request){
    	$user = Auth::user(); 
    	$input['userId'] = $user->id;
    	$input['companyId'] = $request->id;
    	$fav = DB::table('favourites')->where('userId',$user->id)->where('companyId',$request->id)->get();
    	if(sizeOf($fav)){
    		$delet = DB::table('favourites')->where('userId',$user->id)->where('companyId',$request->id)->delete();
    		if($delet){
    			$result['message'] = "company is removed from your favourite list.";
    			$result['status'] = 200;	
    			return $result;
    		}else{
    			$result['message'] = "Some error occered please try again after some time.";
    			$result['status'] = 500;	
    			return $result;
    		}    		
    	}else{
    		$result['message'] = "company is not found in your favourite list.";
    		$result['status'] = 401;
    		return $result;
    	}
    }

    public function getFav(){
    	$user = Auth::user(); 
    	$fav = DB::table('favourites')->where('userId',$user->id)->get();
    	if(sizeOf($fav)){
	    	$companies = []; 	
	    	foreach ($fav as $key => $value) {
	    		array_push($companies, $value->companyId);
	    	}
	    	$company = DB::table('companies')->whereIn("id",$companies)->get();
	    	$result['message'] = "success.";
	    	$result['company'] = $company;
	    	$result['status'] = 200;
	    	return $result;
    	}else{
    		$result['message'] = "no companies found in your favourite list.";
    		$result['status'] = 404;
    		return $result;
    	}
    }
}
