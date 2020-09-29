<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Serie;
use http\Env\Response;
use Illuminate\Http\Request;
use Validator;
use App\Models\SeriesCategories;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

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

            foreach ($request->category as $cate){

                $cat = Category::where('category_name',$cate)->first();
                SeriesCategories::create(
                    [
                        'category_id' => $cat->id,
                        'serie_id'=>$serie->id
                    ]
                );
            }

        $kat = DB::table('categories')
            ->join('series_categories','series_categories.category_id','=','categories.id')
            ->where('series_categories.serie_id',$serie->id)
            ->select('category_name')
            ->get();


        return \response()->json(['serie'=>$serie,'categories'=>$kat],201);


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
            return response()->json(['errors'=>$validator->errors()],400);

        }

        $serie = Serie::find($id);
        if(is_null($serie)){
            return response()->json(['error'=>'data not found'],404);
        }
        $serie->update($request->all());
        if(isset($request->category)){
            $serr = SeriesCategories::with(['serie','category'])->where('serie_id',$id,)->get();
            $filter = [];
            foreach ($serr as $s){
                if(!in_array($s->category->category_name,$request->category)){
                    $s->delete();
                }
                else if(in_array($s->category->category_name,$request->category)){
                    array_push($filter,$s->category->category_name);
                }
            }

            foreach ($request->category as $cate){

                $cat = Category::where('category_name',$cate)->first();
               if(!in_array($cate,$filter)){
                   SeriesCategories::create(
                       ['category_id'=>$cat->id,
                           'serie_id'=>$id]
                   );
               }
            }
        }

        $se = Serie::find($id);

        $seriesComment = Comment::where('serie_id',$id)
            ->select('comment')
            ->get();

        $kat = DB::table('categories')
            ->join('series_categories','series_categories.category_id','=','categories.id')
            ->where('series_categories.serie_id',$id)
            ->select('category_name')
            ->get();


        return \response()->json(['serie'=>$se,'categories'=>$kat,'comments'=>$seriesComment],200);

//        $joinle = DB::table('series')
//            ->join('series_categories','series.id','=','series_categories.serie_id')
//            ->join('categories','categories.id','=','series_categories.category_id')
//            ->where('series_categories.serie_id',$id)
//            ->get();
//
//        return $joinle;




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
