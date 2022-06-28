<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auth\User;
use App\Models\Auth\UserType;
use App\Models\Auth\AccountStatus;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //

    function getUser(Request $request){
    	$validator = Validator::make($request->all(), [
			'userid' => 'required',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$user = User::where('userid', $request->userid)->orWhere('id', $request->userid)->first();
			if($user){
				return response()->json(['status' => "1",
					'message'=> 'User details',
					'data' => $user, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'No such user',
					'data' => null, 
				]);
			}
    }

    function getUserByInviteCode(Request $request){
    	$validator = Validator::make($request->all(), [
			'invitecode' => 'required',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$user = User::where('myinvitecode', $request->invitecode)->first();
			if($user){
				return response()->json(['status' => "1",
					'message'=> 'User details',
					'data' => $user, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'No such user',
					'data' => null, 
				]);
			}
    }


    function deleteUser(Request $request){
    	$validator = Validator::make($request->all(), [
			'userid' => 'required',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$user = User::where('userid', $request->userid)->orWhere('id', $request->userid)->delete();
			if($user){
				return response()->json(['status' => "1",
					'message'=> 'User deleted',
					'data' => $user, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error deleting user',
					'data' => null, 
				]);
			}
    }

    function approveUser(Request $request){
    	$validator = Validator::make($request->all(), [
			'userid' => 'required',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$user = User::where('userid', $request->userid)->orWhere('id', $request->userid)->update(['accountstatus'=> AccountStatus::Approved]);
			if($user){
				return response()->json(['status' => "1",
					'message'=> 'User approved',
					'data' => $user, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error approving user',
					'data' => null, 
				]);
			}
    }

    function searchPending(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * 50 - 50;
			$users = User::where('accountstatus', AccountStatus::Pending)->where('role', "!=", UserType::TypeAdmin)->take(50)->skip($off_set)->get();
			if($request->has('search')){
				$search = $request->search;
				$users = User::where('accountstatus', AccountStatus::Pending)->where('name', 'LIKE', "%$search%")->where('role', "!=", UserType::TypeAdmin)->take(50)->skip($off_set)->get();
			}
			else{

			}

			
			if($users){
				return response()->json(['status' => "1",
					'message'=> 'User list',
					'data' => $users, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error deleting user',
					'data' => null, 
				]);
			}
    }


    function searchActive(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * 50 - 50;
			$users = User::where('accountstatus', AccountStatus::Approved)->where('role', "!=", 'ADMIN')->take(50)->skip($off_set)->get();
			if($request->has('search')){
				$search = $request->search;
				$users = User::where('accountstatus', AccountStatus::Approved)->where('name', 'LIKE', "%$search%")->where('role', "!=", 'ADMIN')->take(50)->skip($off_set)->get();
			}
			else{

			}

			
			if($users){
				return response()->json(['status' => "1",
					'message'=> 'User list',
					'data' => $users, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error deleting user',
					'data' => null, 
				]);
			}
    }

    function adminTeamMembers(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * 50 - 50;
			$users = User::where('accountstatus', AccountStatus::Approved)->where('role', "=", UserType::TypeTeam)->take(50)->skip($off_set)->get();
			if($request->has('search')){
				$search = $request->search;
				$users = User::where('accountstatus', AccountStatus::Approved)->where('name', 'LIKE', "%$search%")->where('role', "=", UserType::TypeTeam)->take(50)->skip($off_set)->get();
			}
			else{

			}

			
			if($users){
				return response()->json(['status' => "1",
					'message'=> 'User list',
					'data' => $users, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error deleting user',
					'data' => null, 
				]);
			}
    }
}