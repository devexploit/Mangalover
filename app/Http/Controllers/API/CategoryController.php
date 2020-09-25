<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Validator;
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function categoryPost(Request $request)
    {
        $rules = [
          'category_name'=>'required'
        ];

        $validate = Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['error'=>$validate->errors()],400);
        }

        $category = Category::where('category_name',$request->category_name)->first();
        if(isset($category)){
            return response()->json(['message'=>'category already exists']);
        }

        $category = Category::create($request->all());
        return response()->json(['category'=>$category],201);
    }

    public function categoryPut(Request $request, $id){
        $rules = [
            'category_name'=>'min:2|max:255'
        ];
        $validate = Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['error'=>$validate->errors()],400);
        }

        $category = Category::find($id);

        $category->update($request->all());
        return response()->json(['category'=>$category],200);

    }

    public function categoryDelete($id){

        $category = Category::find($id);
        if(is_null($category)){
            return response()->json(['error'=>'data not found'],404);
        }

    }
}
