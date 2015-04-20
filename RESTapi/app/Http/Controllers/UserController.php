<?php namespace Api\Http\Controllers;

use Auth;
use Hash;
use DB;
use Mail;
use Illuminate\Contracts\Auth\Registrar;
use Api\Http\Requests;
use Api\Http\Controllers\Controller;

use Illuminate\Http\Request;

class UserController extends Controller {


	public function __construct(Request $request, Registrar $registrar)
	{
		$this->_request = $request;
		$this->_registrar = $registrar;
		if(Auth::check()) $this->_user = Auth::user();
		else $this->_user = null;
	}
	/**
	 * Create a new user.
	 *
	 * @return Response
	 */
	public function store()
	{	
		$validator = $this->_registrar->validator($this->_request->all());
		if($validator->passes())
		{
			$this->_user = $this->_registrar->create($this->_request->all());
			if($this->_user->id)
			{
				$token = str_random(20);
				Mail::send('emails.activation', ['token' => $token], function($message)
				{
				    $message->to($this->_user->email, $this->_user->firstname." ".$this->_user->lastname)->subject('Activate your account !');
				});	
				return response(DB::update('update users set activation_token = ? where id = ?', [$token, $this->_user->id]));
			}
		}
		return response(0);
	}


	public function passwordCheck()
	{
		return response((string) Hash::check($this->_request->input('password'), $this->_user->password));
	}

	public function emailCheck()
	{
		return response(count(DB::select('select id from users where email = ?', [$this->_request->input('email')])));
	}

	public function activateUser()
	{
		$id = DB::select('select id from users where activation_token = ?', [$this->_request->input('token')]);
		if(count($id) === 1)
		{
			return response(DB::update('update users set activation_token = NULL where id = ?', [$id[0]->id]));
		}
		return response(0, 464);
	}
	/**
	 * Authenticate the user.
	 *
	 * @return Response
	 */
	public function authenticate(Request $request)
	{
		if(Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')]))
		{
			$this->_user = Auth::user();
			if(!$this->_user->activation_token)
			{
				return response()->json(["id" => csrf_token(), "user" => $this->_user]);
			}
		}
		return 0;
	}

	public function logout()
	{
		return response(Auth::logout());
	}
	/**
	 * Display the specified resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if(Auth::check())
		{
			return response()->json(["id" => csrf_token(), "user" => $this->_user]);
		}
		return response(0);
	}

	public function setPicture(Request $request)
	{
		if($request->file('file')->isValid() && Auth::check())
		{
			$filePath = '/news/app/imgDrop/user_'.$this->_user->id.".".$request->file('file')->guessExtension();
			if($request->file('file')->move('../../app/imgDrop/', $filePath))
			{
				DB::update('update users set img = ? where id = ?', [$filePath, $this->_user->id]);
				return response($filePath);	
			}
		}
		return response("upload failure.", 441);
	}


	public function getPicture()
	{
		if(Auth::check())
		{
			$path = DB::select('select img from users where id = ?', [$this->_user->id])[0]->img;
			return response($path);
		}
		return response("Get path failed.", 442);
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		if((($cpt = count($this->_request->input())) > 0 ) && Auth::check())
		{
			if(count(array_intersect_key($this->_request->all(), array('username' => "", 'current' => "", 'password' => "", 'confirm' => ""))) === count($this->_request->all()))
			{
				if($cpt == 1)
				{
					$username = $this->_request->input('username');
					$arg[] = $username;
					$query = "update users set username = ? where id = ".$this->_user->id;
				} else {
					$current = $this->_request->input('current');
					$password = $this->_request->input('password');
					$confirm = $this->_request->input('confirm');
					
					if(!Hash::check($current, $this->_user->password) || $password !== $confirm)
					{
						return response("Wrong password.", 460);
					} else 
					{
						$arg[] = bcrypt($password);
						$query = "update users set password = ? where id = ".$this->_user->id;
					}
				}

				if(DB::update($query, $arg))
				{
					return response("1");
				}
			}
			return response("Profil Update Failed", 461);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if(Auth::check()) 
		{
			if(DB::delete("delete from users where id = ?", [$id])) {
				return response("1");
			}
		}
		return response("Delete Account Failed", 462);
	}

}
