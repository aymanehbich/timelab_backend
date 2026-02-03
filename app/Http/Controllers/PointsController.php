<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PointsController extends Controller
{
    //
    public function award(Request $request)
    {
        $request->validate(['points' => 'required|integer']);

        app(\App\Services\PointsService::class)
            ->award($request->user(), $request->points);

        return response()->json(['success' => true]);
    }

    public function history(Request $request)
    {
        return $request->user()->points()->latest()->get();
    }
}
