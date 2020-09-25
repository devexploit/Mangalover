<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Serie;
use http\Env\Response;
use Illuminate\Http\Request;
use Validator;
use App\Models\SeriesCategories;
use App\Models\Category;

class SerieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function seriesGet()
    {
        //
        return response()->json(['series'=>Serie::get()],200);

    }

    public function serieDetails($id){
        $serie = Serie::find($id);
        if(is_null($serie)){
            return response()->json(['error'=>'data not found'],404);
        }
        return response()->json(['serie'=>$serie]);
    }

    public function seriesPost(Request $request)
    {
        $rules = [
          'name'=>'required',
            'author'=>'required',
            'beginning'=>'required',
            'country'=>'required',
            'type'=>'required',
            'category'=>'required|array|min:1'
        ];

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],400);
        }

        $serie = Serie::create($request->all());
//            foreach ($request->category as $kategori){
//
//                $cat = Category::where('category_name',$kategori)->first();
//                if(is_null($cat)){
//                    return response()->json(['error'=>'category not found'],404);
//                }
//                SeriesCategories::create(
//                    [
//                        'category_id' => $cat->id,
//                        'serie_id'=>$serie->id
//                        ]
//                );
//
//            }
            foreach ($request->category as $cate){

                $cat = Category::where('category_name',$cate)->first();
                SeriesCategories::create(
                    [
                        'category_id' => $cat->id,
                        'serie_id'=>$serie->id
                    ]
                );
            }

        return \response()->json(['serie'=>SeriesCategories::with('serie','category')->where('serie_id',$serie->id)->get()],201);

//        return response()->json(['serie'=>$serie],201);

//        $seriescategories = SeriesCategories::with(['serie','category'])->get();
//        return response()->json($seriescategories);

//        $categories = Category::with('seriescategories')->where('category_name','dram')->get();
//        return response()->json($categories);



    }

    public function seriesPut(Request $request, $id){

        $rules = [
            'name'=>'min:2|max:255',
            'author'=>'min:2|max:255',
            'beginning'=>'min:2|max:255',
            'country'=>'min:2|max:255',
            'type'=>'min:2|max:255',
            'category'=>'array|min:1'
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],200);

        }

        $serie = Serie::find($id);
        if(is_null($serie)){
            return response()->json(['error'=>'data not found'],404);
        }
        $serie->update($request->all());
        if(isset($request->category)){
            SeriesCategories::where('serie_id',$id)->delete();
            foreach ($request->category as $cate){

                $cat = Category::where('category_name',$cate)->first();
                SeriesCategories::create(
                    [
                        'category_id' => $cat->id,
                        'serie_id'=>$id
                    ]
                );
            }

        }
        return response()->json($serie,200);
    }

    public function seriesDelete($id){

        $serie = Serie::find($id);
        if(is_null($serie)){
            return response()->json(['error'=>'data not found'],404);
        }
        $serie->delete();
        SeriesCategories::where('serie_id',$id)->delete();
        return response()->json(null,204);

    }

}
