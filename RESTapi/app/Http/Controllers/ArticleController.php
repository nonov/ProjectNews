<?php namespace Api\Http\Controllers;

use DB;
use Auth;
use Api\Http\Requests;
use Api\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ArticleController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		if($request->input('categorie') === "0") 
		{
			//$result = DB::select('select * from articles');
			$result = DB::select('select a.id, a.title, a.content, a.timestamp, a.img_path, a.categorie, u.username from articles a inner join users u on u.id = a.author_id order by a.id desc');
		} else {
			$result = DB::select('select a.id, a.title, a.content, a.timestamp, a.img_path, a.categorie, u.username from articles a inner join users u on u.id = a.author_id where a.categorie = ? order by a.id desc', [$request->input('categorie')]);
		}
		if(count($result)) 
		{
			foreach ($result as $article) {
				$article->timestamp = date('F d, Y', strtotime($article->timestamp));
			}
			return response()->json($result);
		}
		return response("Selection Failed.", 451);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Set Article Image.
	 *
	 * @return Response
	 */
	public function setPicture(Request $request)
	{
		if($request->file('file')->isValid() && Auth::check())
		{
			$filePath = '/project/app/imgDrop/Articles/article_'.str_random(20).".".$request->file('file')->guessExtension();
			if($request->file('file')->move('../../app/imgDrop/Articles/', $filePath))
			{
				return response($filePath);	
			}
		}
		return response("upload failure.", 441);
	}


	public function getPicture()
	{
		if(Auth::check())
		{
			$path = DB::select('select img from articles where id = ?', [Auth::user()->id])[0]->img;
			return response($path);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		if(DB::insert('insert into articles (title, content, author_id, img_path, categorie) values (?, ?, ?, ?, ?)', [$request->input('title'), $request->input('content'), $request->input('author_id'), $request->input('img_path'), $request->input('categorie')])) 
		{
			return response("1");
		} 
		return response("Insertion Failed.", 450);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
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
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if(DB::delete('delete from articles where id = ?', [$id]) == 1)
		{
			return response("1");
		}
		return response("Delete Failed.", 452);
	}

}
