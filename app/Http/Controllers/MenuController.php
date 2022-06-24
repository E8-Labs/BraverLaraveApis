<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Menu;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    //

    function loadMenu(Request $request){
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
			$menus = Menu::get();
			return response()->json(['status' => "1",
					'message'=> 'Menu list',
					'data' => $menus, 
				]);

    }
}
