<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Quiz;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {   
        if( $request->ajax() ){
            $result = Quiz::select(DB::raw("
                quiz.id,
                quiz.course_id,
                quiz.name,
                quiz.intro,
                quiz.attempts
            "))
            ->get();
            return ['data'=>$result];
        }
        $courses = $result = DB::table('courses')->get();
        return view('home',compact('courses'));
    }

    public function store(Request $request){
        Quiz::updateOrCreate(
            [
                'id'=>$request->id
            ],
            [
            'course_id'=>$request->course_id,
            'name'=>$request->name,
            'intro'=>$request->intro,
            'attempts'=>$request->attempts
        ]);
        $text = ( is_null($request->id) ) ? 'Registro Agregado' : 'Registro Actualizado' ;
        return [
            'title'=>'Buen Trabajo',
            'text' =>$text,
            'icon' =>'success'
        ];
    }

    public function edit($id){
        $result = Quiz::where('id',$id)->first();
        return $result;
    }

    public function destroy($id){
        $result = Quiz::where('id',$id)->delete();
        return [
            'title'=>'Buen Trabajo',
            'text' =>'Registro Eliminado',
            'icon' =>'success'
        ];
    }

}
