<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Clothes;
use App\Coordination;
use App\ClothesCoordination;
use App\Season;
use Illuminate\Support\Facades\DB;

class CoordinationController extends Controller
{
    public function index()
    {
        $coordination = Coordination::select('id', 'url')->with('seasons')->with(['clothes' => function ($q) {
            $q->select('id', 'url', 'category', 'color', 'x', 'y', 'width', 'height');
        }])->orderBy('created_at', 'desc')->get();

        return $coordination;
    }

    public function add(Request $request)
    {
        DB::beginTransaction();
        try {
            $newCoordination = new Coordination();
            $newCoordination->user_id = 1;
            $newCoordination->cloudinary_id = $request->cloudinary_id;
            $newCoordination->url = $request->url;
            $newCoordination->save();

            $newCoordination->seasons()->sync($request->seasons);

            foreach ($request->clothesList as $clothes) {
                $newCoordinateClothes = new ClothesCoordination();
                $newCoordinateClothes->coordinations_id = $newCoordination->id;
                $newCoordinateClothes->clothes_id = $clothes['id'];
                $newCoordinateClothes->x = $clothes['x'];
                $newCoordinateClothes->y = $clothes['y'];
                $newCoordinateClothes->width = $clothes['w'];
                $newCoordinateClothes->height = $clothes['h'];

                $newCoordinateClothes->save();
            }
            DB::commit();
            return $newCoordination;
        } catch (\Exception $e) {
            DB::rollback();
            return "エラー：".$e;
        }
    }
}
